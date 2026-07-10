<?php

namespace App\Policies;

use App\Models\TahfidzTarget;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TahfidzTargetPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('manage tahfidz')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk melihat target hafalan.');
    }

    public function view(User $user, TahfidzTarget $target): Response
    {
        if (! $user->can('manage tahfidz')) {
            return Response::deny('Anda tidak memiliki akses ke detail target hafalan.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $target->tenant_id || $user->tenant_id !== $target->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses target hafalan dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function create(User $user): Response
    {
        if (! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('manage tahfidz')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk membuat target hafalan.');
    }

    public function update(User $user, TahfidzTarget $target): Response
    {
        if (! $user->can('manage tahfidz')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah target hafalan.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $target->tenant_id || $user->tenant_id !== $target->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah target hafalan dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, TahfidzTarget $target): Response
    {
        if (! $user->can('manage tahfidz')) {
            return Response::deny('Anda tidak memiliki akses untuk menghapus target hafalan.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $target->tenant_id || $user->tenant_id !== $target->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus target hafalan dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
