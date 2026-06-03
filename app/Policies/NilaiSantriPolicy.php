<?php

namespace App\Policies;

use App\Models\NilaiSantri;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NilaiSantriPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return Response::allow();
    }

    public function view(User $user, NilaiSantri $nilaiSantri): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $nilaiSantri->tenant_id || $user->tenant_id !== $nilaiSantri->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses nilai dari tenant pondok lain.');
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

    public function update(User $user, NilaiSantri $nilaiSantri): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $nilaiSantri->tenant_id || $user->tenant_id !== $nilaiSantri->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah nilai dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, NilaiSantri $nilaiSantri): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $nilaiSantri->tenant_id || $user->tenant_id !== $nilaiSantri->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus nilai dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
