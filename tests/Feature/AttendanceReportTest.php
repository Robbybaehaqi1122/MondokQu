<?php

use App\Models\AttendanceActivity;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Room;
use App\Models\Santri;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');

    Permission::findOrCreate('manage absensi', 'web');
});

test('user with manage absensi permission can view attendance reports with filters', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');

    $roomA = Room::factory()->forTenant($admin->tenant)->create(['name' => 'Kamar Umar']);
    $roomB = Room::factory()->forTenant($admin->tenant)->create(['name' => 'Kamar Abu Bakar']);
    $activity = AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'name' => 'Halaqah Subuh',
        'active_days' => [AttendanceActivity::DAY_FRIDAY],
    ]);
    $session = AttendanceSession::factory()->forActivity($activity)->create([
        'session_date' => '2026-05-22',
        'status' => AttendanceSession::STATUS_OPEN,
    ]);
    $santriAbsent = Santri::factory()->forTenant($admin->tenant)->create([
        'full_name' => 'Santri Sering Alpa',
        'room_id' => $roomA->id,
    ]);
    $santriLate = Santri::factory()->forTenant($admin->tenant)->create([
        'full_name' => 'Santri Telat Datang',
        'room_id' => $roomB->id,
    ]);
    $santriPresent = Santri::factory()->forTenant($admin->tenant)->create([
        'full_name' => 'Santri Rajin Hadir',
        'room_id' => $roomA->id,
    ]);

    AttendanceRecord::factory()->forSessionAndSantri($session, $santriAbsent)->create([
        'status' => AttendanceRecord::STATUS_ABSENT,
        'notes' => 'Catatan alpa unik laporan.',
    ]);
    AttendanceRecord::factory()->forSessionAndSantri($session, $santriLate)->create([
        'status' => AttendanceRecord::STATUS_LATE,
        'notes' => 'Catatan telat unik laporan.',
    ]);
    AttendanceRecord::factory()->forSessionAndSantri($session, $santriPresent)->create([
        'status' => AttendanceRecord::STATUS_PRESENT,
        'notes' => 'Catatan hadir unik laporan.',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('attendance.reports.index', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]))
        ->assertOk()
        ->assertSee('Laporan Absensi')
        ->assertSee('Halaqah Subuh')
        ->assertSee('Santri Perlu Perhatian')
        ->assertSee('Santri Sering Alpa')
        ->assertSee('Catatan alpa unik laporan.')
        ->assertSee('Catatan telat unik laporan.')
        ->assertSee('Catatan hadir unik laporan.');

    $this
        ->actingAs($admin)
        ->get(route('attendance.reports.index', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
            'status' => AttendanceRecord::STATUS_LATE,
        ]))
        ->assertOk()
        ->assertSee('Catatan telat unik laporan.')
        ->assertDontSee('Catatan alpa unik laporan.')
        ->assertDontSee('Catatan hadir unik laporan.');

    $this
        ->actingAs($admin)
        ->get(route('attendance.reports.index', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
            'room' => $roomA->id,
        ]))
        ->assertOk()
        ->assertSee('Catatan alpa unik laporan.')
        ->assertSee('Catatan hadir unik laporan.')
        ->assertDontSee('Catatan telat unik laporan.');
});

test('attendance reports are scoped to the current tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $ownActivity = AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'name' => 'Absensi Tenant Sendiri',
        'active_days' => [AttendanceActivity::DAY_FRIDAY],
    ]);
    $otherActivity = AttendanceActivity::factory()->forTenant($otherTenant)->create([
        'name' => 'Absensi Tenant Lain',
        'active_days' => [AttendanceActivity::DAY_FRIDAY],
    ]);
    $ownSession = AttendanceSession::factory()->forActivity($ownActivity)->create([
        'session_date' => '2026-05-22',
    ]);
    $otherSession = AttendanceSession::factory()->forActivity($otherActivity)->create([
        'session_date' => '2026-05-22',
    ]);
    $ownSantri = Santri::factory()->forTenant($admin->tenant)->create();
    $otherSantri = Santri::factory()->forTenant($otherTenant)->create();

    AttendanceRecord::factory()->forSessionAndSantri($ownSession, $ownSantri)->create([
        'notes' => 'Catatan tenant sendiri terlihat.',
    ]);
    AttendanceRecord::factory()->forSessionAndSantri($otherSession, $otherSantri)->create([
        'notes' => 'Catatan tenant lain tersembunyi.',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('attendance.reports.index', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]))
        ->assertOk()
        ->assertSee('Catatan tenant sendiri terlihat.')
        ->assertDontSee('Catatan tenant lain tersembunyi.');
});

test('user without manage absensi permission can not access attendance reports', function () {
    $pengurus = tenantUser('Pengurus');

    $this
        ->actingAs($pengurus)
        ->get(route('attendance.reports.index'))
        ->assertForbidden();
});
