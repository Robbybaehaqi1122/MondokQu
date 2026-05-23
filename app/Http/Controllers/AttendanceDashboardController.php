<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Santri;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $today = today();
        $issueStatuses = [
            AttendanceRecord::STATUS_PERMISSION,
            AttendanceRecord::STATUS_SICK,
            AttendanceRecord::STATUS_ABSENT,
            AttendanceRecord::STATUS_LATE,
        ];

        $activeSantriCount = Santri::query()
            ->visibleTo($currentUser)
            ->where('status', Santri::STATUS_ACTIVE)
            ->count();

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

        $attentionSantris = $this->recordBaseQuery($currentUser)
            ->whereDate('attendance_sessions.session_date', '>=', $today->copy()->subDays(6)->toDateString())
            ->whereDate('attendance_sessions.session_date', '<=', $today->toDateString())
            ->whereIn('attendance_records.status', $issueStatuses)
            ->join('santris as attention_santris', function (JoinClause $join): void {
                $join
                    ->on('attendance_records.santri_id', '=', 'attention_santris.id')
                    ->on('attendance_records.tenant_id', '=', 'attention_santris.tenant_id');
            })
            ->select('attention_santris.id', 'attention_santris.full_name', 'attention_santris.nis')
            ->selectRaw('COUNT(*) as issue_total')
            ->selectRaw('SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as permission_count', [AttendanceRecord::STATUS_PERMISSION])
            ->selectRaw('SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as sick_count', [AttendanceRecord::STATUS_SICK])
            ->selectRaw('SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as absent_count', [AttendanceRecord::STATUS_ABSENT])
            ->selectRaw('SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as late_count', [AttendanceRecord::STATUS_LATE])
            ->groupBy('attention_santris.id', 'attention_santris.full_name', 'attention_santris.nis')
            ->orderByDesc('issue_total')
            ->orderByDesc('absent_count')
            ->limit(8)
            ->get();

        $sessionsNeedingInput = $todaySessions->filter(
            fn (AttendanceSession $session): bool => $session->status !== AttendanceSession::STATUS_COMPLETED
                && (int) $session->records_count < $activeSantriCount
        );

        return view('attendance.dashboard', [
            'activeSantriCount' => $activeSantriCount,
            'attentionSantris' => $attentionSantris,
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
        ]);
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
