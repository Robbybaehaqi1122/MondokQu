<?php

namespace App\Policies;

use App\Models\KesehatanObat;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KesehatanObatPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('manage kesehatan')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk melihat data obat.');
    }

    public function view(User $user, KesehatanObat $obat): Response
    {
        if (! $user->can('manage kesehatan')) {
            return Response::deny('Anda tidak memiliki akses ke detail obat.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $obat->tenant_id || $user->tenant_id !== $obat->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses data obat dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function create(User $user): Response
    {
        if (! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('manage kesehatan')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk menambah obat.');
    }

    public function update(User $user, KesehatanObat $obat): Response
    {
        if (! $user->can('manage kesehatan')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah data obat.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $obat->tenant_id || $user->tenant_id !== $obat->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah data obat dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, KesehatanObat $obat): Response
    {
        if (! $user->can('manage kesehatan')) {
            return Response::deny('Anda tidak memiliki akses untuk menghapus data obat.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $obat->tenant_id || $user->tenant_id !== $obat->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus data obat dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
