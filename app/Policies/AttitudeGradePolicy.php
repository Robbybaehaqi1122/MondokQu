<?php

namespace App\Policies;

use App\Models\AttitudeGrade;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttitudeGradePolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return Response::allow();
    }

    public function view(User $user, AttitudeGrade $attitudeGrade): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $attitudeGrade->tenant_id || $user->tenant_id !== $attitudeGrade->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses nilai sikap dari tenant pondok lain.');
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

    public function update(User $user, AttitudeGrade $attitudeGrade): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $attitudeGrade->tenant_id || $user->tenant_id !== $attitudeGrade->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah nilai sikap dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, AttitudeGrade $attitudeGrade): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $attitudeGrade->tenant_id || $user->tenant_id !== $attitudeGrade->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus nilai sikap dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
