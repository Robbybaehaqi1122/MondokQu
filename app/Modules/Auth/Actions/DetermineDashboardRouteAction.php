<?php

namespace App\Modules\Auth\Actions;

use App\Models\User;

class DetermineDashboardRouteAction
{
    /**
     * Resolve the dashboard route for the current user role.
     */
    public function handle(?User $user): string
    {
        if (! $user) {
            return route('login');
        }

        if ($user->password_change_required) {
            return route('profile.edit');
        }

        if ($user->hasAnyRole(['Superadmin', 'Admin'])) {
            return route('admin.dashboard');
        }

        if ($user->hasRole('Pengurus')) {
            return route('pengurus.dashboard');
        }

        if ($user->hasRole('Bendahara')) {
            return route('bendahara.dashboard');
        }

        if ($user->hasRole('Musyrif/Ustadz')) {
            return route('tahfidz.dashboard');
        }

        if ($user->hasRole('Wali Santri')) {
            return route('wali-santri.dashboard');
        }

        return route('dashboard.home');
    }
}
