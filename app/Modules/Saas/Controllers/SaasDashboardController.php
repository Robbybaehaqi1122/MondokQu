<?php

namespace App\Modules\Saas\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\Tenant;
use App\Models\TenantBillingNote;
use App\Models\User;
use Illuminate\View\View;

class SaasDashboardController extends Controller
{
    /**
     * Display the SaaS platform dashboard for internal superadmin use.
     */
    public function index(): View
    {
        abort_unless(request()->user()?->isSuperAdmin(), 403);

        $tenants = Tenant::query()
            ->with('owner')
            ->latest()
            ->paginate(10);

        $expiringTenants = Tenant::query()
            ->with('owner')
            ->where(function ($query) {
                $query
                    ->where(fn ($q) => $q
                        ->where('subscription_status', Tenant::SUBSCRIPTION_TRIAL)
                        ->whereBetween('trial_ends_at', [now(), now()->addDays(7)]))
                    ->orWhere(fn ($q) => $q
                        ->where('subscription_status', Tenant::SUBSCRIPTION_ACTIVE)
                        ->whereBetween('subscription_ends_at', [now(), now()->addDays(7)]))
                    ->orWhere(fn ($q) => $q
                        ->where('subscription_status', Tenant::SUBSCRIPTION_GRACE)
                        ->whereBetween('grace_ends_at', [now(), now()->addDays(7)]));
            })
            ->latest()
            ->get();

        return view('modules.saas.dashboard', [
            'tenants' => $tenants,
            'expiringTenants' => $expiringTenants,
            'tenantGrowthChart' => $this->buildTenantGrowthChart(),
            'revenueChart' => $this->buildRevenueChart(),
            'stats' => [
                'total_tenants' => Tenant::query()->count(),
                'new_tenants_this_month' => Tenant::query()
                    ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
                'trial_tenants' => Tenant::query()->where('subscription_status', Tenant::SUBSCRIPTION_TRIAL)->count(),
                'active_tenants' => Tenant::query()->where('subscription_status', Tenant::SUBSCRIPTION_ACTIVE)->count(),
                'grace_tenants' => Tenant::query()->where('subscription_status', Tenant::SUBSCRIPTION_GRACE)->count(),
                'expired_tenants' => Tenant::query()->where('subscription_status', Tenant::SUBSCRIPTION_EXPIRED)->count(),
                'deleting_tenants' => Tenant::query()->where('subscription_status', Tenant::SUBSCRIPTION_DELETING)->count(),
                'platform_users' => User::query()->count(),
                'total_santri' => Santri::query()->count(),
                'total_revenue' => (int) TenantBillingNote::query()->sum('amount'),
                'revenue_this_month' => (int) TenantBillingNote::query()
                    ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('amount'),
            ],
        ]);
    }

    /**
     * Build monthly tenant acquisition and cumulative growth data for the current year.
     *
     * @return array<int, array{label: string, new_tenants: int, total_tenants: int}>
     */
    protected function buildTenantGrowthChart(): array
    {
        $startOfYear = now()->startOfYear();
        $runningTotal = Tenant::query()
            ->where('created_at', '<', $startOfYear)
            ->count();

        return collect(range(0, 11))
            ->map(function (int $offset) use (&$runningTotal, $startOfYear): array {
                $month = $startOfYear->copy()->addMonths($offset);

                if ($month->isAfter(now())) {
                    return [
                        'label' => $month->translatedFormat('M'),
                        'new_tenants' => 0,
                        'total_tenants' => $runningTotal,
                    ];
                }

                $newTenants = Tenant::query()
                    ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->count();

                $runningTotal += $newTenants;

                return [
                    'label' => $month->translatedFormat('M'),
                    'new_tenants' => $newTenants,
                    'total_tenants' => $runningTotal,
                ];
            })
            ->all();
    }

    /**
     * Build monthly platform revenue data from tenant billing notes for the current year.
     *
     * @return array<int, array{label: string, amount: float}>
     */
    protected function buildRevenueChart(): array
    {
        $startOfYear = now()->startOfYear();

        return collect(range(0, 11))
            ->map(function (int $offset) use ($startOfYear): array {
                $month = $startOfYear->copy()->addMonths($offset);

                if ($month->isAfter(now())) {
                    return [
                        'label' => $month->translatedFormat('M'),
                        'amount' => 0,
                    ];
                }

                return [
                    'label' => $month->translatedFormat('M'),
                    'amount' => (int) TenantBillingNote::query()
                        ->whereBetween('paid_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                        ->sum('amount'),
                ];
            })
            ->all();
    }
}
