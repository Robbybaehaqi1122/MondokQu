<?php

namespace App\Policies;

use App\Models\NilaiSikap;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NilaiSikapPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return Response::allow();
    }

    public function view(User $user, NilaiSikap $nilaiSikap): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $nilaiSikap->tenant_id || $user->tenant_id !== $nilaiSikap->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses nilai sikap dari tenant pondok lain.');
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

    public function update(User $user, NilaiSikap $nilaiSikap): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $nilaiSikap->tenant_id || $user->tenant_id !== $nilaiSikap->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah nilai sikap dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, NilaiSikap $nilaiSikap): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $nilaiSikap->tenant_id || $user->tenant_id !== $nilaiSikap->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus nilai sikap dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
