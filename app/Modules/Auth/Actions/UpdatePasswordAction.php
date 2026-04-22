<?php

namespace App\Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UpdatePasswordAction
{
    /**
     * Update the authenticated user's password.
     */
    public function handle(User $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
            'password_change_required' => false,
            'remember_token' => Str::random(60),
        ])->save();
    }
}
