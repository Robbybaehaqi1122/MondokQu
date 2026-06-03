<?php

namespace App\Policies;

use App\Models\PelanggaranKategori;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PelanggaranKategoriPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return Response::allow();
    }

    public function view(User $user, PelanggaranKategori $kategori): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $kategori->tenant_id || $user->tenant_id !== $kategori->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses kategori dari tenant pondok lain.');
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

    public function update(User $user, PelanggaranKategori $kategori): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $kategori->tenant_id || $user->tenant_id !== $kategori->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah kategori dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, PelanggaranKategori $kategori): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $kategori->tenant_id || $user->tenant_id !== $kategori->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus kategori dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
