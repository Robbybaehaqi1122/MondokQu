<?php

namespace App\Policies;

use App\Models\GradeLevel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GradeLevelPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return Response::allow();
    }

    public function view(User $user, GradeLevel $gradeLevel): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $gradeLevel->tenant_id || $user->tenant_id !== $gradeLevel->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses tingkat dari tenant pondok lain.');
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

    public function update(User $user, GradeLevel $gradeLevel): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $gradeLevel->tenant_id || $user->tenant_id !== $gradeLevel->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah tingkat dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, GradeLevel $gradeLevel): Response
    {
        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $gradeLevel->tenant_id || $user->tenant_id !== $gradeLevel->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus tingkat dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
