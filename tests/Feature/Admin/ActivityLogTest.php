<?php

use App\Models\ActivityLog;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $superadmin = Role::findOrCreate('Superadmin', 'web');
    $adminRole = Role::findOrCreate('Admin', 'web');
    $pengurusRole = Role::findOrCreate('Pengurus', 'web');

    $viewActivityLogs = Permission::findOrCreate('view activity logs', 'web');
    $manageActivityLogs = Permission::findOrCreate('manage activity logs', 'web');
    $manageSystemSettings = Permission::findOrCreate('manage system settings', 'web');
    $assignRoles = Permission::findOrCreate('assign roles', 'web');
    $viewUsers = Permission::findOrCreate('view users', 'web');
    $createUsers = Permission::findOrCreate('create users', 'web');

    $superadmin->syncPermissions([
        $viewActivityLogs,
        $manageActivityLogs,
        $manageSystemSettings,
        $assignRoles,
        $viewUsers,
        $createUsers,
    ]);

    $adminRole->syncPermissions([
        $viewActivityLogs,
        $manageActivityLogs,
        $viewUsers,
        $createUsers,
    ]);

    $pengurusRole->syncPermissions([
        $viewActivityLogs,
    ]);
});

test('superadmin can view the activity log page', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    ActivityLog::query()->create([
        'actor_id' => $user->id,
        'actor_name' => $user->name,
        'action' => 'user_created',
        'description' => 'Membuat user baru.',
        'target_name' => 'Test User (@testuser)',
        'ip_address' => '127.0.0.1',
    ]);

    $response = $this->actingAs($user)->get(route('admin.activity-logs'));

    $response->assertOk();
    $response->assertSee('Log Activity');
    $response->assertSee('User Created');
});

test('creating a user writes an activity log entry', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    Role::findOrCreate('Bendahara', 'web');

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Audit User',
        'username' => 'audituser',
        'email' => 'audit@example.com',
        'role' => 'Bendahara',
        'status' => User::STATUS_ACTIVE,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $log = ActivityLog::query()->latest()->first();

    expect($log)->not->toBeNull();
    expect($log->action)->toBe('user_created');
    expect($log->actor_id)->toBe($admin->id);
});

test('admin can delete all activity logs', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    ActivityLog::query()->create([
        'actor_id' => $admin->id,
        'actor_name' => $admin->name,
        'action' => 'login_success',
        'description' => 'Login berhasil ke aplikasi.',
        'target_name' => $admin->name,
        'ip_address' => '127.0.0.1',
    ]);

    $response = $this
        ->actingAs($admin)
        ->delete(route('admin.activity-logs.destroy-all'));

    $response->assertRedirect(route('admin.activity-logs', absolute: false));
    $response->assertSessionHas('success');
    expect(ActivityLog::query()->count())->toBe(0);
});

test('non admin roles can not delete activity logs', function () {
    $user = User::factory()->create();
    $user->assignRole('Pengurus');

    ActivityLog::query()->create([
        'actor_id' => $user->id,
        'actor_name' => $user->name,
        'action' => 'login_success',
        'description' => 'Login berhasil ke aplikasi.',
        'target_name' => $user->name,
        'ip_address' => '127.0.0.1',
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.activity-logs.destroy-all'));

    $response->assertForbidden();
    expect(ActivityLog::query()->count())->toBe(1);
});

test('failed login writes an activity log entry', function () {
    $user = User::factory()->create();

    $this->from('/login')->post('/login', [
        'login' => $user->username,
        'password' => 'salah-total',
    ]);

    $log = ActivityLog::query()->latest()->first();

    expect($log)->not->toBeNull();
    expect($log->action)->toBe('login_failed');
});
