<?php

namespace App\Policies;

use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MataPelajaranPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return Response::allow();
    }

    public function view(User $user, MataPelajaran $mataPelajaran): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $mataPelajaran->tenant_id || $user->tenant_id !== $mataPelajaran->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses mata pelajaran dari tenant pondok lain.');
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

    public function update(User $user, MataPelajaran $mataPelajaran): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $mataPelajaran->tenant_id || $user->tenant_id !== $mataPelajaran->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah mata pelajaran dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, MataPelajaran $mataPelajaran): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $mataPelajaran->tenant_id || $user->tenant_id !== $mataPelajaran->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus mata pelajaran dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
