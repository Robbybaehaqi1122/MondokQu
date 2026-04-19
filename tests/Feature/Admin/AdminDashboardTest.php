<?php

use App\Models\ActivityLog;
use App\Models\Santri;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Superadmin', 'web');
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');
    Role::findOrCreate('Bendahara', 'web');
});

test('admin dashboard shows monitoring statistics', function () {
    $admin = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
        'last_login_at' => now(),
    ]);
    $admin->assignRole('Admin');

    $superadmin = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
        'last_login_at' => now(),
        'created_at' => now()->subDays(2),
    ]);
    $superadmin->assignRole('Superadmin');

    $inactiveUser = User::factory()->create([
        'status' => User::STATUS_INACTIVE,
        'last_login_at' => null,
        'created_at' => now()->subDays(1),
    ]);
    $inactiveUser->assignRole('Pengurus');

    $suspendedUser = User::factory()->create([
        'status' => User::STATUS_SUSPENDED,
        'last_login_at' => null,
        'created_at' => now()->subDays(10),
    ]);
    $suspendedUser->assignRole('Bendahara');

    Santri::factory()->create([
        'status' => Santri::STATUS_ACTIVE,
        'created_at' => now()->startOfMonth()->addDay(),
    ]);

    Santri::factory()->create([
        'status' => Santri::STATUS_ALUMNI,
        'created_at' => now()->subMonths(2),
    ]);

    Santri::factory()->create([
        'status' => Santri::STATUS_EXITED,
        'created_at' => now()->subMonths(1),
    ]);

    ActivityLog::query()->create([
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
    $response->assertSee('System Monitoring Dashboard');
    $response->assertSee('Total User');
    $response->assertSee('Total Santri');
    $response->assertSee('User per Role');
    $response->assertSee('Login Hari Ini');
    $response->assertSee('User Baru Minggu Ini');
    $response->assertSee('Santri Baru Bulan Ini');

    expect($response->viewData('stats')['total_users'])->toBe(4);
    expect($response->viewData('stats')['active_users'])->toBe(2);
    expect($response->viewData('stats')['inactive_users'])->toBe(1);
    expect($response->viewData('stats')['suspended_users'])->toBe(1);
    expect($response->viewData('stats')['never_logged_in_users'])->toBe(2);
    expect($response->viewData('loginCountToday'))->toBe(2);
    expect($response->viewData('newUsersThisWeek'))->toBe(3);
    expect($response->viewData('newSantriThisMonth'))->toBe(1);
    expect($response->viewData('santriStats')['total_santri'])->toBe(3);
    expect($response->viewData('santriStats')['active_santri'])->toBe(1);
    expect($response->viewData('santriStats')['alumni_santri'])->toBe(1);
    expect($response->viewData('santriStats')['exited_santri'])->toBe(1);
});
