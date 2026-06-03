<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttendanceRecordPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('manage absensi')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses ke data absensi.');
    }

    public function view(User $user, AttendanceRecord $record): Response
    {
        if (! $user->can('manage absensi')) {
            return Response::deny('Anda tidak memiliki akses ke detail absensi.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $record->tenant_id || $user->tenant_id !== $record->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses data absensi dari tenant pondok lain.');
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
            : Response::deny('Anda tidak memiliki akses untuk mengisi absensi.');
    }

    public function update(User $user, AttendanceRecord $record): Response
    {
        if (! $user->can('manage absensi')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah data absensi.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $record->tenant_id || $user->tenant_id !== $record->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah data absensi dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, AttendanceRecord $record): Response
    {
        if (! $user->can('manage absensi')) {
            return Response::deny('Anda tidak memiliki akses untuk menghapus data absensi.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $record->tenant_id || $user->tenant_id !== $record->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus data absensi dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
