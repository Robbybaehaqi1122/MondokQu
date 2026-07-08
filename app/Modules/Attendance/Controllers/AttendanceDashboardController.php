<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Room;
use App\Models\Santri;
use App\Services\AttendanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AttendanceDashboardController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function index(Request $request): View
    {
        return view('attendance.dashboard', $this->getDashboardData($request));
    }

    public function api(Request $request): JsonResponse
    {
        $data = $this->getDashboardData($request);

        return response()->json([
            'activeSantriCount' => $data['activeSantriCount'],
            'attendedCount' => $data['attendedCount'],
            'notAttendedCount' => $data['notAttendedCount'],
            'attendancePercentage' => $data['attendancePercentage'],
            'dashboardStats' => $data['dashboardStats'],
            'statusSummary' => $data['statusSummary']->values(),
            'todaySessions' => $data['todaySessions']->map(function (AttendanceSession $session): array {
                return [
                    'id' => $session->id,
                    'activity_name' => $session->activity?->name,
                    'activity_time_range' => $session->activity?->timeRangeLabel(),
                    'status' => $session->status,
                    'status_label' => $session->statusLabel(),
                    'records_count' => (int) $session->records_count,
                    'issue_records_count' => (int) $session->issue_records_count,
                    'edit_url' => route('attendance.sessions.records.edit', $session),
                ];
            }),
            'notAttendedSantris' => $data['notAttendedSantris']->map(function (Santri $santri): array {
                return [
                    'id' => $santri->id,
                    'full_name' => $santri->full_name,
                    'nis' => $santri->nis,
                    'room_name' => $santri->room?->name,
                ];
            }),
            'attentionSantris' => $data['attentionSantris']->map(function ($santri): array {
                return [
                    'full_name' => $santri->full_name,
                    'nis' => $santri->nis,
                    'permission_count' => (int) $santri->permission_count,
                    'sick_count' => (int) $santri->sick_count,
                    'absent_count' => (int) $santri->absent_count,
                    'late_count' => (int) $santri->late_count,
                    'issue_total' => (int) $santri->issue_total,
                ];
            }),
            'sessionsNeedingInput' => $data['sessionsNeedingInput']->map(fn (AttendanceSession $session): int => $session->id)->values(),
        ]);
    }

    protected function getDashboardData(Request $request): array
    {
        $currentUser = $request->user();
        $today = today();
        $roomId = $request->integer('room') ?: null;

        $issueStatuses = [
            AttendanceRecord::STATUS_PERMISSION,
            AttendanceRecord::STATUS_SICK,
            AttendanceRecord::STATUS_ABSENT,
            AttendanceRecord::STATUS_LATE,
        ];

        $santriBase = Santri::query()
            ->visibleTo($currentUser)
            ->where('status', Santri::STATUS_ACTIVE);

        if ($roomId) {
            $santriBase->where('room_id', $roomId);
        }

        $activeSantriCount = (clone $santriBase)->count();

        $attendedTodayIds = $this->recordBaseQuery($currentUser)
            ->whereDate('attendance_sessions.session_date', $today->toDateString())
            ->distinct()
            ->pluck('attendance_records.santri_id');

        $attendedCount = $attendedTodayIds->count();
        $notAttendedCount = $activeSantriCount - $attendedCount;
        $attendancePercentage = $activeSantriCount > 0
            ? round(($attendedCount / $activeSantriCount) * 100, 1)
            : 0;

        $notAttendedSantris = (clone $santriBase)
            ->whereNotIn('id', $attendedTodayIds)
            ->with('room')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nis', 'room_id']);

        $roomOptions = Room::query()
            ->visibleTo($currentUser)
            ->orderBy('name')
            ->get(['id', 'name']);

        $todaySessions = AttendanceSession::query()
            ->visibleTo($currentUser)
            ->with(['activity.responsibleUser'])
            ->withCount([
                'records',
                'records as issue_records_count' => fn (Builder $query) => $query->whereIn('status', $issueStatuses),
            ])
            ->whereDate('session_date', $today->toDateString())
            ->orderBy('status')
            ->orderBy('id')
            ->get();

        $todayStatusCounts = $this->recordBaseQuery($currentUser)
            ->whereDate('attendance_sessions.session_date', $today->toDateString())
            ->select('attendance_records.status', DB::raw('COUNT(*) as total'))
            ->groupBy('attendance_records.status')
            ->pluck('total', 'attendance_records.status');

        $attentionSantris = $this->attendanceService->attentionSantris(
            query: $this->recordBaseQuery($currentUser)
                ->whereDate('attendance_sessions.session_date', '>=', $today->copy()->subDays(6)->toDateString())
                ->whereDate('attendance_sessions.session_date', '<=', $today->toDateString()),
            issueStatuses: $issueStatuses,
            limit: 8,
        );

        $sessionsNeedingInput = $todaySessions->filter(
            fn (AttendanceSession $session): bool => $session->status !== AttendanceSession::STATUS_COMPLETED
                && (int) $session->records_count < $activeSantriCount
        );

        return [
            'activeSantriCount' => $activeSantriCount,
            'attendedCount' => $attendedCount,
            'notAttendedCount' => $notAttendedCount,
            'attendancePercentage' => $attendancePercentage,
            'notAttendedSantris' => $notAttendedSantris,
            'attentionSantris' => $attentionSantris,
            'roomOptions' => $roomOptions,
            'selectedRoomId' => $roomId,
            'dashboardStats' => [
                'sessions_today' => $todaySessions->count(),
                'open_sessions' => $todaySessions->where('status', AttendanceSession::STATUS_OPEN)->count(),
                'needs_input' => $sessionsNeedingInput->count(),
                'completed_sessions' => $todaySessions->where('status', AttendanceSession::STATUS_COMPLETED)->count(),
            ],
            'issueStatuses' => $issueStatuses,
            'sessionsNeedingInput' => $sessionsNeedingInput,
            'statusOptions' => AttendanceRecord::statusOptions(),
            'statusSummary' => collect(AttendanceRecord::statusOptions())->map(fn (array $statusOption): array => [
                'value' => $statusOption['value'],
                'label' => $statusOption['label'],
                'count' => (int) $todayStatusCounts->get($statusOption['value'], 0),
            ]),
            'today' => $today,
            'todaySessions' => $todaySessions,
        ];
    }

    protected function recordBaseQuery($currentUser): Builder
    {
        return AttendanceRecord::query()
            ->visibleTo($currentUser)
            ->join('attendance_sessions', function (JoinClause $join): void {
                $join
                    ->on('attendance_records.attendance_session_id', '=', 'attendance_sessions.id')
                    ->on('attendance_records.tenant_id', '=', 'attendance_sessions.tenant_id');
            });
    }
}
