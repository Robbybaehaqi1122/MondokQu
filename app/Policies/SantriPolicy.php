<?php

namespace App\Policies;

use App\Models\Santri;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SantriPolicy
{
    /**
     * Determine whether the user can view the santri list.
     */
    public function viewAny(User $user): Response
    {
        return $user->can('view santri')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk melihat data santri.');
    }

    /**
     * Determine whether the user can view a specific santri.
     */
    public function view(User $user, Santri $santri): Response
    {
        if (! $user->can('view santri')) {
            return Response::deny('Anda tidak memiliki akses ke detail santri.');
        }

        if ($user->tenant_id && $santri->tenant_id && $user->tenant_id !== $santri->tenant_id) {
            return Response::deny('Anda tidak dapat mengakses data santri dari tenant pondok lain.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can create santri records.
     */
    public function create(User $user): Response
    {
        return $user->can('create santri')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk menambah santri.');
    }

    /**
     * Determine whether the user can update santri records.
     */
    public function update(User $user, Santri $santri): Response
    {
        if (! $user->can('update santri')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah data santri.');
        }

        if ($user->tenant_id && $santri->tenant_id && $user->tenant_id !== $santri->tenant_id) {
            return Response::deny('Anda tidak dapat mengubah data santri dari tenant pondok lain.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete santri records.
     */
    public function delete(User $user, Santri $santri): Response
    {
        if (! $user->can('delete santri')) {
            return Response::deny('Anda tidak memiliki akses untuk menghapus data santri.');
        }

        if ($user->tenant_id && $santri->tenant_id && $user->tenant_id !== $santri->tenant_id) {
            return Response::deny('Anda tidak dapat menghapus data santri dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
