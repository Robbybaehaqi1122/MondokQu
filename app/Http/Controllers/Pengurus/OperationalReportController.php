<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Room;
use App\Models\Santri;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class OperationalReportController extends Controller
{
    /**
     * Display simple room occupancy and leave request reports.
     */
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $selectedMonth = $this->resolveSelectedMonth($request);
        $monthStart = Carbon::createFromFormat('Y-m-d', $selectedMonth.'-01')->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $selectedStatus = $this->resolveSelectedStatus($request);
        $selectedSantriId = trim((string) $request->query('santri', ''));

        $roomReports = $this->buildRoomReports($currentUser);
        $roomSummary = $this->buildRoomSummary($roomReports);
        $statusOptions = $this->statusOptions();
        $statusLabels = collect($statusOptions)->pluck('label', 'value');
        $leaveBaseQuery = LeaveRequest::query()
            ->visibleTo($currentUser)
            ->with(['santri' => fn ($query) => $query->select('id', 'full_name', 'nis')]);

        $periodLeaveRequests = (clone $leaveBaseQuery)
            ->whereBetween('start_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->when($selectedSantriId !== '', fn (Builder $query) => $query->where('santri_id', (int) $selectedSantriId))
            ->get();

        $filteredPeriodLeaveRequests = $periodLeaveRequests
            ->when($selectedStatus !== '', fn (Collection $items) => $items->where('status', $selectedStatus))
            ->values();

        $yearLeaveRequests = (clone $leaveBaseQuery)
            ->whereBetween('start_date', [
                $monthStart->copy()->startOfYear()->toDateString(),
                $monthStart->copy()->endOfYear()->toDateString(),
            ])
            ->when($selectedStatus !== '', fn (Builder $query) => $query->where('status', $selectedStatus))
            ->when($selectedSantriId !== '', fn (Builder $query) => $query->where('santri_id', (int) $selectedSantriId))
            ->get();

        return view('pengurus.reports.index', [
            'filters' => [
                'month' => $selectedMonth,
                'status' => $selectedStatus,
                'santri' => $selectedSantriId,
            ],
            'roomReports' => $roomReports,
            'roomSummary' => $roomSummary,
            'leaveSummary' => [
                'total' => $filteredPeriodLeaveRequests->count(),
                'month_label' => $monthStart->translatedFormat('F Y'),
            ],
            'leaveStatusCounts' => $this->buildStatusCounts($periodLeaveRequests),
            'monthlyLeaveRecaps' => $this->buildMonthlyLeaveRecaps($yearLeaveRequests, (int) $monthStart->year, $statusLabels),
            'santriLeaveRecaps' => $this->buildSantriLeaveRecaps($filteredPeriodLeaveRequests),
            'statusOptions' => $statusOptions,
            'statusLabels' => $statusLabels,
            'santris' => Santri::query()
                ->visibleTo($currentUser)
                ->orderBy('full_name')
                ->limit(500)
                ->get(['id', 'full_name', 'nis']),
        ]);
    }

    protected function resolveSelectedMonth(Request $request): string
    {
        $selectedMonth = trim((string) $request->query('month', now()->format('Y-m')));

        try {
            Carbon::createFromFormat('Y-m-d', $selectedMonth.'-01');

            return $selectedMonth;
        } catch (\Throwable) {
            return now()->format('Y-m');
        }
    }

    protected function resolveSelectedStatus(Request $request): string
    {
        $selectedStatus = trim((string) $request->query('status', ''));

        return in_array($selectedStatus, LeaveRequest::availableStatuses(), true) ? $selectedStatus : '';
    }

    protected function buildRoomReports($currentUser): Collection
    {
        return Room::query()
            ->visibleTo($currentUser)
            ->withCount([
                'santris',
                'santris as active_santris_count' => fn (Builder $query) => $query->where('status', Santri::STATUS_ACTIVE),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (Room $room): array {
                $capacity = $room->capacity ? (int) $room->capacity : null;
                $activeCount = (int) $room->active_santris_count;

                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'status' => $room->status,
                    'status_label' => $room->statusLabel(),
                    'capacity' => $capacity,
                    'active_santris_count' => $activeCount,
                    'total_santris_count' => (int) $room->santris_count,
                    'remaining_capacity' => $capacity === null ? null : max(0, $capacity - $activeCount),
                    'occupancy_percentage' => $capacity ? min(100, (int) round(($activeCount / $capacity) * 100)) : null,
                    'is_over_capacity' => $capacity !== null && $activeCount > $capacity,
                ];
            });
    }

    protected function buildRoomSummary(Collection $roomReports): array
    {
        $totalCapacity = $roomReports->sum(fn (array $room) => $room['capacity'] ?? 0);
        $occupied = $roomReports->sum('active_santris_count');
        $available = $roomReports->sum(fn (array $room) => $room['remaining_capacity'] ?? 0);

        return [
            'total' => $roomReports->count(),
            'capacity' => $totalCapacity,
            'occupied' => $occupied,
            'available' => $available,
            'unlimited_rooms' => $roomReports->whereNull('capacity')->count(),
            'over_capacity' => $roomReports->where('is_over_capacity', true)->count(),
            'occupancy_percentage' => $totalCapacity > 0 ? min(100, (int) round(($occupied / $totalCapacity) * 100)) : null,
        ];
    }

    protected function buildStatusCounts(Collection $leaveRequests): Collection
    {
        return collect($this->statusOptions())->map(function (array $statusOption) use ($leaveRequests): array {
            return [
                'status' => $statusOption['value'],
                'label' => $statusOption['label'],
                'count' => $leaveRequests->where('status', $statusOption['value'])->count(),
            ];
        });
    }

    protected function buildMonthlyLeaveRecaps(Collection $leaveRequests, int $year, Collection $statusLabels): Collection
    {
        return collect(range(1, 12))->map(function (int $month) use ($leaveRequests, $year, $statusLabels): array {
            $monthlyRequests = $leaveRequests->filter(fn (LeaveRequest $leaveRequest): bool => (int) $leaveRequest->start_date->month === $month);
            $monthDate = Carbon::create($year, $month, 1);

            return [
                'month' => $monthDate->format('Y-m'),
                'month_label' => $monthDate->translatedFormat('M Y'),
                'total' => $monthlyRequests->count(),
                'statuses' => $statusLabels->keys()->mapWithKeys(
                    fn (string $status) => [$status => $monthlyRequests->where('status', $status)->count()]
                ),
            ];
        });
    }

    protected function buildSantriLeaveRecaps(Collection $leaveRequests): Collection
    {
        return $leaveRequests
            ->groupBy('santri_id')
            ->map(function (Collection $items): array {
                /** @var LeaveRequest $first */
                $first = $items->first();

                return [
                    'santri_name' => $first->santri?->full_name ?? '-',
                    'nis' => $first->santri?->nis ?? '-',
                    'total' => $items->count(),
                    'pending' => $items->where('status', LeaveRequest::STATUS_PENDING)->count(),
                    'approved' => $items->where('status', LeaveRequest::STATUS_APPROVED)->count(),
                    'rejected' => $items->where('status', LeaveRequest::STATUS_REJECTED)->count(),
                    'completed' => $items->where('status', LeaveRequest::STATUS_COMPLETED)->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    protected function statusOptions(): array
    {
        return [
            ['value' => LeaveRequest::STATUS_PENDING, 'label' => 'Menunggu'],
            ['value' => LeaveRequest::STATUS_APPROVED, 'label' => 'Disetujui'],
            ['value' => LeaveRequest::STATUS_REJECTED, 'label' => 'Ditolak'],
            ['value' => LeaveRequest::STATUS_COMPLETED, 'label' => 'Selesai'],
        ];
    }
}
