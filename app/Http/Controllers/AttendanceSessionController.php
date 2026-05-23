<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\StoreAttendanceSessionRequest;
use App\Http\Requests\Attendance\UpdateAttendanceSessionRequest;
use App\Models\AttendanceRecord;
use App\Models\AttendanceActivity;
use App\Models\AttendanceSession;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceSessionController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $selectedActivityId = trim((string) $request->string('activity'));
        $selectedStatus = trim((string) $request->string('status'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        $baseQuery = AttendanceSession::query()->visibleTo($currentUser);

        $filteredQuery = (clone $baseQuery)
            ->with(['activity.responsibleUser', 'creator'])
            ->withCount([
                'records',
                'records as present_records_count' => fn (Builder $query) => $query->where('status', AttendanceRecord::STATUS_PRESENT),
                'records as issue_records_count' => fn (Builder $query) => $query->whereIn('status', [
                    AttendanceRecord::STATUS_PERMISSION,
                    AttendanceRecord::STATUS_SICK,
                    AttendanceRecord::STATUS_ABSENT,
                    AttendanceRecord::STATUS_LATE,
                ]),
            ])
            ->tap(fn (Builder $builder) => $this->applyFilters($builder, $selectedActivityId, $selectedStatus, $dateFrom, $dateTo));

        $sessions = (clone $filteredQuery)
            ->orderByDesc('session_date')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('attendance.sessions.index', [
            'sessions' => $sessions,
            'filters' => [
                'activity' => $selectedActivityId,
                'status' => $selectedStatus,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'sessionStats' => [
                'total' => (clone $baseQuery)->count(),
                'draft' => (clone $baseQuery)->where('status', AttendanceSession::STATUS_DRAFT)->count(),
                'open' => (clone $baseQuery)->where('status', AttendanceSession::STATUS_OPEN)->count(),
                'today' => (clone $baseQuery)->whereDate('session_date', now()->toDateString())->count(),
            ],
            'activityOptions' => $this->activityOptions($currentUser),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(StoreAttendanceSessionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $currentUser = $request->user();

        if (! $currentUser?->tenant_id) {
            abort(403);
        }

        $activity = AttendanceActivity::query()
            ->visibleTo($currentUser)
            ->findOrFail($validated['attendance_activity_id']);

        $session = AttendanceSession::query()->create([
            'tenant_id' => $activity->tenant_id,
            'attendance_activity_id' => $activity->id,
            'session_date' => $validated['session_date'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => $currentUser->id,
        ]);

        $this->activityLogger->log(
            action: 'attendance_session_created',
            actor: $currentUser,
            target: $session,
            description: 'Sesi absensi harian dibuat.',
            properties: [
                'activity_id' => $activity->id,
                'activity_name' => $activity->name,
                'session_date' => $session->session_date?->toDateString(),
                'status' => $session->status,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('attendance.sessions.index')
            ->with('success', 'Sesi absensi berhasil dibuat.');
    }

    public function update(UpdateAttendanceSessionRequest $request, AttendanceSession $attendanceSession): RedirectResponse
    {
        $validated = $request->validated();
        $currentUser = $request->user();

        $session = AttendanceSession::query()
            ->visibleTo($currentUser)
            ->findOrFail($attendanceSession->id);
        $activity = AttendanceActivity::query()
            ->visibleTo($currentUser)
            ->findOrFail($validated['attendance_activity_id']);

        $previousValues = $session->only([
            'attendance_activity_id',
            'session_date',
            'status',
            'notes',
        ]);

        $session->update([
            'attendance_activity_id' => $activity->id,
            'session_date' => $validated['session_date'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $session->refresh();

        $this->activityLogger->log(
            action: 'attendance_session_updated',
            actor: $currentUser,
            target: $session,
            description: 'Sesi absensi harian diperbarui.',
            properties: [
                'before' => $previousValues,
                'after' => $session->only([
                    'attendance_activity_id',
                    'session_date',
                    'status',
                    'notes',
                ]),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('attendance.sessions.index')
            ->with('success', 'Sesi absensi berhasil diperbarui.');
    }

    public function destroy(Request $request, AttendanceSession $attendanceSession): RedirectResponse
    {
        $currentUser = $request->user();
        $session = AttendanceSession::query()
            ->visibleTo($currentUser)
            ->with('activity')
            ->findOrFail($attendanceSession->id);

        $this->activityLogger->log(
            action: 'attendance_session_deleted',
            actor: $currentUser,
            target: $session,
            description: 'Sesi absensi harian dihapus.',
            properties: [
                'activity_id' => $session->attendance_activity_id,
                'activity_name' => $session->activity?->name,
                'session_date' => $session->session_date?->toDateString(),
                'status' => $session->status,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $session->delete();

        return redirect()
            ->route('attendance.sessions.index')
            ->with('success', 'Sesi absensi berhasil dihapus.');
    }

    protected function applyFilters(Builder $builder, string $selectedActivityId, string $selectedStatus, string $dateFrom, string $dateTo): void
    {
        $builder
            ->when($selectedActivityId !== '', fn (Builder $query) => $query->where('attendance_activity_id', $selectedActivityId))
            ->when($selectedStatus !== '', fn (Builder $query) => $query->where('status', $selectedStatus))
            ->when($dateFrom !== '', fn (Builder $query) => $query->whereDate('session_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn (Builder $query) => $query->whereDate('session_date', '<=', $dateTo));
    }

    protected function activityOptions($currentUser)
    {
        return AttendanceActivity::query()
            ->visibleTo($currentUser)
            ->orderBy('status')
            ->orderBy('start_time')
            ->orderBy('name')
            ->limit(500)
            ->get();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    protected function statusOptions(): array
    {
        return [
            ['value' => AttendanceSession::STATUS_DRAFT, 'label' => 'Draft'],
            ['value' => AttendanceSession::STATUS_OPEN, 'label' => 'Dibuka'],
            ['value' => AttendanceSession::STATUS_COMPLETED, 'label' => 'Selesai'],
        ];
    }
}
