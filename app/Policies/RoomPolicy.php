<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RoomPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('manage kamar')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses ke manajemen kamar.');
    }

    public function view(User $user, Room $room): Response
    {
        if (! $user->can('manage kamar')) {
            return Response::deny('Anda tidak memiliki akses ke detail kamar.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $room->tenant_id || $user->tenant_id !== $room->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses kamar dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function create(User $user): Response
    {
        if (! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('manage kamar')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk menambah kamar.');
    }

    public function update(User $user, Room $room): Response
    {
        if (! $user->can('manage kamar')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah kamar.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $room->tenant_id || $user->tenant_id !== $room->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah kamar dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, Room $room): Response
    {
        if (! $user->can('manage kamar')) {
            return Response::deny('Anda tidak memiliki akses untuk menghapus kamar.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $room->tenant_id || $user->tenant_id !== $room->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus kamar dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
