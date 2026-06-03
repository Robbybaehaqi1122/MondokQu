<?php

namespace App\Policies;

use App\Models\AttendanceSession;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttendanceSessionPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('manage absensi')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses ke sesi absensi.');
    }

    public function view(User $user, AttendanceSession $session): Response
    {
        if (! $user->can('manage absensi')) {
            return Response::deny('Anda tidak memiliki akses ke detail sesi absensi.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $session->tenant_id || $user->tenant_id !== $session->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses sesi absensi dari tenant pondok lain.');
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
            : Response::deny('Anda tidak memiliki akses untuk membuat sesi absensi.');
    }

    public function update(User $user, AttendanceSession $session): Response
    {
        if (! $user->can('manage absensi')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah sesi absensi.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $session->tenant_id || $user->tenant_id !== $session->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah sesi absensi dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, AttendanceSession $session): Response
    {
        if (! $user->can('manage absensi')) {
            return Response::deny('Anda tidak memiliki akses untuk menghapus sesi absensi.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $session->tenant_id || $user->tenant_id !== $session->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus sesi absensi dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function inputRecords(User $user, AttendanceSession $session): Response
    {
        if (! $user->can('manage absensi')) {
            return Response::deny('Anda tidak memiliki akses untuk mengisi absensi.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $session->tenant_id || $user->tenant_id !== $session->tenant_id)) {
            return Response::deny('Anda tidak dapat mengisi absensi dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
