<?php

namespace App\Modules\Saas\Controllers;

use App\Http\Controllers\Controller;
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

        return view('modules.saas.dashboard', [
            'tenants' => $tenants,
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
                'total_revenue' => (int) TenantBillingNote::query()->sum('amount'),
                'revenue_this_month' => (int) TenantBillingNote::query()
                    ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('amount'),
            ],
        ]);
    }

    /**
     * Build monthly tenant acquisition and cumulative growth data.
     *
     * @return array<int, array{label: string, new_tenants: int, total_tenants: int}>
     */
    protected function buildTenantGrowthChart(int $months = 6): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);
        $runningTotal = Tenant::query()
            ->where('created_at', '<', $start)
            ->count();

        return collect(range(0, $months - 1))
            ->map(function (int $offset) use (&$runningTotal, $start): array {
                $month = $start->copy()->addMonths($offset);
                $newTenants = Tenant::query()
                    ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->count();

                $runningTotal += $newTenants;

                return [
                    'label' => $month->translatedFormat('M Y'),
                    'new_tenants' => $newTenants,
                    'total_tenants' => $runningTotal,
                ];
            })
            ->all();
    }

    /**
     * Build monthly platform revenue data from tenant billing notes.
     *
     * @return array<int, array{label: string, amount: float}>
     */
    protected function buildRevenueChart(int $months = 6): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        return collect(range(0, $months - 1))
            ->map(function (int $offset) use ($start): array {
                $month = $start->copy()->addMonths($offset);

                return [
                    'label' => $month->translatedFormat('M Y'),
                    'amount' => (int) TenantBillingNote::query()
                        ->whereBetween('paid_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                        ->sum('amount'),
                ];
            })
            ->all();
    }
}
