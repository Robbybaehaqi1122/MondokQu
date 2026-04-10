<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
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
        $roles = Role::query()
            ->withCount('users')
            ->orderBy('name')
            ->get();

        $maxRoleUsers = max(1, (int) $roles->max('users_count'));

        return view('admin.dashboard', [
            'loginCountToday' => ActivityLog::query()
                ->where('action', 'login_success')
                ->whereDate('created_at', today())
                ->count(),
            'newUsersThisWeek' => User::query()
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
                'total_users' => User::query()->count(),
                'active_users' => User::query()->where('status', User::STATUS_ACTIVE)->count(),
                'inactive_users' => User::query()->where('status', User::STATUS_INACTIVE)->count(),
                'suspended_users' => User::query()->where('status', User::STATUS_SUSPENDED)->count(),
                'never_logged_in_users' => User::query()->whereNull('last_login_at')->count(),
            ],
        ]);
    }
}
