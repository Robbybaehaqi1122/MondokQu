<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeaveRequestPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->canAny(['create izin', 'approve izin'])
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses ke pengajuan izin.');
    }

    public function view(User $user, LeaveRequest $leaveRequest): Response
    {
        if (! $user->canAny(['create izin', 'approve izin'])) {
            return Response::deny('Anda tidak memiliki akses ke detail izin.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $leaveRequest->tenant_id || $user->tenant_id !== $leaveRequest->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses izin dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function create(User $user): Response
    {
        if (! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('create izin')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk membuat izin.');
    }

    public function update(User $user, LeaveRequest $leaveRequest): Response
    {
        if (! $user->can('create izin')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah izin.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $leaveRequest->tenant_id || $user->tenant_id !== $leaveRequest->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah izin dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function approve(User $user, LeaveRequest $leaveRequest): Response
    {
        if (! $user->can('approve izin')) {
            return Response::deny('Anda tidak memiliki akses untuk menyetujui izin.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $leaveRequest->tenant_id || $user->tenant_id !== $leaveRequest->tenant_id)) {
            return Response::deny('Anda tidak dapat menyetujui izin dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function reject(User $user, LeaveRequest $leaveRequest): Response
    {
        if (! $user->can('approve izin')) {
            return Response::deny('Anda tidak memiliki akses untuk menolak izin.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $leaveRequest->tenant_id || $user->tenant_id !== $leaveRequest->tenant_id)) {
            return Response::deny('Anda tidak dapat menolak izin dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function complete(User $user, LeaveRequest $leaveRequest): Response
    {
        if (! $user->can('approve izin')) {
            return Response::deny('Anda tidak memiliki akses untuk menyelesaikan izin.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $leaveRequest->tenant_id || $user->tenant_id !== $leaveRequest->tenant_id)) {
            return Response::deny('Anda tidak dapat menyelesaikan izin dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, LeaveRequest $leaveRequest): Response
    {
        if (! $user->can('create izin')) {
            return Response::deny('Anda tidak memiliki akses untuk menghapus izin.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $leaveRequest->tenant_id || $user->tenant_id !== $leaveRequest->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus izin dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
