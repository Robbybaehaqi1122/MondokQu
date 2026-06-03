<?php

namespace App\Policies;

use App\Models\AttendanceActivity;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttendanceActivityPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('manage absensi')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses ke kegiatan absensi.');
    }

    public function view(User $user, AttendanceActivity $activity): Response
    {
        if (! $user->can('manage absensi')) {
            return Response::deny('Anda tidak memiliki akses ke detail kegiatan.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $activity->tenant_id || $user->tenant_id !== $activity->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses kegiatan dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function create(User $user): Response
    {
        if (! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('manage absensi')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk menambah kegiatan.');
    }

    public function update(User $user, AttendanceActivity $activity): Response
    {
        if (! $user->can('manage absensi')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah kegiatan.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $activity->tenant_id || $user->tenant_id !== $activity->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah kegiatan dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, AttendanceActivity $activity): Response
    {
        if (! $user->can('manage absensi')) {
            return Response::deny('Anda tidak memiliki akses untuk menghapus kegiatan.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $activity->tenant_id || $user->tenant_id !== $activity->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus kegiatan dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
