<?php

namespace App\Policies;

use App\Models\KesehatanRekamMedis;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KesehatanRekamMedisPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('manage kesehatan')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk melihat data rekam medis.');
    }

    public function view(User $user, KesehatanRekamMedis $rekamMedis): Response
    {
        if (! $user->can('manage kesehatan')) {
            return Response::deny('Anda tidak memiliki akses ke detail rekam medis.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $rekamMedis->tenant_id || $user->tenant_id !== $rekamMedis->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses data rekam medis dari tenant pondok lain.');
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
            : Response::deny('Anda tidak memiliki akses untuk menambah rekam medis.');
    }

    public function update(User $user, KesehatanRekamMedis $rekamMedis): Response
    {
        if (! $user->can('manage kesehatan')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah data rekam medis.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $rekamMedis->tenant_id || $user->tenant_id !== $rekamMedis->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah data rekam medis dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, KesehatanRekamMedis $rekamMedis): Response
    {
        if (! $user->can('manage kesehatan')) {
            return Response::deny('Anda tidak memiliki akses untuk menghapus data rekam medis.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $rekamMedis->tenant_id || $user->tenant_id !== $rekamMedis->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus data rekam medis dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
