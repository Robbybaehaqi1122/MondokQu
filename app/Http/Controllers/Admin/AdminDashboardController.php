<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Santri;
use App\Models\User;
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
        $tenantId = $currentUser && ! $currentUser->isSuperAdmin() ? $currentUser->tenant_id : null;

        $roles = Role::query()
            ->withCount('users')
            ->orderBy('name')
            ->get();

        $maxRoleUsers = max(1, (int) $roles->max('users_count'));

        return view('admin.dashboard', [
            'loginCountToday' => ActivityLog::query()
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->where('action', 'login_success')
                ->whereDate('created_at', today())
                ->count(),
            'newSantriThisMonth' => Santri::query()
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'newUsersThisWeek' => User::query()
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->where('created_at', '>=', now()->startOfWeek())
                ->count(),
            'roleDistribution' => $roles->map(function (Role $role) use ($maxRoleUsers): array {
                return [
                    'name' => $role->name,
                    'count' => $role->users_count,
                    'percentage' => (int) round(($role->users_count / $maxRoleUsers) * 100),
                ];
            }),
            'stats' => [
                'total_users' => User::query()->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))->count(),
                'active_users' => User::query()->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))->where('status', User::STATUS_ACTIVE)->count(),
                'inactive_users' => User::query()->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))->where('status', User::STATUS_INACTIVE)->count(),
                'suspended_users' => User::query()->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))->where('status', User::STATUS_SUSPENDED)->count(),
                'never_logged_in_users' => User::query()->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))->whereNull('last_login_at')->count(),
            ],
            'santriStats' => [
                'total_santri' => Santri::query()->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))->count(),
                'active_santri' => Santri::query()->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))->where('status', Santri::STATUS_ACTIVE)->count(),
                'alumni_santri' => Santri::query()->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))->where('status', Santri::STATUS_ALUMNI)->count(),
                'exited_santri' => Santri::query()->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))->where('status', Santri::STATUS_EXITED)->count(),
            ],
        ]);
    }
}
