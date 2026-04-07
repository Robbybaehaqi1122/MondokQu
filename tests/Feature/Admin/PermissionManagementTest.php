<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $superadmin = Role::findOrCreate('Superadmin', 'web');
    $admin = Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');

    $manageSystemSettings = Permission::findOrCreate('manage system settings', 'web');
    $assignRoles = Permission::findOrCreate('assign roles', 'web');
    $viewUsers = Permission::findOrCreate('view users', 'web');

    $superadmin->syncPermissions([
        $manageSystemSettings,
        $assignRoles,
        $viewUsers,
    ]);

    $admin->syncPermissions([
        $viewUsers,
    ]);
});

test('superadmin can view the permission management page', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $response = $this->actingAs($user)->get(route('admin.permissions'));

    $response->assertOk();
    $response->assertSee('Permission Management');
});

test('superadmin can create a new permission', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $response = $this->actingAs($user)->post(route('admin.permissions.store'), [
        'name' => 'approve tagihan',
    ]);

    $response->assertRedirect(route('admin.permissions', absolute: false));

    expect(Permission::findByName('approve tagihan', 'web'))->not->toBeNull();
});

test('superadmin can update a permission name', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $permission = Permission::findOrCreate('approve pembayaran', 'web');

    $response = $this->actingAs($user)->patch(route('admin.permissions.update', $permission), [
        'name' => 'approve pembayaran lama',
    ]);

    $response->assertRedirect(route('admin.permissions', absolute: false));

    expect(Permission::findByName('approve pembayaran lama', 'web'))->not->toBeNull();
});

test('superadmin can map roles to a permission', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $permission = Permission::findOrCreate('approve izin', 'web');
    $admin = Role::findByName('Admin', 'web');
    $pengurus = Role::findByName('Pengurus', 'web');

    $response = $this->actingAs($user)->patch(route('admin.permissions.update-roles', $permission), [
        'roles' => [$admin->id, $pengurus->id],
    ]);

    $response->assertRedirect(route('admin.permissions', absolute: false));

    expect($permission->fresh()->roles->pluck('name')->all())
        ->toBe(['Admin', 'Pengurus']);
});

test('admin without manage system settings permission can not access permission management', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin');

    $response = $this->actingAs($user)->get(route('admin.permissions'));

    $response->assertForbidden();
});
