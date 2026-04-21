<?php

namespace App\Modules\Saas\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\View\View;

class SaasDashboardController extends Controller
{
    /**
     * Display the SaaS platform dashboard for internal superadmin use.
     */
    public function index(): View
    {
        $tenants = Tenant::query()
            ->with('owner')
            ->latest()
            ->paginate(10);

        return view('modules.saas.dashboard', [
            'tenants' => $tenants,
            'stats' => [
                'total_tenants' => Tenant::query()->count(),
                'trial_tenants' => Tenant::query()->where('subscription_status', Tenant::SUBSCRIPTION_TRIAL)->count(),
                'active_tenants' => Tenant::query()->where('subscription_status', Tenant::SUBSCRIPTION_ACTIVE)->count(),
                'grace_tenants' => Tenant::query()->where('subscription_status', Tenant::SUBSCRIPTION_GRACE)->count(),
                'expired_tenants' => Tenant::query()->where('subscription_status', Tenant::SUBSCRIPTION_EXPIRED)->count(),
                'platform_users' => User::query()->count(),
            ],
        ]);
    }
}
