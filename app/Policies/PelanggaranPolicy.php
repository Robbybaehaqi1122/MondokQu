<?php

namespace App\Policies;

use App\Models\Pelanggaran;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PelanggaranPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return Response::allow();
    }

    public function view(User $user, Pelanggaran $pelanggaran): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $pelanggaran->tenant_id || $user->tenant_id !== $pelanggaran->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses pelanggaran dari tenant pondok lain.');
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

    public function delete(User $user, Pelanggaran $pelanggaran): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $pelanggaran->tenant_id || $user->tenant_id !== $pelanggaran->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus pelanggaran dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
