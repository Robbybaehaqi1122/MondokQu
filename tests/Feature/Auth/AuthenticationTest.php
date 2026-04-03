<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using username on the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'login' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard.home', absolute: false));
});

test('users can authenticate using email on the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'login' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard.home', absolute: false));
});

test('admin users are redirected to the admin dashboard after login', function () {
    Role::findOrCreate('Admin', 'web');
    $user = User::factory()->create();
    $user->assignRole('Admin');

    $response = $this->post('/login', [
        'login' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'login' => $user->username,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
