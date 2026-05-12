<?php

use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

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
    $admin = tenantUser('Admin');

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

    $log = ActivityLog::query()->withoutTenantScope()->latest()->first();

    expect($log)->not->toBeNull();
    expect($log->action)->toBe('user_created');
    expect($log->actor_id)->toBe($admin->id);
});

test('admin can view and filter tenant scoped activity logs', function () {
    $admin = tenantUser('Admin');
    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $otherUser = User::factory()->forTenant($otherTenant)->create();

    ActivityLog::query()->create([
        'tenant_id' => $admin->tenant_id,
        'actor_id' => $admin->id,
        'actor_name' => $admin->name,
        'action' => 'santri_deleted',
        'description' => 'Data santri dihapus dari sistem.',
        'target_name' => 'Santri Tenant Sendiri',
        'ip_address' => '127.0.0.1',
    ]);
    ActivityLog::query()->create([
        'tenant_id' => $otherTenant->id,
        'actor_id' => $otherUser->id,
        'actor_name' => $otherUser->name,
        'action' => 'santri_deleted',
        'description' => 'Data santri tenant lain dihapus.',
        'target_name' => 'Santri Tenant Lain',
        'ip_address' => '127.0.0.1',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.activity-logs', [
            'action' => 'santri_deleted',
            'search' => 'Tenant Sendiri',
        ]));

    $response->assertOk();
    $response->assertSee('Catatan aktivitas internal tenant');
    $response->assertSee('Santri Tenant Sendiri');
    $response->assertDontSee('Santri Tenant Lain');
    $response->assertViewHas('logSummary', fn (array $summary): bool => $summary['total'] === 1 && $summary['filtered'] === 1);
});

test('admin can export filtered tenant scoped activity logs', function () {
    $admin = tenantUser('Admin');
    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $otherUser = User::factory()->forTenant($otherTenant)->create();

    ActivityLog::unguarded(fn () => ActivityLog::query()->create([
        'tenant_id' => $admin->tenant_id,
        'actor_id' => $admin->id,
        'actor_name' => $admin->name,
        'action' => 'santri_deleted',
        'description' => 'Data santri dihapus dari sistem.',
        'target_name' => 'Santri Export Tenant Sendiri',
        'ip_address' => '127.0.0.1',
        'properties' => ['nis' => 'AUDIT-001'],
        'created_at' => '2026-05-11 09:00:00',
        'updated_at' => '2026-05-11 09:00:00',
    ]));
    ActivityLog::unguarded(fn () => ActivityLog::query()->create([
        'tenant_id' => $admin->tenant_id,
        'actor_id' => $admin->id,
        'actor_name' => $admin->name,
        'action' => 'login_success',
        'description' => 'Login berhasil ke aplikasi.',
        'target_name' => 'Login Tenant Sendiri',
        'ip_address' => '127.0.0.1',
        'created_at' => '2026-05-11 10:00:00',
        'updated_at' => '2026-05-11 10:00:00',
    ]));
    ActivityLog::unguarded(fn () => ActivityLog::query()->create([
        'tenant_id' => $otherTenant->id,
        'actor_id' => $otherUser->id,
        'actor_name' => $otherUser->name,
        'action' => 'santri_deleted',
        'description' => 'Data santri tenant lain dihapus.',
        'target_name' => 'Santri Export Tenant Lain',
        'ip_address' => '127.0.0.1',
        'created_at' => '2026-05-11 09:00:00',
        'updated_at' => '2026-05-11 09:00:00',
    ]));

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.activity-logs.export', [
            'action' => 'santri_deleted',
            'search' => 'Export',
            'date_from' => '2026-05-11',
            'date_to' => '2026-05-11',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $csv = $response->streamedContent();

    expect($csv)->toContain('Santri Export Tenant Sendiri');
    expect($csv)->toContain('AUDIT-001');
    expect($csv)->not->toContain('Login Tenant Sendiri');
    expect($csv)->not->toContain('Santri Export Tenant Lain');
});

test('admin can not delete tenant activity logs', function () {
    $admin = tenantUser('Admin');

    ActivityLog::query()->create([
        'tenant_id' => $admin->tenant_id,
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

    $response->assertForbidden();
    expect(ActivityLog::query()->count())->toBe(1);
});

test('superadmin can delete activity logs by role even without explicit manage permission', function () {
    $superadminRole = Role::findByName('Superadmin', 'web');
    $superadminRole->revokePermissionTo('manage activity logs');
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    ActivityLog::query()->create([
        'actor_id' => $superadmin->id,
        'actor_name' => $superadmin->name,
        'action' => 'login_success',
        'description' => 'Login berhasil ke aplikasi.',
        'target_name' => $superadmin->name,
        'ip_address' => '127.0.0.1',
    ]);

    $response = $this
        ->actingAs($superadmin)
        ->delete(route('admin.activity-logs.destroy-all'));

    $response->assertRedirect(route('admin.activity-logs', absolute: false));
    $response->assertSessionHas('success');
    expect(ActivityLog::query()->count())->toBe(0);
});

test('non admin roles can not delete activity logs', function () {
    $user = tenantUser('Pengurus');

    ActivityLog::query()->create([
        'tenant_id' => $user->tenant_id,
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

    $log = ActivityLog::query()->withoutTenantScope()->latest()->first();

    expect($log)->not->toBeNull();
    expect($log->action)->toBe('login_failed');
});

test('failed login for tenant user writes a tenant scoped activity log entry', function () {
    $user = tenantUser('Pengurus');

    $this->from('/login')->post('/login', [
        'login' => $user->username,
        'password' => 'salah-total',
    ]);

    $log = ActivityLog::query()->withoutTenantScope()->latest()->first();

    expect($log)->not->toBeNull();
    expect($log->action)->toBe('login_failed');
    expect($log->tenant_id)->toBe($user->tenant_id);
});
