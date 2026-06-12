<?php

use App\Models\AttendanceActivity;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Santri;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');

    Permission::findOrCreate('manage absensi', 'web');
});

test('user with manage absensi permission can view attendance dashboard', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $today = now()->toDateString();
    $activityA = AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'name' => 'Halaqah Dashboard Pagi',
    ]);
    $activityB = AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'name' => 'Muhadharah Dashboard',
    ]);
    $sessionA = AttendanceSession::factory()->forActivity($activityA)->create([
        'session_date' => $today,
        'status' => AttendanceSession::STATUS_OPEN,
    ]);
    AttendanceSession::factory()->forActivity($activityB)->create([
        'session_date' => $today,
        'status' => AttendanceSession::STATUS_DRAFT,
    ]);
    $santriLate = Santri::factory()->forTenant($admin->tenant)->create([
        'full_name' => 'Santri Telat Dashboard',
        'status' => Santri::STATUS_ACTIVE,
    ]);
    Santri::factory()->forTenant($admin->tenant)->create([
        'full_name' => 'Santri Belum Diinput Dashboard',
        'status' => Santri::STATUS_ACTIVE,
    ]);

    AttendanceRecord::factory()->forSessionAndSantri($sessionA, $santriLate)->create([
        'status' => AttendanceRecord::STATUS_LATE,
    ]);

    $this
        ->actingAs($admin)
        ->get(route('attendance.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Absensi')
        ->assertSee('Total Santri')
        ->assertSee('Sudah Absen')
        ->assertSee('Belum Absen')
        ->assertSee('% Kehadiran')
        ->assertSee('Sesi Hari Ini')
        ->assertSee('Santri Belum Absen')
        ->assertSee('Halaqah Dashboard Pagi')
        ->assertSee('Muhadharah Dashboard')
        ->assertSee('Santri Perlu Perhatian')
        ->assertSee('Santri Telat Dashboard')
        ->assertSee('Santri Belum Diinput Dashboard');
});

test('attendance dashboard is scoped to the current tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $ownActivity = AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'name' => 'Dashboard Tenant Sendiri',
    ]);
    $otherActivity = AttendanceActivity::factory()->forTenant($otherTenant)->create([
        'name' => 'Dashboard Tenant Lain Rahasia',
    ]);
    $today = now()->toDateString();

    AttendanceSession::factory()->forActivity($ownActivity)->create([
        'session_date' => $today,
    ]);
    AttendanceSession::factory()->forActivity($otherActivity)->create([
        'session_date' => $today,
    ]);

    $this
        ->actingAs($admin)
        ->get(route('attendance.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Tenant Sendiri')
        ->assertDontSee('Dashboard Tenant Lain Rahasia');
});

test('user without manage absensi permission can not access attendance dashboard', function () {
    $pengurus = tenantUser('Pengurus');

    $this
        ->actingAs($pengurus)
        ->get(route('attendance.dashboard'))
        ->assertForbidden();
});
