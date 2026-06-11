<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttendanceActivity;
use App\Models\AttendanceRecord;
use App\Models\Room;
use App\Models\Santri;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'activity' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', Rule::in(AttendanceRecord::availableStatuses())],
            'santri' => ['nullable', 'integer'],
            'room' => ['nullable', 'integer'],
        ]);

        $dateTo = $validated['date_to'] ?? ($validated['date_from'] ?? now()->toDateString());
        $dateFrom = $validated['date_from'] ?? \Carbon\Carbon::parse($dateTo)->startOfMonth()->toDateString();
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'activity' => filled($validated['activity'] ?? null) ? (string) $validated['activity'] : '',
            'status' => filled($validated['status'] ?? null) ? (string) $validated['status'] : '',
            'santri' => filled($validated['santri'] ?? null) ? (string) $validated['santri'] : '',
            'room' => filled($validated['room'] ?? null) ? (string) $validated['room'] : '',
        ];

        $baseQuery = $this->filteredRecordsQuery($currentUser, $filters);
        $statusCounts = (clone $baseQuery)
            ->reorder()
            ->select('attendance_records.status', DB::raw('COUNT(*) as total'))
            ->groupBy('attendance_records.status')
            ->pluck('total', 'attendance_records.status');

        $issueStatuses = [
            AttendanceRecord::STATUS_PERMISSION,
            AttendanceRecord::STATUS_SICK,
            AttendanceRecord::STATUS_ABSENT,
            AttendanceRecord::STATUS_LATE,
        ];

        $records = (clone $baseQuery)
            ->orderByDesc('attendance_sessions.session_date')
            ->orderBy('attendance_records.id')
            ->paginate(25)
            ->withQueryString();

        $cacheTtl = 300;
        $tenantId = $currentUser?->tenant_id;

        $activityOptions = Cache::remember("attendance.filters.$tenantId.activities", $cacheTtl, fn () =>
            AttendanceActivity::query()
                ->visibleTo($currentUser)
                ->orderBy('name')
                ->get(['id', 'name'])
        );

        $santriOptions = Cache::remember("attendance.filters.$tenantId.santris", $cacheTtl, fn () =>
            Santri::query()
                ->visibleTo($currentUser)
                ->orderBy('full_name')
                ->limit(500)
                ->get(['id', 'nis', 'full_name'])
        );

        $roomOptions = Cache::remember("attendance.filters.$tenantId.rooms", $cacheTtl, fn () =>
            Room::query()
                ->visibleTo($currentUser)
                ->orderBy('name')
                ->get(['id', 'name'])
        );

        $statsQuery = (clone $baseQuery)->reorder()->select(
            DB::raw('COUNT(*) as records'),
            DB::raw('COUNT(DISTINCT attendance_records.attendance_session_id) as sessions'),
            DB::raw('COUNT(DISTINCT attendance_records.santri_id) as santris'),
            DB::raw("SUM(CASE WHEN attendance_records.status IN ('permission','sick','absent','late') THEN 1 ELSE 0 END) as issues"),
        )->first();

        return view('attendance.reports.index', [
            'activityOptions' => $activityOptions,
            'santriOptions' => $santriOptions,
            'roomOptions' => $roomOptions,
            'filters' => $filters,
            'records' => $records,
            'statusOptions' => AttendanceRecord::statusOptions(),
            'statusSummary' => collect(AttendanceRecord::statusOptions())->map(fn (array $statusOption): array => [
                'value' => $statusOption['value'],
                'label' => $statusOption['label'],
                'count' => (int) $statusCounts->get($statusOption['value'], 0),
            ]),
            'reportStats' => [
                'records' => (int) ($statsQuery?->records ?? 0),
                'sessions' => (int) ($statsQuery?->sessions ?? 0),
                'santris' => (int) ($statsQuery?->santris ?? 0),
                'issues' => (int) ($statsQuery?->issues ?? 0),
            ],
            'attentionSantris' => $this->attentionSantris((clone $baseQuery), $issueStatuses),
        ]);
    }

    /**
     * @param  array<string, string>  $filters
     */
    protected function filteredRecordsQuery($currentUser, array $filters): Builder
    {
        return AttendanceRecord::query()
            ->visibleTo($currentUser)
            ->select('attendance_records.*')
            ->join('attendance_sessions', function (JoinClause $join): void {
                $join
                    ->on('attendance_records.attendance_session_id', '=', 'attendance_sessions.id')
                    ->on('attendance_records.tenant_id', '=', 'attendance_sessions.tenant_id');
            })
            ->with(['session.activity', 'santri.room', 'recorder'])
            ->whereDate('attendance_sessions.session_date', '>=', $filters['date_from'])
            ->whereDate('attendance_sessions.session_date', '<=', $filters['date_to'])
            ->when($filters['activity'] !== '', fn (Builder $query) => $query
                ->where('attendance_sessions.attendance_activity_id', (int) $filters['activity']))
            ->when($filters['status'] !== '', fn (Builder $query) => $query
                ->where('attendance_records.status', $filters['status']))
            ->when($filters['santri'] !== '', fn (Builder $query) => $query
                ->where('attendance_records.santri_id', (int) $filters['santri']))
            ->when($filters['room'] !== '', fn (Builder $query) => $query
                ->whereHas('santri', fn (Builder $santriQuery) => $santriQuery->where('room_id', (int) $filters['room'])));
    }

    /**
     * @param  array<int, string>  $issueStatuses
     */
    protected function attentionSantris(Builder $query, array $issueStatuses)
    {
        return $query
            ->reorder()
            ->whereIn('attendance_records.status', $issueStatuses)
            ->join('santris as issue_santris', function (JoinClause $join): void {
                $join
                    ->on('attendance_records.santri_id', '=', 'issue_santris.id')
                    ->on('attendance_records.tenant_id', '=', 'issue_santris.tenant_id');
            })
            ->select('issue_santris.id', 'issue_santris.full_name', 'issue_santris.nis')
            ->selectRaw('COUNT(*) as issue_total')
            ->selectRaw('SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as permission_count', [AttendanceRecord::STATUS_PERMISSION])
            ->selectRaw('SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as sick_count', [AttendanceRecord::STATUS_SICK])
            ->selectRaw('SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as absent_count', [AttendanceRecord::STATUS_ABSENT])
            ->selectRaw('SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as late_count', [AttendanceRecord::STATUS_LATE])
            ->groupBy('issue_santris.id', 'issue_santris.full_name', 'issue_santris.nis')
            ->orderByDesc('issue_total')
            ->orderByDesc('absent_count')
            ->limit(10)
            ->get();
    }
}
