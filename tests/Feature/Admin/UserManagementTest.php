<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');
    Role::findOrCreate('Bendahara', 'web');
});

test('admin can view the user management page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.users'));

    $response->assertOk();
    $response->assertSee('Manajemen User');
});

test('admin can create a user and assign a role from the panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'User Baru',
            'username' => 'userbaru',
            'email' => 'userbaru@example.com',
            'role' => 'Pengurus',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response->assertRedirect(route('admin.users', absolute: false));

    $user = User::query()->where('email', 'userbaru@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->username)->toBe('userbaru');
    expect($user->hasRole('Pengurus'))->toBeTrue();
});

test('admin can update a user role from the panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $user = User::factory()->create();
    $user->assignRole('Pengurus');

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update-role', $user), [
            'role' => 'Bendahara',
        ]);

    $response->assertRedirect(route('admin.users', absolute: false));

    expect($user->fresh()->hasRole('Bendahara'))->toBeTrue();
});

test('admin can reset a user password from the panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update-password', $user), [
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ]);

    $response->assertRedirect(route('admin.users', absolute: false));
    expect(Hash::check('password-baru', $user->fresh()->password))->toBeTrue();
});

test('admin can delete another user from the panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $user = User::factory()->create();
    $user->assignRole('Pengurus');

    $response = $this
        ->actingAs($admin)
        ->delete(route('admin.users.destroy', $user));

    $response->assertRedirect(route('admin.users', absolute: false));

    expect($user->fresh())->toBeNull();
});

test('admin can not delete their own account from the panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->delete(route('admin.users.destroy', $admin));

    $response->assertRedirect(route('admin.users', absolute: false));

    expect($admin->fresh())->not->toBeNull();
});

test('non admin users can not access the user management page', function () {
    $user = User::factory()->create();
    $user->assignRole('Pengurus');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.users'));

    $response->assertForbidden();
});
