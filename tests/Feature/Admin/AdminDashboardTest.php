<?php

use App\Models\ActivityLog;
use App\Models\Santri;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Superadmin', 'web');
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');
    Role::findOrCreate('Bendahara', 'web');
});

test('admin dashboard shows monitoring statistics', function () {
    $admin = tenantUser('Admin', [
        'status' => User::STATUS_ACTIVE,
        'last_login_at' => now(),
    ]);
    $tenant = $admin->tenant;

    $superadmin = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
        'last_login_at' => now(),
        'created_at' => now()->subDays(2),
    ]);
    $superadmin->assignRole('Superadmin');

    $inactiveUser = User::factory()->forTenant($tenant)->create([
        'status' => User::STATUS_INACTIVE,
        'last_login_at' => null,
        'created_at' => now(),
    ]);
    $inactiveUser->assignRole('Pengurus');

    $suspendedUser = User::factory()->forTenant($tenant)->create([
        'status' => User::STATUS_SUSPENDED,
        'last_login_at' => null,
        'created_at' => now()->subDays(10),
    ]);
    $suspendedUser->assignRole('Bendahara');

    Santri::factory()->forTenant($tenant)->create([
        'status' => Santri::STATUS_ACTIVE,
        'full_name' => 'Santri Aktif A',
        'room_name' => 'Asrama A1',
        'entry_year' => 2024,
        'created_at' => now()->startOfMonth()->addDay(),
    ]);

    Santri::factory()->forTenant($tenant)->create([
        'status' => Santri::STATUS_ALUMNI,
        'full_name' => 'Santri Alumni B',
        'room_name' => 'Asrama A1',
        'entry_year' => 2023,
        'created_at' => now()->subMonths(2),
    ]);

    Santri::factory()->forTenant($tenant)->create([
        'status' => Santri::STATUS_EXITED,
        'full_name' => 'Santri Keluar C',
        'room_name' => 'Asrama B2',
        'entry_year' => 2024,
        'created_at' => now()->subMonths(1),
    ]);

    ActivityLog::query()->create([
        'tenant_id' => $tenant->id,
        'actor_id' => $admin->id,
        'actor_name' => $admin->name,
        'action' => 'login_success',
        'description' => 'Login berhasil ke aplikasi.',
        'target_type' => User::class,
        'target_id' => $admin->id,
        'target_name' => $admin->name,
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    ActivityLog::query()->create([
        'actor_id' => $superadmin->id,
        'actor_name' => $superadmin->name,
        'action' => 'login_success',
        'description' => 'Login berhasil ke aplikasi.',
        'target_type' => User::class,
        'target_id' => $superadmin->id,
        'target_name' => $superadmin->name,
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Dashboard Operasional Pondok');
    $response->assertSee('Total User');
    $response->assertSee('Total Santri');
    $response->assertSee('User per Role');
    $response->assertSee('Login Hari Ini');
    $response->assertSee('User Baru Minggu Ini');
    $response->assertSee('Santri Baru Bulan Ini');
    $response->assertSee('Sebaran Santri per Kamar');
    $response->assertSee('Sebaran Santri per Angkatan');
    $response->assertSee('Login Terakhir User');
    $response->assertSee('Santri Terbaru');
    $response->assertSee($tenant->name);
    $response->assertSee('Subscription Aktif');
    $response->assertSee('Asrama A1');
    $response->assertSee('Angkatan 2024');
    $response->assertSee('Santri Aktif A');

    expect($response->viewData('stats')['total_users'])->toBe(3);
    expect($response->viewData('stats')['active_users'])->toBe(1);
    expect($response->viewData('stats')['inactive_users'])->toBe(1);
    expect($response->viewData('stats')['suspended_users'])->toBe(1);
    expect($response->viewData('stats')['never_logged_in_users'])->toBe(2);
    expect($response->viewData('loginCountToday'))->toBe(1);
    expect($response->viewData('newUsersThisWeek'))->toBe(2);
    expect($response->viewData('newSantriThisMonth'))->toBe(1);
    expect($response->viewData('santriStats')['total_santri'])->toBe(3);
    expect($response->viewData('santriStats')['active_santri'])->toBe(1);
    expect($response->viewData('santriStats')['alumni_santri'])->toBe(1);
    expect($response->viewData('santriStats')['exited_santri'])->toBe(1);
    expect($response->viewData('tenantSummary')['title'])->toBe($tenant->name);
    expect($response->viewData('tenantSummary')['badge'])->toBe('Subscription Aktif');
    expect($response->viewData('roomDistribution')->first()['room_name'])->toBe('Asrama A1');
    expect($response->viewData('roomDistribution')->first()['santri_count'])->toBe(2);
    expect($response->viewData('entryYearDistribution')->first()['entry_year'])->toBe('2024');
    expect($response->viewData('entryYearDistribution')->first()['santri_count'])->toBe(2);
    expect($response->viewData('recentUsers'))->toHaveCount(3);
    expect($response->viewData('recentSantri'))->toHaveCount(3);
});

test('admin dashboard shows trial warning when tenant trial is near expiry', function () {
    $admin = tenantUser('Admin');
    $tenant = $admin->tenant;

    $tenant->forceFill([
        'subscription_status' => Tenant::SUBSCRIPTION_TRIAL,
        'subscription_plan' => 'trial',
        'trial_ends_at' => now()->addDays(2),
        'subscription_starts_at' => null,
        'subscription_ends_at' => null,
        'grace_ends_at' => null,
    ])->save();

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Masa trial akan segera berakhir');
    $response->assertSee('Segera hubungi admin platform untuk aktivasi subscription');
    expect($response->viewData('tenantLifecycleNotice')['style'])->toBe('warning');
});

test('admin dashboard shows grace period warning for tenant', function () {
    $admin = tenantUser('Admin');
    $tenant = $admin->tenant;

    $tenant->forceFill([
        'subscription_status' => Tenant::SUBSCRIPTION_GRACE,
        'subscription_plan' => 'basic',
        'subscription_starts_at' => now()->subMonth(),
        'subscription_ends_at' => now()->subDay(),
        'grace_ends_at' => now()->addDay(),
    ])->save();

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Tenant sedang dalam grace period');
    $response->assertSee('Konfirmasi pembayaran atau minta perpanjangan ke admin platform');
    expect($response->viewData('tenantLifecycleNotice')['style'])->toBe('danger');
});
