<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $superadmin = Role::findOrCreate('Superadmin', 'web');
    Role::findOrCreate('Admin', 'web');

    $assignRoles = Permission::findOrCreate('assign roles', 'web');
    $manageSystemSettings = Permission::findOrCreate('manage system settings', 'web');
    $viewUsers = Permission::findOrCreate('view users', 'web');
    $createUsers = Permission::findOrCreate('create users', 'web');

    $superadmin->syncPermissions([
        $assignRoles,
        $manageSystemSettings,
        $viewUsers,
        $createUsers,
    ]);
});

test('superadmin can view the role management page', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $response = $this->actingAs($user)->get(route('admin.roles'));

    $response->assertOk();
    $response->assertSee('Manajemen Role');
});

test('superadmin can create a new role', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $response = $this->actingAs($user)->post(route('admin.roles.store'), [
        'name' => 'Operator Pendaftaran',
    ]);

    $response->assertRedirect(route('admin.roles', absolute: false));

    expect(Role::findByName('Operator Pendaftaran', 'web'))->not->toBeNull();
});

test('superadmin can update permissions assigned to a role', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $role = Role::findOrCreate('Operator', 'web');
    $viewUsers = Permission::findByName('view users', 'web');
    $createUsers = Permission::findByName('create users', 'web');

    $response = $this->actingAs($user)->patch(route('admin.roles.update-permissions', $role), [
        'permissions' => [$viewUsers->id, $createUsers->id],
    ]);

    $response->assertRedirect(route('admin.roles', absolute: false));

    expect($role->fresh()->permissions->pluck('name')->all())
        ->toBe(['view users', 'create users']);
});

test('admin without assign roles permission can not access role management', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin');

    $response = $this->actingAs($user)->get(route('admin.roles'));

    $response->assertForbidden();
});
