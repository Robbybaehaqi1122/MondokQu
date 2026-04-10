<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $avatar = function_exists('imagecreatetruecolor')
        ? UploadedFile::fake()->image('profile-avatar.png', 300, 300)->size(512)
        : null;

    $payload = [
        'name' => 'Test User',
        'username' => 'test-user',
        'email' => 'test@example.com',
        'phone_number' => '081234567890',
    ];

    if ($avatar) {
        $payload['avatar'] = $avatar;
    }

    $response = $this
        ->actingAs($user)
        ->patch('/profile', $payload);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test-user', $user->username);
    $this->assertSame('test@example.com', $user->email);
    $this->assertSame('081234567890', $user->phone_number);
    if ($avatar) {
        expect($user->avatar_path)->toStartWith('avatars/');
        Storage::disk('public')->assertExists($user->avatar_path);
    } else {
        expect($user->avatar_path)->toBeNull();
    }
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'username' => $user->username,
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
