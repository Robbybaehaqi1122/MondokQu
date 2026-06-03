<?php

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ActivityLogPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        if ($user->isSuperAdmin()) {
            return Response::allow();
        }

        return $user->can('view activity logs')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses ke log aktivitas.');
    }

    public function view(User $user, ActivityLog $log): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        if ($user->isSuperAdmin()) {
            return Response::allow();
        }

        if (! $user->can('view activity logs')) {
            return Response::deny('Anda tidak memiliki akses ke log aktivitas.');
        }

        if ($log->tenant_id && $user->tenant_id !== $log->tenant_id) {
            return Response::deny('Anda tidak dapat mengakses log dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user): Response
    {
        return $user->isSuperAdmin()
            ? Response::allow()
            : Response::deny('Hanya Superadmin yang dapat menghapus log aktivitas.');
    }
}
