<?php

namespace App\Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordAction
{
    /**
     * Update the authenticated user's password.
     */
    public function handle(User $user, string $password): void
    {
        $user->update([
            'password' => Hash::make($password),
            'password_change_required' => false,
        ]);
    }
}
