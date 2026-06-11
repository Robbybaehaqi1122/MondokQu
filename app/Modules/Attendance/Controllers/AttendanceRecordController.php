<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Requests\UpdateAttendanceRecordsRequest;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\LeaveRequest;
use App\Models\Santri;
use App\Models\User;
use App\Notifications\SantriAttendanceAlertNotification;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AttendanceRecordController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function edit(Request $request, AttendanceSession $attendanceSession): View
    {
        $this->authorize('inputRecords', $attendanceSession);

        $currentUser = $request->user();
        $session = AttendanceSession::query()
            ->visibleTo($currentUser)
            ->with(['activity.responsibleUser', 'creator', 'records.recorder'])
            ->withCount('records')
            ->findOrFail($attendanceSession->id);

        $activeSantris = Santri::query()
            ->visibleTo($currentUser)
            ->forTenant($session->tenant_id)
            ->with('room')
            ->where('status', Santri::STATUS_ACTIVE)
            ->orderBy('full_name')
            ->get();

        $activeLeaveRequestsBySantri = LeaveRequest::query()
            ->visibleTo($currentUser)
            ->forTenant($session->tenant_id)
            ->activeOnDate($session->session_date)
            ->get()
            ->keyBy('santri_id');

        $recordsBySantri = $session->records->keyBy('santri_id');
        $recordStats = $this->recordStats($session);

        return view('attendance.records.edit', [
            'session' => $session,
            'activeSantris' => $activeSantris,
            'activeLeaveRequestsBySantri' => $activeLeaveRequestsBySantri,
            'recordsBySantri' => $recordsBySantri,
            'recordStats' => $recordStats,
            'statusOptions' => AttendanceRecord::statusOptions(),
            'defaultStatus' => AttendanceRecord::STATUS_PRESENT,
            'permissionStatus' => AttendanceRecord::STATUS_PERMISSION,
            'canEditRecords' => $session->status !== AttendanceSession::STATUS_COMPLETED,
        ]);
    }

    public function update(UpdateAttendanceRecordsRequest $request, AttendanceSession $attendanceSession): RedirectResponse
    {
        $this->authorize('inputRecords', $attendanceSession);

        $currentUser = $request->user();
        $validated = $request->validated();
        $session = AttendanceSession::query()
            ->visibleTo($currentUser)
            ->findOrFail($attendanceSession->id);
        $recordedAt = now();
        $records = collect($validated['records']);
        $newAbsentSantriIds = [];

        DB::transaction(function () use (&$newAbsentSantriIds, $records, $session, $currentUser, $recordedAt): void {
            $existingRecords = AttendanceRecord::query()
                ->where('tenant_id', $session->tenant_id)
                ->where('attendance_session_id', $session->id)
                ->whereIn('santri_id', $records->pluck('santri_id'))
                ->get()
                ->keyBy('santri_id');

            $upsertData = $records->map(fn (array $payload) => [
                'tenant_id' => $session->tenant_id,
                'attendance_session_id' => $session->id,
                'santri_id' => (int) $payload['santri_id'],
                'status' => $payload['status'],
                'notes' => $payload['notes'] ?? null,
                'recorded_by' => $currentUser?->id,
                'recorded_at' => $recordedAt,
            ])->all();

            AttendanceRecord::query()->upsert(
                $upsertData,
                ['tenant_id', 'attendance_session_id', 'santri_id'],
                ['status', 'notes', 'recorded_by', 'recorded_at']
            );

            foreach ($records as $payload) {
                $santriId = (int) $payload['santri_id'];
                $previousStatus = $existingRecords->get($santriId)?->status;

                if (
                    $payload['status'] === AttendanceRecord::STATUS_ABSENT
                    && $previousStatus !== AttendanceRecord::STATUS_ABSENT
                ) {
                    $newAbsentSantriIds[] = $santriId;
                }
            }
        });

        $newAbsentRecordIds = $newAbsentSantriIds === []
            ? []
            : AttendanceRecord::query()
                ->where('tenant_id', $session->tenant_id)
                ->where('attendance_session_id', $session->id)
                ->whereIn('santri_id', $newAbsentSantriIds)
                ->where('status', AttendanceRecord::STATUS_ABSENT)
                ->pluck('id')
                ->all();

        $this->notifyAbsentGuardians($newAbsentRecordIds);

        $this->activityLogger->log(
            action: 'attendance_records_updated',
            actor: $currentUser,
            target: $session,
            description: 'Input absensi santri diperbarui.',
            properties: [
                'session_id' => $session->id,
                'activity_id' => $session->attendance_activity_id,
                'session_date' => $session->session_date?->toDateString(),
                'record_count' => $records->count(),
                'status_counts' => $records->countBy('status')->all(),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('attendance.sessions.records.edit', $session)
            ->with('success', 'Absensi santri berhasil disimpan.');
    }

    /**
     * @return array<string, int>
     */
    protected function recordStats(AttendanceSession $session): array
    {
        $counts = $session->records
            ->countBy('status');

        return [
            'recorded' => $session->records->count(),
            AttendanceRecord::STATUS_PRESENT => (int) $counts->get(AttendanceRecord::STATUS_PRESENT, 0),
            AttendanceRecord::STATUS_PERMISSION => (int) $counts->get(AttendanceRecord::STATUS_PERMISSION, 0),
            AttendanceRecord::STATUS_SICK => (int) $counts->get(AttendanceRecord::STATUS_SICK, 0),
            AttendanceRecord::STATUS_ABSENT => (int) $counts->get(AttendanceRecord::STATUS_ABSENT, 0),
            AttendanceRecord::STATUS_LATE => (int) $counts->get(AttendanceRecord::STATUS_LATE, 0),
        ];
    }

    /**
     * Notify linked wali users when a santri is newly marked absent.
     *
     * @param array<int, int> $recordIds
     */
    protected function notifyAbsentGuardians(array $recordIds): void
    {
        if ($recordIds === []) {
            return;
        }

        AttendanceRecord::query()
            ->with(['santri.guardians' => fn ($q) => $q->where('status', User::STATUS_ACTIVE), 'session.activity'])
            ->whereIn('id', array_unique($recordIds))
            ->get()
            ->each(function (AttendanceRecord $attendanceRecord): void {
                $guardians = $attendanceRecord->santri?->guardians;

                if ($guardians->isEmpty()) {
                    return;
                }

                Notification::send($guardians, new SantriAttendanceAlertNotification($attendanceRecord));
            });
    }
}
