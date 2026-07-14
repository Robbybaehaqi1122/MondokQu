<?php

namespace App\Policies;

use App\Models\KesehatanPemeriksaan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KesehatanPemeriksaanPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('manage kesehatan')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk melihat data pemeriksaan.');
    }

    public function view(User $user, KesehatanPemeriksaan $pemeriksaan): Response
    {
        if (! $user->can('manage kesehatan')) {
            return Response::deny('Anda tidak memiliki akses ke detail pemeriksaan.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $pemeriksaan->tenant_id || $user->tenant_id !== $pemeriksaan->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses data pemeriksaan dari tenant pondok lain.');
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
            : Response::deny('Anda tidak memiliki akses untuk menambah pemeriksaan.');
    }

    public function update(User $user, KesehatanPemeriksaan $pemeriksaan): Response
    {
        if (! $user->can('manage kesehatan')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah data pemeriksaan.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $pemeriksaan->tenant_id || $user->tenant_id !== $pemeriksaan->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah data pemeriksaan dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, KesehatanPemeriksaan $pemeriksaan): Response
    {
        if (! $user->can('manage kesehatan')) {
            return Response::deny('Anda tidak memiliki akses untuk menghapus data pemeriksaan.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $pemeriksaan->tenant_id || $user->tenant_id !== $pemeriksaan->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus data pemeriksaan dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
