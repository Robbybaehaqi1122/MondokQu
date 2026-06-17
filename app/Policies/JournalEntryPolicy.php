<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\KeuanganQu\Models\JournalEntry;
use Illuminate\Auth\Access\HandlesAuthorization;

class JournalEntryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('manage keuangan');
    }

    public function view(User $user, JournalEntry $journalEntry): bool
    {
        return $user->can('manage keuangan') && $user->tenant_id === $journalEntry->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->can('create jurnal');
    }

    public function update(User $user, JournalEntry $journalEntry): bool
    {
        return $user->can('create jurnal')
            && $user->tenant_id === $journalEntry->tenant_id
            && $journalEntry->isDraft();
    }

    public function delete(User $user, JournalEntry $journalEntry): bool
    {
        return $user->can('create jurnal')
            && $user->tenant_id === $journalEntry->tenant_id
            && $journalEntry->isDraft();
    }

    public function approve(User $user, JournalEntry $journalEntry): bool
    {
        return $user->can('approve jurnal')
            && $user->tenant_id === $journalEntry->tenant_id
            && $journalEntry->isDraft();
    }
}
