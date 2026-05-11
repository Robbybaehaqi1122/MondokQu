<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Santri;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AdminDashboardController extends Controller
{
    /**
     * Display the monitoring dashboard for admin and superadmin.
     */
    public function index(): View
    {
        $currentUser = request()->user();
        $tenant = $currentUser?->tenant;
        $cachedData = $this->dashboardCacheTtl() > 0
            ? Cache::remember(
                $this->dashboardCacheKey($currentUser),
                now()->addSeconds($this->dashboardCacheTtl()),
                fn (): array => $this->buildCachedDashboardData($currentUser)
            )
            : $this->buildCachedDashboardData($currentUser);

        return view('admin.dashboard', [
            'tenantSummary' => $this->buildTenantSummary($tenant, $currentUser?->isSuperAdmin() ?? false),
            'tenantLifecycleNotice' => $this->buildTenantLifecycleNotice($tenant, $currentUser?->isSuperAdmin() ?? false),
            ...$cachedData,
        ]);
    }

    /**
     * Build dashboard data that is safe to cache for a short period.
     *
     * @return array<string, mixed>
     */
    protected function buildCachedDashboardData(?User $currentUser): array
    {
        $roles = Role::query()
            ->withCount([
                'users' => fn ($query) => $query->visibleTo($currentUser),
            ])
            ->orderBy('name')
            ->get();

        $maxRoleUsers = max(1, (int) $roles->max('users_count'));
        $santriBaseQuery = Santri::query()->visibleTo($currentUser);
        $invoiceBaseQuery = SantriInvoice::query()->visibleTo($currentUser);
        $paymentBaseQuery = SantriPayment::query()->visibleTo($currentUser);
        $userBaseQuery = User::query()->visibleTo($currentUser);

        return [
            'loginCountToday' => ActivityLog::query()
                ->visibleTo($currentUser)
                ->where('action', 'login_success')
                ->whereDate('created_at', today())
                ->count(),
            'newSantriThisMonth' => (clone $santriBaseQuery)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'newUsersThisWeek' => (clone $userBaseQuery)
                ->where('created_at', '>=', now()->startOfWeek())
                ->count(),
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
                ->latest()
                ->limit(5)
                ->get(),
            'stats' => [
                'total_users' => (clone $userBaseQuery)->count(),
                'active_users' => (clone $userBaseQuery)->where('status', User::STATUS_ACTIVE)->count(),
                'inactive_users' => (clone $userBaseQuery)->where('status', User::STATUS_INACTIVE)->count(),
                'suspended_users' => (clone $userBaseQuery)->where('status', User::STATUS_SUSPENDED)->count(),
                'never_logged_in_users' => (clone $userBaseQuery)->whereNull('last_login_at')->count(),
            ],
            'santriStats' => [
                'total_santri' => (clone $santriBaseQuery)->count(),
                'active_santri' => (clone $santriBaseQuery)->where('status', Santri::STATUS_ACTIVE)->count(),
                'leave_santri' => (clone $santriBaseQuery)->where('status', Santri::STATUS_LEAVE)->count(),
                'alumni_santri' => (clone $santriBaseQuery)->where('status', Santri::STATUS_ALUMNI)->count(),
                'exited_santri' => (clone $santriBaseQuery)->where('status', Santri::STATUS_EXITED)->count(),
            ],
            'financeStats' => [
                'paid_this_month' => (clone $paymentBaseQuery)
                    ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('amount'),
                'outstanding_amount' => (clone $invoiceBaseQuery)
                    ->where('status', '!=', SantriInvoice::STATUS_PAID)
                    ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')
                    ->value('total') ?? 0,
                'overdue_invoices' => (clone $invoiceBaseQuery)
                    ->where('status', '!=', SantriInvoice::STATUS_PAID)
                    ->whereDate('due_date', '<', now()->toDateString())
                    ->count(),
            ],
        ];
    }

    protected function dashboardCacheKey(?User $currentUser): string
    {
        $scope = $currentUser?->isSuperAdmin()
            ? 'platform'
            : 'tenant:'.($currentUser?->tenant_id ?? 'none');

        return implode(':', [
            'admin-dashboard',
            $scope,
            now()->format('Y-m-d'),
            now()->format('o-W'),
            now()->format('Y-m'),
        ]);
    }

    protected function dashboardCacheTtl(): int
    {
        return max(0, (int) config('cache.dashboard_ttl_seconds', 300));
    }

    /**
     * Build a subscription-oriented summary for the current tenant context.
     *
     * @return array<string, mixed>
     */
    protected function buildTenantSummary(?Tenant $tenant, bool $isSuperAdmin): array
    {
        if ($isSuperAdmin) {
            return [
                'title' => 'Akun Platform',
                'badge' => 'Superadmin',
                'badge_color' => 'azure',
                'description' => 'Anda sedang melihat dashboard lintas tenant sebagai pengelola platform.',
                'meta' => 'Gunakan panel SaaS untuk memantau trial, subscription, dan billing semua tenant.',
            ];
        }

        if (! $tenant) {
            return [
                'title' => 'Tenant Belum Terhubung',
                'badge' => 'Perlu Tindak Lanjut',
                'badge_color' => 'warning',
                'description' => 'Akun ini belum terhubung ke tenant pondok.',
                'meta' => 'Hubungi Superadmin agar akun Anda ditempatkan pada tenant yang benar.',
            ];
        }

        $statusLabel = match ($tenant->subscription_status) {
            Tenant::SUBSCRIPTION_TRIAL => 'Masa Trial',
            Tenant::SUBSCRIPTION_ACTIVE => 'Subscription Aktif',
            Tenant::SUBSCRIPTION_GRACE => 'Grace Period',
            Tenant::SUBSCRIPTION_EXPIRED => 'Expired',
            default => 'Status Tidak Diketahui',
        };

        $badgeColor = match ($tenant->subscription_status) {
            Tenant::SUBSCRIPTION_TRIAL => 'azure',
            Tenant::SUBSCRIPTION_ACTIVE => 'success',
            Tenant::SUBSCRIPTION_GRACE => 'warning',
            Tenant::SUBSCRIPTION_EXPIRED => 'danger',
            default => 'secondary',
        };

        $meta = match ($tenant->subscription_status) {
            Tenant::SUBSCRIPTION_TRIAL => 'Trial berakhir pada '.optional($tenant->trial_ends_at)->translatedFormat('d M Y H:i'),
            Tenant::SUBSCRIPTION_ACTIVE => 'Akses aktif sampai '.optional($tenant->subscription_ends_at)->translatedFormat('d M Y H:i'),
            Tenant::SUBSCRIPTION_GRACE => 'Grace period berakhir pada '.optional($tenant->grace_ends_at)->translatedFormat('d M Y H:i'),
            Tenant::SUBSCRIPTION_EXPIRED => 'Akses tenant saat ini sudah berakhir dan perlu aktivasi ulang.',
            default => 'Periksa status subscription tenant Anda.',
        };

        return [
            'title' => $tenant->name,
            'badge' => $statusLabel,
            'badge_color' => $badgeColor,
            'description' => 'Ringkasan operasional pondok Anda tampil dari data tenant yang sedang aktif.',
            'meta' => $meta,
        ];
    }

    /**
     * Build a proactive tenant lifecycle warning for dashboard display.
     *
     * @return array<string, string>|null
     */
    protected function buildTenantLifecycleNotice(?Tenant $tenant, bool $isSuperAdmin): ?array
    {
        if ($isSuperAdmin || ! $tenant) {
            return null;
        }

        $trialWarningDays = (int) config('saas.trial_warning_days', 3);
        $graceWarningDays = (int) config('saas.grace_warning_days', 3);

        if ($tenant->subscription_status === Tenant::SUBSCRIPTION_TRIAL && $tenant->trial_ends_at) {
            $remainingDays = max(0, now()->diffInDays($tenant->trial_ends_at, false));

            if ($remainingDays <= $trialWarningDays) {
                return [
                    'style' => 'warning',
                    'title' => 'Masa trial akan segera berakhir',
                    'message' => 'Tenant Anda masih dalam masa trial dan akan berakhir pada '.$tenant->trial_ends_at->translatedFormat('d M Y H:i').'.',
                    'action' => 'Segera hubungi admin platform untuk aktivasi subscription agar operasional pondok tidak terhenti.',
                ];
            }
        }

        if ($tenant->subscription_status === Tenant::SUBSCRIPTION_GRACE && $tenant->grace_ends_at) {
            $remainingDays = max(0, now()->diffInDays($tenant->grace_ends_at, false));

            return [
                'style' => $remainingDays <= $graceWarningDays ? 'danger' : 'warning',
                'title' => 'Tenant sedang dalam grace period',
                'message' => 'Akses tenant Anda masih dibuka sementara sampai '.$tenant->grace_ends_at->translatedFormat('d M Y H:i').'.',
                'action' => 'Konfirmasi pembayaran atau minta perpanjangan ke admin platform sebelum masa toleransi ini habis.',
            ];
        }

        return null;
    }

    /**
     * Build the room distribution list for quick occupancy monitoring.
     *
     * @return Collection<int, array{room_name: string, santri_count: int}>
     */
    protected function buildRoomDistribution($query): Collection
    {
        return $query
            ->selectRaw("COALESCE(NULLIF(room_name, ''), 'Belum diatur') as room_name, COUNT(*) as santri_count")
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

    /**
     * Build the entry-year distribution list for intake monitoring.
     *
     * @return Collection<int, array{entry_year: string, santri_count: int}>
     */
    protected function buildEntryYearDistribution($query): Collection
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

    /**
     * Build monthly revenue totals for the last six months.
     *
     * @return Collection<int, array{label: string, total: float, percentage: int}>
     */
    protected function buildMonthlyRevenue($query): Collection
    {
        $months = collect(range(5, 0))
            ->map(fn (int $monthsAgo) => now()->subMonths($monthsAgo)->startOfMonth());
        $totals = (clone $query)
            ->whereBetween('paid_at', [$months->first()?->copy()->startOfMonth(), now()->endOfMonth()])
            ->get(['paid_at', 'amount'])
            ->groupBy(fn (SantriPayment $payment): string => $payment->paid_at->format('Y-m'))
            ->map(fn (Collection $payments): float => (float) $payments->sum('amount'));
        $maxTotal = max(1, (float) $totals->max());

        return $months->map(function ($month) use ($maxTotal, $totals): array {
            $total = (float) ($totals[$month->format('Y-m')] ?? 0);

            return [
                'label' => $month->translatedFormat('M Y'),
                'total' => $total,
                'percentage' => (int) round(($total / $maxTotal) * 100),
            ];
        });
    }

    /**
     * Build the highest outstanding overdue invoices.
     *
     * @return Collection<int, array{invoice_number: string, santri_name: string, due_date: string, outstanding_amount: float}>
     */
    protected function buildTopOverdueInvoices($query): Collection
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
                'outstanding_amount' => (float) $invoice->outstanding_amount,
            ]);
    }
}
