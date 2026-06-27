<?php

use App\Modules\Auth\Actions\UpdatePasswordAction;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

test('updates password and clears change required flag', function (): void {
    $user = User::factory()->create([
        'password' => bcrypt('old-password'),
        'password_change_required' => true,
    ]);
    $oldRememberToken = $user->remember_token;

    $action = app(UpdatePasswordAction::class);
    $action->handle($user, 'new-secret-password');

    $user->refresh();

    expect(Hash::check('new-secret-password', $user->password))->toBeTrue();
    expect($user->password_change_required)->toBeFalse();
    expect($user->remember_token)->not->toBe($oldRememberToken);
    expect(strlen($user->remember_token))->toBe(60);
});
