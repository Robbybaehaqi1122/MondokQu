<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view the user management list.
     */
    public function viewAny(User $actor): Response
    {
        if ($actor->isSuperAdmin()) {
            return Response::allow();
        }

        return $actor->can('view users')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses ke manajemen user.');
    }

    /**
     * Determine whether the user can view a specific user.
     */
    public function view(User $actor, User $target): Response
    {
        if ($actor->isSuperAdmin()) {
            return Response::allow();
        }

        return $actor->can('view user details')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses ke detail user.');
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $actor): Response
    {
        if ($actor->isSuperAdmin()) {
            return Response::allow();
        }

        return $actor->can('create users')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk membuat user.');
    }

    /**
     * Determine whether the user can create a user with the selected role.
     */
    public function createWithRole(User $actor, string $roleName): Response
    {
        if (! $actor->can('create users')) {
            return Response::deny('Anda tidak memiliki akses untuk membuat user.');
        }

        if ($actor->isSuperAdmin()) {
            return Response::allow();
        }

        return in_array($roleName, ['Superadmin', 'Admin'], true)
            ? Response::deny('Hanya Superadmin yang dapat membuat user dengan role Admin atau Superadmin.')
            : Response::allow();
    }

    /**
     * Determine whether the user can update a user profile.
     */
    public function update(User $actor, User $target): Response
    {
        if ($actor->isSuperAdmin()) {
            return Response::allow();
        }

        if (! $actor->can('update users')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah user.');
        }

        return $this->canManageTarget($actor, $target, 'Akun Superadmin hanya dapat diubah oleh Superadmin.');
    }

    /**
     * Determine whether the user can update a user role.
     */
    public function updateRole(User $actor, User $target): Response
    {
        if ($actor->isSuperAdmin()) {
            return Response::allow();
        }

        if (! $actor->can('assign roles')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah role user.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can assign the selected role.
     */
    public function assignRole(User $actor, User $target, string $roleName): Response
    {
        if ($actor->isSuperAdmin()) {
            return Response::allow();
        }

        if (! $actor->can('assign roles')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah role user.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can change a user status.
     */
    public function changeStatus(User $actor, User $target, ?string $status = null): Response
    {
        if ($actor->isSuperAdmin()) {
            if ($actor->id === $target->id && $status !== null && $status !== User::STATUS_ACTIVE) {
                return Response::deny('Akun yang sedang Anda gunakan harus tetap aktif.');
            }

            return Response::allow();
        }

        if (! $actor->can('update user status')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah status user.');
        }

        $authorization = $this->canManageTarget($actor, $target, 'Akun Superadmin hanya dapat diubah oleh Superadmin.');

        if ($authorization->denied()) {
            return $authorization;
        }

        if ($actor->id === $target->id && $status !== null && $status !== User::STATUS_ACTIVE) {
            return Response::deny('Akun yang sedang Anda gunakan harus tetap aktif.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can verify a user email.
     */
    public function verifyEmail(User $actor, User $target): Response
    {
        if ($actor->isSuperAdmin()) {
            return Response::allow();
        }

        if (! $actor->can('verify user emails')) {
            return Response::deny('Anda tidak memiliki akses untuk memverifikasi email user.');
        }

        return $this->canManageTarget($actor, $target, 'Akun Superadmin hanya dapat diubah oleh Superadmin.');
    }

    /**
     * Determine whether the user can resend a verification email.
     */
    public function resendVerification(User $actor, User $target): Response
    {
        if ($actor->isSuperAdmin()) {
            return Response::allow();
        }

        if (! $actor->can('verify user emails')) {
            return Response::deny('Anda tidak memiliki akses untuk mengirim ulang verifikasi email user.');
        }

        return $this->canManageTarget($actor, $target, 'Akun Superadmin hanya dapat diubah oleh Superadmin.');
    }

    /**
     * Determine whether the user can reset a user password.
     */
    public function resetPassword(User $actor, User $target): Response
    {
        if ($actor->isSuperAdmin()) {
            return Response::allow();
        }

        if (! $actor->can('reset user passwords')) {
            return Response::deny('Anda tidak memiliki akses untuk mereset password user.');
        }

        return $this->canManageTarget($actor, $target, 'Password Superadmin hanya dapat direset oleh Superadmin.');
    }

    /**
     * Determine whether the user can delete a user.
     */
    public function delete(User $actor, User $target): Response
    {
        if ($actor->isSuperAdmin()) {
            if ($actor->id === $target->id) {
                return Response::deny('Akun yang sedang Anda gunakan tidak dapat dihapus.');
            }

            return Response::allow();
        }

        if (! $actor->can('delete users')) {
            return Response::deny('Anda tidak memiliki akses untuk menghapus user.');
        }

        if ($actor->id === $target->id) {
            return Response::deny('Akun yang sedang Anda gunakan tidak dapat dihapus.');
        }

        return Response::allow();
    }

    protected function canManageTarget(User $actor, User $target, string $message): Response
    {
        if (! $actor->hasAnyRole(['Superadmin', 'Admin'])) {
            return Response::deny('Anda tidak memiliki akses ke manajemen user.');
        }

        if ($target->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return Response::deny($message);
        }

        return Response::allow();
    }
}
