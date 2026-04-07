<?php

use App\Models\ActivityLog;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $superadmin = Role::findOrCreate('Superadmin', 'web');
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');

    $viewActivityLogs = Permission::findOrCreate('view activity logs', 'web');
    $manageSystemSettings = Permission::findOrCreate('manage system settings', 'web');
    $assignRoles = Permission::findOrCreate('assign roles', 'web');

    $superadmin->syncPermissions([
        $viewActivityLogs,
        $manageSystemSettings,
        $assignRoles,
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
