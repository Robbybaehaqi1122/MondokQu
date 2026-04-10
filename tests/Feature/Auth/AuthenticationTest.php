<?php

use App\Models\ActivityLog;
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
    expect($user->fresh()->last_login_at)->not->toBeNull();
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

test('superadmin users are redirected to the admin dashboard after login', function () {
    Role::findOrCreate('Superadmin', 'web');
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

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
    $log = ActivityLog::query()->latest()->first();

    expect($log)->not->toBeNull();
    expect($log->action)->toBe('login_failed');
    expect($log->properties['reason'])->toBe('wrong_password');
    expect($log->user_agent)->not->toBeNull();
});

test('inactive users can not authenticate', function () {
    $user = User::factory()->create([
        'status' => User::STATUS_INACTIVE,
    ]);

    $response = $this->from('/login')->post('/login', [
        'login' => $user->username,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('login');
    expect(ActivityLog::query()->latest()->first()->properties['reason'])->toBe('account_inactive');
});

test('suspended users can not authenticate', function () {
    $user = User::factory()->create([
        'status' => User::STATUS_SUSPENDED,
    ]);

    $response = $this->from('/login')->post('/login', [
        'login' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('login');
    expect(ActivityLog::query()->latest()->first()->properties['reason'])->toBe('account_suspended');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('users with password change required are redirected to profile after login', function () {
    $user = User::factory()->create([
        'password_change_required' => true,
    ]);

    $response = $this->post('/login', [
        'login' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('profile.edit', absolute: false));
});
