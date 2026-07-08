<?php

namespace App\Modules\Attendance\Controllers;

use App\Exports\AttendanceDetailExport;
use App\Exports\AttendanceRekapExport;
use App\Http\Controllers\Controller;
use App\Models\AttendanceActivity;
use App\Models\AttendanceRecord;
use App\Models\Room;
use App\Models\Santri;
use App\Services\AttendanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceReportController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}
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
            'view_mode' => ['nullable', 'string', Rule::in(['detail', 'rekap'])],
        ]);

        $viewMode = $validated['view_mode'] ?? 'detail';

        $dateTo = $validated['date_to'] ?? ($validated['date_from'] ?? now()->toDateString());
        $dateFrom = $validated['date_from'] ?? Carbon::parse($dateTo)->startOfMonth()->toDateString();
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'activity' => filled($validated['activity'] ?? null) ? (string) $validated['activity'] : '',
            'status' => filled($validated['status'] ?? null) ? (string) $validated['status'] : '',
            'santri' => filled($validated['santri'] ?? null) ? (string) $validated['santri'] : '',
            'room' => filled($validated['room'] ?? null) ? (string) $validated['room'] : '',
        ];

        $cacheTtl = 300;
        $tenantId = $currentUser?->tenant_id;

        $activityOptions = Cache::remember("attendance.filters.$tenantId.activities", $cacheTtl, fn () => AttendanceActivity::query()
            ->visibleTo($currentUser)
            ->orderBy('name')
            ->get(['id', 'name'])
        );

        $santriOptions = Cache::remember("attendance.filters.$tenantId.santris", $cacheTtl, fn () => Santri::query()
            ->visibleTo($currentUser)
            ->orderBy('full_name')
            ->limit(500)
            ->get(['id', 'nis', 'full_name'])
        );

        $roomOptions = Cache::remember("attendance.filters.$tenantId.rooms", $cacheTtl, fn () => Room::query()
            ->visibleTo($currentUser)
            ->orderBy('name')
            ->get(['id', 'name'])
        );

        if ($viewMode === 'rekap') {
            return $this->renderRekapView($currentUser, $filters, $activityOptions, $santriOptions, $roomOptions, $viewMode);
        }

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

        $statsQuery = (clone $baseQuery)->reorder()->select(
            DB::raw('COUNT(*) as records'),
            DB::raw('COUNT(DISTINCT attendance_records.attendance_session_id) as sessions'),
            DB::raw('COUNT(DISTINCT attendance_records.santri_id) as santris'),
            DB::raw("SUM(CASE WHEN attendance_records.status IN ('permission','sick','absent','late') THEN 1 ELSE 0 END) as issues"),
        )->first();

        return view('attendance.reports.index', [
            'viewMode' => $viewMode,
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

    public function exportPdf(Request $request)
    {
        $currentUser = $request->user();
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'activity' => ['nullable', 'integer'],
            'room' => ['nullable', 'integer'],
        ]);

        $dateTo = $validated['date_to'] ?? ($validated['date_from'] ?? now()->toDateString());
        $dateFrom = $validated['date_from'] ?? Carbon::parse($dateTo)->startOfMonth()->toDateString();
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'activity' => filled($validated['activity'] ?? null) ? (string) $validated['activity'] : '',
            'room' => filled($validated['room'] ?? null) ? (string) $validated['room'] : '',
        ];

        $rekapData = $this->getRekapData($currentUser, $filters);
        $monthName = Carbon::parse($dateFrom)->translatedFormat('F Y');
        $roomId = filled($filters['room'] ?? null) ? (int) $filters['room'] : null;
        $roomName = $roomId ? ($this->getRoomName($roomId) ?? '-') : 'Semua Kamar';

        $pdf = Pdf::loadView('attendance.reports.pdf', [
            'rekap' => $rekapData['rekap'],
            'monthName' => $monthName,
            'roomName' => $roomName,
        ]);

        return $pdf->download("rekap-absensi-{$monthName}.pdf");
    }

    public function exportRekap(Request $request, string $format)
    {
        $currentUser = $request->user();
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'activity' => ['nullable', 'integer'],
            'room' => ['nullable', 'integer'],
        ]);

        $dateTo = $validated['date_to'] ?? ($validated['date_from'] ?? now()->toDateString());
        $dateFrom = $validated['date_from'] ?? Carbon::parse($dateTo)->startOfMonth()->toDateString();
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'activity' => filled($validated['activity'] ?? null) ? (string) $validated['activity'] : '',
            'room' => filled($validated['room'] ?? null) ? (string) $validated['room'] : '',
        ];

        $rekapData = $this->getRekapData($currentUser, $filters);

        $export = new AttendanceRekapExport($currentUser, $rekapData['rekap'], $dateFrom, $dateTo);
        $writerType = $format === 'csv' ? ExcelWriter::CSV : ExcelWriter::XLSX;
        $extension = $format === 'csv' ? '.csv' : '.xlsx';

        return Excel::download($export, $export->filename().$extension, $writerType);
    }

    public function exportDetail(Request $request, string $format)
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
        $dateFrom = $validated['date_from'] ?? Carbon::parse($dateTo)->startOfMonth()->toDateString();
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'activity' => filled($validated['activity'] ?? null) ? (string) $validated['activity'] : '',
            'status' => filled($validated['status'] ?? null) ? (string) $validated['status'] : '',
            'santri' => filled($validated['santri'] ?? null) ? (string) $validated['santri'] : '',
            'room' => filled($validated['room'] ?? null) ? (string) $validated['room'] : '',
        ];

        $query = $this->filteredRecordsQuery($currentUser, $filters)
            ->orderByDesc('attendance_sessions.session_date')
            ->orderBy('attendance_records.id');

        $export = new AttendanceDetailExport($currentUser, $query);
        $writerType = $format === 'csv' ? ExcelWriter::CSV : ExcelWriter::XLSX;
        $extension = $format === 'csv' ? '.csv' : '.xlsx';

        return Excel::download($export, $export->filename().$extension, $writerType);
    }

    protected function renderRekapView($currentUser, array $filters, $activityOptions, $santriOptions, $roomOptions, string $viewMode): View
    {
        $rekapData = $this->getRekapData($currentUser, $filters);
        $year = Carbon::parse($filters['date_from'])->year;
        $roomId = filled($filters['room'] ?? null) ? (int) $filters['room'] : null;
        $chartData = $this->chartData($currentUser, $year, $roomId);

        return view('attendance.reports.index', [
            'viewMode' => $viewMode,
            'filters' => $filters,
            'activityOptions' => $activityOptions,
            'santriOptions' => $santriOptions,
            'roomOptions' => $roomOptions,
            'rekap' => $rekapData['rekap'],
            'chartData' => $chartData,
            'rekapStats' => $rekapData['stats'],
        ]);
    }

    /**
     * @return array{rekap: Collection, stats: array{total_santri: int, avg_percentage: float}}
     */
    protected function getRekapData($currentUser, array $filters): array
    {
        $dateFrom = $filters['date_from'];
        $dateTo = $filters['date_to'];

        $santriQuery = Santri::query()
            ->visibleTo($currentUser)
            ->where('status', Santri::STATUS_ACTIVE)
            ->orderBy('full_name');

        $roomId = filled($filters['room'] ?? null) ? (int) $filters['room'] : null;
        if ($roomId) {
            $santriQuery->where('room_id', $roomId);
        }

        $santris = $santriQuery->get(['id', 'full_name', 'nis', 'room_id']);
        $santriIds = $santris->pluck('id');

        $records = AttendanceRecord::query()
            ->visibleTo($currentUser)
            ->select('attendance_records.santri_id', 'attendance_records.status', DB::raw('COUNT(*) as count'))
            ->join('attendance_sessions', function (JoinClause $join): void {
                $join
                    ->on('attendance_records.attendance_session_id', '=', 'attendance_sessions.id')
                    ->on('attendance_records.tenant_id', '=', 'attendance_sessions.tenant_id');
            })
            ->whereIn('attendance_records.santri_id', $santriIds)
            ->whereDate('attendance_sessions.session_date', '>=', $dateFrom)
            ->whereDate('attendance_sessions.session_date', '<=', $dateTo)
            ->when(filled($filters['activity'] ?? null), fn ($q) => $q->where('attendance_sessions.attendance_activity_id', (int) $filters['activity']))
            ->groupBy('attendance_records.santri_id', 'attendance_records.status')
            ->get()
            ->groupBy('santri_id');

        $rekap = $santris->map(function (Santri $santri) use ($records): array {
            $santriRecords = $records->get($santri->id, collect());
            $present = (int) $santriRecords->where('status', AttendanceRecord::STATUS_PRESENT)->sum('count');
            $sick = (int) $santriRecords->where('status', AttendanceRecord::STATUS_SICK)->sum('count');
            $permission = (int) $santriRecords->where('status', AttendanceRecord::STATUS_PERMISSION)->sum('count');
            $absent = (int) $santriRecords->where('status', AttendanceRecord::STATUS_ABSENT)->sum('count');
            $late = (int) $santriRecords->where('status', AttendanceRecord::STATUS_LATE)->sum('count');
            $total = $present + $sick + $permission + $absent + $late;
            $percentage = $total > 0 ? round(($present / $total) * 100, 1) : 0;

            return [
                'id' => $santri->id,
                'full_name' => $santri->full_name,
                'nis' => $santri->nis,
                'room_name' => $santri->displayRoomName('-'),
                'present' => $present,
                'sick' => $sick,
                'permission' => $permission,
                'absent' => $absent,
                'late' => $late,
                'total' => $total,
                'percentage' => $percentage,
            ];
        })->sortByDesc('percentage')->values();

        return [
            'rekap' => $rekap,
            'stats' => [
                'total_santri' => $rekap->count(),
                'avg_percentage' => round($rekap->avg('percentage') ?: 0, 1),
            ],
        ];
    }

    protected function getRoomName(int $roomId): ?string
    {
        return Room::find($roomId)?->name;
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
        return $this->attendanceService->attentionSantris(
            query: $query,
            issueStatuses: $issueStatuses,
            alias: 'issue_santris',
            limit: 10,
        );
    }

    protected function chartData($currentUser, int $year, ?int $roomId): array
    {
        $months = range(1, 12);
        $labels = collect($months)->map(fn (int $m): string => Carbon::create()->month($m)->translatedFormat('M'));

        $santriQuery = Santri::query()
            ->visibleTo($currentUser)
            ->where('status', Santri::STATUS_ACTIVE);

        if ($roomId) {
            $santriQuery->where('room_id', $roomId);
        }

        $santriIds = $santriQuery->pluck('id');

        $presentData = [];
        $issueData = [];

        foreach ($months as $m) {
            $dateFrom = Carbon::create($year, $m, 1)->toDateString();
            $dateTo = Carbon::create($year, $m, 1)->endOfMonth()->toDateString();

            $counts = AttendanceRecord::query()
                ->visibleTo($currentUser)
                ->select('attendance_records.status', DB::raw('COUNT(*) as count'))
                ->join('attendance_sessions', function (JoinClause $join): void {
                    $join
                        ->on('attendance_records.attendance_session_id', '=', 'attendance_sessions.id')
                        ->on('attendance_records.tenant_id', '=', 'attendance_sessions.tenant_id');
                })
                ->whereIn('attendance_records.santri_id', $santriIds)
                ->whereDate('attendance_sessions.session_date', '>=', $dateFrom)
                ->whereDate('attendance_sessions.session_date', '<=', $dateTo)
                ->groupBy('attendance_records.status')
                ->pluck('count', 'status');

            $present = (int) ($counts[AttendanceRecord::STATUS_PRESENT] ?? 0);
            $issues = (int) ($counts[AttendanceRecord::STATUS_SICK] ?? 0)
                + (int) ($counts[AttendanceRecord::STATUS_PERMISSION] ?? 0)
                + (int) ($counts[AttendanceRecord::STATUS_ABSENT] ?? 0)
                + (int) ($counts[AttendanceRecord::STATUS_LATE] ?? 0);

            $presentData[] = $present;
            $issueData[] = $issues;
        }

        return [
            'labels' => $labels,
            'present' => $presentData,
            'issues' => $issueData,
        ];
    }
}
