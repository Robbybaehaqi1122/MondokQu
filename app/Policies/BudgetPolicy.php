<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\KeuanganQu\Models\Budget;
use Illuminate\Auth\Access\HandlesAuthorization;

class BudgetPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('manage keuangan');
    }

    public function view(User $user, Budget $budget): bool
    {
        return $user->can('manage anggaran') && $user->tenant_id === $budget->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->can('manage anggaran');
    }

    public function update(User $user, Budget $budget): bool
    {
        return $user->can('manage anggaran') && $user->tenant_id === $budget->tenant_id;
    }

    public function delete(User $user, Budget $budget): bool
    {
        return $user->can('manage anggaran') && $user->tenant_id === $budget->tenant_id;
    }
}
