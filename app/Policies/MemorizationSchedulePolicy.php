<?php

namespace App\Policies;

use App\Models\MemorizationSchedule;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MemorizationSchedulePolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('manage tahfidz')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk melihat jadwal tahfidz.');
    }

    public function view(User $user, MemorizationSchedule $schedule): Response
    {
        if (! $user->can('manage tahfidz')) {
            return Response::deny('Anda tidak memiliki akses ke detail jadwal tahfidz.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $schedule->tenant_id || $user->tenant_id !== $schedule->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses jadwal tahfidz dari tenant pondok lain.');
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
            : Response::deny('Anda tidak memiliki akses untuk membuat jadwal tahfidz.');
    }

    public function update(User $user, MemorizationSchedule $schedule): Response
    {
        if (! $user->can('manage tahfidz')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah jadwal tahfidz.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $schedule->tenant_id || $user->tenant_id !== $schedule->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah jadwal tahfidz dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, MemorizationSchedule $schedule): Response
    {
        if (! $user->can('manage tahfidz')) {
            return Response::deny('Anda tidak memiliki akses untuk menghapus jadwal tahfidz.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $schedule->tenant_id || $user->tenant_id !== $schedule->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus jadwal tahfidz dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
