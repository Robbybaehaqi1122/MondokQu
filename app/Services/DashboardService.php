<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\Santri;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function buildCachedDashboardData(?User $currentUser): array
    {
        $roles = Role::query()
            ->when(
                ! $currentUser?->isSuperAdmin(),
                fn ($query) => $query->forTenant($currentUser?->tenant_id),
            )
            ->withCount([
                'users' => fn ($query) => $query->visibleTo($currentUser),
            ])
            ->orderBy('name')
            ->get();

        if ($currentUser?->isSuperAdmin()) {
            $roles = $roles
                ->groupBy('name')
                ->map(fn ($group) => tap($group->first(), fn ($role) => $role->users_count = $group->sum('users_count')))
                ->values();
        }

        $maxRoleUsers = max(1, (int) $roles->max('users_count'));
        $santriBaseQuery = Santri::query()->visibleTo($currentUser);
        $invoiceBaseQuery = SantriInvoice::query()->visibleTo($currentUser);
        $paymentBaseQuery = SantriPayment::query()->visibleTo($currentUser);
        $userBaseQuery = User::query()->visibleTo($currentUser);
        $userStats = $this->buildUserStats(clone $userBaseQuery);
        $santriStats = $this->buildSantriStats(clone $santriBaseQuery);

        return [
            'loginCountToday' => ActivityLog::query()
                ->visibleTo($currentUser)
                ->where('action', 'login_success')
                ->whereDate('created_at', today())
                ->count(),
            'newSantriThisMonth' => $santriStats['new_santri_this_month'],
            'newUsersThisWeek' => $userStats['new_users_this_week'],
            'roleDistribution' => $roles->map(function (Role $role) use ($maxRoleUsers): array {
                return [
                    'name' => $role->name,
                    'count' => $role->users_count,
                    'percentage' => (int) round(($role->users_count / $maxRoleUsers) * 100),
                ];
            }),
            'roomDistribution' => $this->buildRoomDistribution(clone $santriBaseQuery),
            'entryYearDistribution' => $this->buildEntryYearDistribution(clone $santriBaseQuery),
            'monthlyRevenue' => $this->buildMonthlyRevenue(clone $paymentBaseQuery),
            'topOverdueInvoices' => $this->buildTopOverdueInvoices(clone $invoiceBaseQuery),
            'recentUsers' => (clone $userBaseQuery)
                ->with('roles')
                ->orderByDesc('last_login_at')
                ->orderBy('name')
                ->limit(5)
                ->get(),
            'recentSantri' => (clone $santriBaseQuery)
                ->with('room')
                ->latest()
                ->limit(5)
                ->get(),
            'stats' => $userStats->only([
                'total_users', 'active_users', 'inactive_users', 'suspended_users', 'never_logged_in_users',
            ])->all(),
            'santriStats' => $santriStats->only([
                'total_santri', 'active_santri', 'leave_santri', 'alumni_santri', 'exited_santri',
            ])->all(),
            'financeStats' => $this->buildFinanceStats(clone $invoiceBaseQuery, clone $paymentBaseQuery),
        ];
    }

    public function buildUserStats($query): Collection
    {
        $row = $query
            ->selectRaw('COUNT(*) as total_users')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_users', [User::STATUS_ACTIVE])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as inactive_users', [User::STATUS_INACTIVE])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as suspended_users', [User::STATUS_SUSPENDED])
            ->selectRaw('SUM(CASE WHEN last_login_at IS NULL THEN 1 ELSE 0 END) as never_logged_in_users')
            ->selectRaw('SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_users_this_week', [now()->startOfWeek()])
            ->first();

        return collect([
            'total_users' => (int) ($row?->total_users ?? 0),
            'active_users' => (int) ($row?->active_users ?? 0),
            'inactive_users' => (int) ($row?->inactive_users ?? 0),
            'suspended_users' => (int) ($row?->suspended_users ?? 0),
            'never_logged_in_users' => (int) ($row?->never_logged_in_users ?? 0),
            'new_users_this_week' => (int) ($row?->new_users_this_week ?? 0),
        ]);
    }

    public function buildSantriStats($query): Collection
    {
        $row = $query
            ->selectRaw('COUNT(*) as total_santri')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_santri', [Santri::STATUS_ACTIVE])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as leave_santri', [Santri::STATUS_LEAVE])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as alumni_santri', [Santri::STATUS_ALUMNI])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as exited_santri', [Santri::STATUS_EXITED])
            ->selectRaw('SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_santri_this_month', [now()->startOfMonth()])
            ->first();

        return collect([
            'total_santri' => (int) ($row?->total_santri ?? 0),
            'active_santri' => (int) ($row?->active_santri ?? 0),
            'leave_santri' => (int) ($row?->leave_santri ?? 0),
            'alumni_santri' => (int) ($row?->alumni_santri ?? 0),
            'exited_santri' => (int) ($row?->exited_santri ?? 0),
            'new_santri_this_month' => (int) ($row?->new_santri_this_month ?? 0),
        ]);
    }

    public function buildFinanceStats($invoiceQuery, $paymentQuery): array
    {
        $invoiceStats = $invoiceQuery
            ->selectRaw('COALESCE(SUM(CASE WHEN status != ? THEN amount - paid_amount ELSE 0 END), 0) as outstanding_amount', [SantriInvoice::STATUS_PAID])
            ->selectRaw('SUM(CASE WHEN status != ? AND due_date < ? THEN 1 ELSE 0 END) as overdue_invoices', [SantriInvoice::STATUS_PAID, now()->toDateString()])
            ->first();

        return [
            'paid_this_month' => $paymentQuery
                ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'),
            'outstanding_amount' => (int) ($invoiceStats?->outstanding_amount ?? 0),
            'overdue_invoices' => (int) ($invoiceStats?->overdue_invoices ?? 0),
        ];
    }

    public function buildRoomDistribution($query): Collection
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
            ->selectRaw('room_name, COUNT(*) as santri_count')
            ->groupBy('room_name')
            ->orderByDesc('santri_count')
            ->orderBy('room_name')
            ->limit(5)
            ->get()
            ->map(fn ($item): array => [
                'room_name' => (string) $item->room_name,
                'santri_count' => (int) $item->santri_count,
            ]);
    }

    public function buildEntryYearDistribution($query): Collection
    {
        return $query
            ->selectRaw("COALESCE(CAST(entry_year AS CHAR), 'Belum diatur') as entry_year, COUNT(*) as santri_count")
            ->groupBy('entry_year')
            ->orderByDesc('entry_year')
            ->limit(5)
            ->get()
            ->map(fn ($item): array => [
                'entry_year' => (string) $item->entry_year,
                'santri_count' => (int) $item->santri_count,
            ]);
    }

    public function buildMonthlyRevenue($query): Collection
    {
        $months = collect(range(5, 0))
            ->map(fn (int $monthsAgo) => now()->subMonths($monthsAgo)->startOfMonth());
        $totals = (clone $query)
            ->whereBetween('paid_at', [$months->first()?->copy()->startOfMonth(), now()->endOfMonth()])
            ->get(['paid_at', 'amount'])
            ->groupBy(fn (SantriPayment $payment): string => $payment->paid_at->format('Y-m'))
            ->map(fn (Collection $payments): int => $payments->sum('amount'));
        $maxTotal = max(1, $totals->max());

        return $months->map(function ($month) use ($maxTotal, $totals): array {
            $total = (int) ($totals[$month->format('Y-m')] ?? 0);

            return [
                'label' => $month->translatedFormat('M Y'),
                'total' => $total,
                'percentage' => (int) round(($total / $maxTotal) * 100),
            ];
        });
    }

    public function buildTopOverdueInvoices($query): Collection
    {
        return $query
            ->with('santri')
            ->where('status', '!=', SantriInvoice::STATUS_PAID)
            ->whereDate('due_date', '<', now()->toDateString())
            ->select('*')
            ->selectRaw('(amount - paid_amount) as outstanding_amount')
            ->orderByDesc('outstanding_amount')
            ->orderBy('due_date')
            ->limit(5)
            ->get()
            ->map(fn (SantriInvoice $invoice): array => [
                'invoice_number' => $invoice->invoice_number,
                'santri_name' => $invoice->santri?->full_name ?? '-',
                'due_date' => $invoice->due_date?->translatedFormat('d M Y') ?? '-',
                'outstanding_amount' => (int) $invoice->outstanding_amount,
            ]);
    }
}
