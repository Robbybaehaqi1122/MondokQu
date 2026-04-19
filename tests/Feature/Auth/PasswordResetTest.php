<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->post('/forgot-password', ['email' => $user->email]);

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Jika email Anda terdaftar di sistem, kami sudah mengirimkan link reset password.');
    Notification::assertSentTo($user, ResetPassword::class);
});

test('forgot password request does not reveal whether an email exists', function () {
    Notification::fake();

    $response = $this->from('/forgot-password')->post('/forgot-password', [
        'email' => 'tidak-terdaftar@example.com',
    ]);

    $response
        ->assertRedirect('/forgot-password')
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Jika email Anda terdaftar di sistem, kami sudah mengirimkan link reset password.');

    Notification::assertNothingSent();
});

test('forgot password requests are throttled', function () {
    Notification::fake();

    foreach (range(1, 3) as $attempt) {
        $this->post('/forgot-password', [
            'email' => 'pengujian@example.com',
        ])->assertSessionHasNoErrors();
    }

    $response = $this->post('/forgot-password', [
        'email' => 'pengujian@example.com',
    ]);

    $response->assertStatus(429);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create([
        'password_change_required' => true,
    ]);

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        expect($user->fresh()->password_change_required)->toBeFalse();

        return true;
    });
});
