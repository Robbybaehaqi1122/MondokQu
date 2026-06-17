<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\KeuanganQu\Models\CoaAccount;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoaAccountPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('manage keuangan');
    }

    public function view(User $user, CoaAccount $coaAccount): bool
    {
        return $user->can('manage keuangan') && $user->tenant_id === $coaAccount->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->can('manage coa');
    }

    public function update(User $user, CoaAccount $coaAccount): bool
    {
        return $user->can('manage coa') && $user->tenant_id === $coaAccount->tenant_id;
    }

    public function delete(User $user, CoaAccount $coaAccount): bool
    {
        return $user->can('manage coa') && $user->tenant_id === $coaAccount->tenant_id;
    }
}
