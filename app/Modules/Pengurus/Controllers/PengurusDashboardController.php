<?php

namespace App\Modules\Pengurus\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Room;
use App\Models\Santri;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PengurusDashboardController extends Controller
{
    /**
     * Display the pengurus dashboard with comprehensive Santri management overview.
     */
    public function index(): View
    {
        $currentUser = request()->user();
        $tenant = $currentUser?->tenant;

        $santriBaseQuery = Santri::query()->visibleTo($currentUser);
        $roomBaseQuery = Room::query()->visibleTo($currentUser);
        $leaveRequestBaseQuery = LeaveRequest::query()->visibleTo($currentUser);
        $invoiceQuery = SantriInvoice::query()->visibleTo($currentUser);
        $paymentQuery = SantriPayment::query()->visibleTo($currentUser);
        $userQuery = User::query()->visibleTo($currentUser);

        $paidThisMonth = (clone $paymentQuery)
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');

        $outstandingRow = (clone $invoiceQuery)
            ->whereIn('status', [SantriInvoice::STATUS_PENDING, SantriInvoice::STATUS_PARTIAL])
            ->selectRaw('COALESCE(SUM(amount - COALESCE(paid_amount, 0)), 0) as outstanding')
            ->first();

        return view('pengurus.dashboard', [
            'tenantName' => $tenant?->name ?? 'Tanpa Tenant',
            'stats' => [
                'total_santri' => (clone $santriBaseQuery)->count(),
                'active_santri' => (clone $santriBaseQuery)->where('status', Santri::STATUS_ACTIVE)->count(),
                'leave_santri' => (clone $santriBaseQuery)->where('status', Santri::STATUS_LEAVE)->count(),
                'alumni_santri' => (clone $santriBaseQuery)->where('status', Santri::STATUS_ALUMNI)->count(),
                'exited_santri' => (clone $santriBaseQuery)->where('status', Santri::STATUS_EXITED)->count(),
            ],
            'leaveStats' => [
                'pending_approval' => (clone $leaveRequestBaseQuery)->where('status', LeaveRequest::STATUS_PENDING)->count(),
                'approved_today' => (clone $leaveRequestBaseQuery)->where('status', LeaveRequest::STATUS_APPROVED)->whereDate('approved_at', today())->count(),
                'currently_on_leave' => (clone $leaveRequestBaseQuery)
                    ->where('status', LeaveRequest::STATUS_APPROVED)
                    ->whereDate('start_date', '<=', today())
                    ->whereDate('end_date', '>=', today())
                    ->count(),
                'overdue_return' => (clone $leaveRequestBaseQuery)
                    ->where('status', LeaveRequest::STATUS_APPROVED)
                    ->whereDate('end_date', '<', today())
                    ->count(),
            ],
            'financeStats' => [
                'paid_this_month' => (int) $paidThisMonth,
                'total_outstanding' => (int) ($outstandingRow?->outstanding ?? 0),
                'overdue_invoices' => (clone $invoiceQuery)
                    ->whereIn('status', [SantriInvoice::STATUS_PENDING, SantriInvoice::STATUS_PARTIAL])
                    ->whereDate('due_date', '<', now())
                    ->count(),
                'total_invoices' => (clone $invoiceQuery)->count(),
            ],
            'userStats' => [
                'total_users' => (clone $userQuery)->count(),
                'active_users' => (clone $userQuery)->where('status', User::STATUS_ACTIVE)->count(),
            ],
            'genderStats' => [
                'male' => (clone $santriBaseQuery)->where('gender', Santri::GENDER_MALE)->count(),
                'female' => (clone $santriBaseQuery)->where('gender', Santri::GENDER_FEMALE)->count(),
            ],
            'roomStats' => $this->buildRoomStats(clone $santriBaseQuery),
            'roomSummary' => [
                'total' => (clone $roomBaseQuery)->count(),
                'active' => (clone $roomBaseQuery)->where('status', Room::STATUS_ACTIVE)->count(),
                'capacity' => (clone $roomBaseQuery)->sum('capacity'),
            ],
            'entryYearStats' => $this->buildEntryYearStats(clone $santriBaseQuery),
            'recentSantri' => (clone $santriBaseQuery)
                ->with('room')
                ->latest()
                ->limit(5)
                ->get(),
            'recentPayments' => (clone $paymentQuery)
                ->with(['santri'])
                ->latest('paid_at')
                ->limit(5)
                ->get(),
            'recentlyUpdatedSantri' => (clone $santriBaseQuery)
                ->with('room')
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * Build room distribution statistics.
     */
    protected function buildRoomStats($query): array
    {
        $roomSourceQuery = $query
            ->leftJoin('rooms', function ($join): void {
                $join
                    ->on('santris.room_id', '=', 'rooms.id')
                    ->on('santris.tenant_id', '=', 'rooms.tenant_id');
            })
            ->selectRaw("COALESCE(NULLIF(rooms.name, ''), 'Belum diatur') as room_name");

        return DB::query()
            ->fromSub($roomSourceQuery, 'room_source')
            ->selectRaw('room_name, COUNT(*) as count')
            ->groupBy('room_name')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(fn ($item) => [
                'room_name' => (string) $item->room_name,
                'count' => (int) $item->count,
            ])
            ->toArray();
    }

    /**
     * Build entry year statistics.
     */
    protected function buildEntryYearStats($query): array
    {
        return $query
            ->selectRaw("COALESCE(CAST(entry_year AS CHAR), 'Belum diatur') as entry_year, COUNT(*) as count")
            ->groupBy('entry_year')
            ->orderByDesc('entry_year')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'entry_year' => (string) $item->entry_year,
                'count' => (int) $item->count,
            ])
            ->toArray();
    }
}
