<?php

namespace App\Policies;

use App\Models\TahfidzSession;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TahfidzSessionPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return Response::allow();
    }

    public function view(User $user, TahfidzSession $session): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $session->tenant_id || $user->tenant_id !== $session->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses setoran dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function create(User $user): Response
    {
        if (! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return Response::allow();
    }
}
