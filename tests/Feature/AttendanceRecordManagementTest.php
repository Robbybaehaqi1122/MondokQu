<?php

use App\Models\AttendanceActivity;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\LeaveRequest;
use App\Models\Santri;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');
    Role::findOrCreate('Musyrif/Ustadz', 'web');

    Permission::findOrCreate('manage absensi', 'web');
});

test('user with manage absensi permission can input attendance records for a session', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $activity = AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'name' => 'Tahfidz Pagi',
        'active_days' => [AttendanceActivity::DAY_FRIDAY],
    ]);
    $session = AttendanceSession::factory()->forActivity($activity)->create([
        'session_date' => '2026-05-22',
        'status' => AttendanceSession::STATUS_OPEN,
    ]);
    $santriA = Santri::factory()->forTenant($admin->tenant)->create([
        'nis' => 'ABS001',
        'full_name' => 'Ahmad Hadir',
    ]);
    $santriB = Santri::factory()->forTenant($admin->tenant)->create([
        'nis' => 'ABS002',
        'full_name' => 'Badrun Terlambat',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('attendance.sessions.records.edit', $session))
        ->assertOk()
        ->assertSee('Input Absensi Santri')
        ->assertSee('Cari Santri')
        ->assertSee('Tandai Semua Hadir')
        ->assertSee('Reset Filter')
        ->assertSee('Ahmad Hadir')
        ->assertSee('Badrun Terlambat');

    $response = $this
        ->actingAs($admin)
        ->put(route('attendance.sessions.records.update', $session), [
            'records' => [
                [
                    'santri_id' => $santriA->id,
                    'status' => AttendanceRecord::STATUS_PRESENT,
                    'notes' => null,
                ],
                [
                    'santri_id' => $santriB->id,
                    'status' => AttendanceRecord::STATUS_LATE,
                    'notes' => 'Datang setelah pembukaan.',
                ],
            ],
        ]);

    $response->assertRedirect(route('attendance.sessions.records.edit', $session, absolute: false));

    $this->assertDatabaseHas('attendance_records', [
        'tenant_id' => $admin->tenant_id,
        'attendance_session_id' => $session->id,
        'santri_id' => $santriA->id,
        'status' => AttendanceRecord::STATUS_PRESENT,
        'recorded_by' => $admin->id,
    ]);
    $this->assertDatabaseHas('attendance_records', [
        'tenant_id' => $admin->tenant_id,
        'attendance_session_id' => $session->id,
        'santri_id' => $santriB->id,
        'status' => AttendanceRecord::STATUS_LATE,
        'notes' => 'Datang setelah pembukaan.',
        'recorded_by' => $admin->id,
    ]);

    expect(AttendanceRecord::query()->where('attendance_session_id', $session->id)->count())->toBe(2);

    $this
        ->actingAs($admin)
        ->get(route('attendance.sessions.index'))
        ->assertOk()
        ->assertSee('2 santri terisi')
        ->assertSee('1 perlu perhatian');
});

test('attendance input only lists active santri from current tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $activity = AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'active_days' => [AttendanceActivity::DAY_FRIDAY],
    ]);
    $session = AttendanceSession::factory()->forActivity($activity)->create([
        'session_date' => '2026-05-22',
        'status' => AttendanceSession::STATUS_OPEN,
    ]);

    Santri::factory()->forTenant($admin->tenant)->create([
        'full_name' => 'Santri Tenant Sendiri',
        'status' => Santri::STATUS_ACTIVE,
    ]);
    Santri::factory()->forTenant($admin->tenant)->create([
        'full_name' => 'Santri Alumni',
        'status' => Santri::STATUS_ALUMNI,
    ]);
    Santri::factory()->forTenant($otherTenant)->create([
        'full_name' => 'Santri Tenant Lain',
        'status' => Santri::STATUS_ACTIVE,
    ]);

    $this
        ->actingAs($admin)
        ->get(route('attendance.sessions.records.edit', $session))
        ->assertOk()
        ->assertSee('Santri Tenant Sendiri')
        ->assertDontSee('Santri Alumni')
        ->assertDontSee('Santri Tenant Lain');
});

test('attendance input defaults active approved leave requests to permission status', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $activity = AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'active_days' => [AttendanceActivity::DAY_FRIDAY],
    ]);
    $session = AttendanceSession::factory()->forActivity($activity)->create([
        'session_date' => '2026-05-22',
        'status' => AttendanceSession::STATUS_OPEN,
    ]);
    $santriOnLeave = Santri::factory()->forTenant($admin->tenant)->create([
        'full_name' => 'Santri Sedang Izin',
        'status' => Santri::STATUS_ACTIVE,
    ]);
    $santriRecorded = Santri::factory()->forTenant($admin->tenant)->create([
        'full_name' => 'Santri Sudah Diinput',
        'status' => Santri::STATUS_ACTIVE,
    ]);

    foreach ([$santriOnLeave, $santriRecorded] as $santri) {
        LeaveRequest::query()->create([
            'tenant_id' => $admin->tenant_id,
            'santri_id' => $santri->id,
            'start_date' => '2026-05-21',
            'end_date' => '2026-05-23',
            'reason' => 'Izin pulang keluarga.',
            'status' => LeaveRequest::STATUS_APPROVED,
            'approved_by' => $admin->id,
            'approved_at' => '2026-05-20 08:00:00',
            'created_by' => $admin->id,
        ]);
    }

    AttendanceRecord::factory()
        ->forSessionAndSantri($session, $santriRecorded)
        ->create([
            'status' => AttendanceRecord::STATUS_LATE,
            'notes' => 'Sudah dinilai manual.',
        ]);

    $this
        ->actingAs($admin)
        ->get(route('attendance.sessions.records.edit', $session))
        ->assertOk()
        ->assertSee('Izin aktif')
        ->assertSeeInOrder([
            'Santri Sedang Izin',
            '<option value="permission" selected>',
            'Santri Sudah Diinput',
            '<option value="late" selected>',
        ], false);
});

test('attendance records reject santri from another tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $activity = AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'active_days' => [AttendanceActivity::DAY_FRIDAY],
    ]);
    $session = AttendanceSession::factory()->forActivity($activity)->create([
        'session_date' => '2026-05-22',
        'status' => AttendanceSession::STATUS_OPEN,
    ]);
    $otherSantri = Santri::factory()->forTenant($otherTenant)->create([
        'status' => Santri::STATUS_ACTIVE,
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('attendance.sessions.records.edit', $session))
        ->put(route('attendance.sessions.records.update', $session), [
            'records' => [
                [
                    'santri_id' => $otherSantri->id,
                    'status' => AttendanceRecord::STATUS_PRESENT,
                ],
            ],
        ]);

    $response->assertRedirect(route('attendance.sessions.records.edit', $session, absolute: false));
    $response->assertSessionHasErrors('records.0.santri_id', null, 'attendanceRecords');
    expect(AttendanceRecord::query()->count())->toBe(0);
});

test('completed attendance session can not be edited through attendance input', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $activity = AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'active_days' => [AttendanceActivity::DAY_FRIDAY],
    ]);
    $session = AttendanceSession::factory()->forActivity($activity)->create([
        'session_date' => '2026-05-22',
        'status' => AttendanceSession::STATUS_COMPLETED,
    ]);
    $santri = Santri::factory()->forTenant($admin->tenant)->create([
        'status' => Santri::STATUS_ACTIVE,
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('attendance.sessions.records.edit', $session))
        ->put(route('attendance.sessions.records.update', $session), [
            'records' => [
                [
                    'santri_id' => $santri->id,
                    'status' => AttendanceRecord::STATUS_PRESENT,
                ],
            ],
        ]);

    $response->assertRedirect(route('attendance.sessions.records.edit', $session, absolute: false));
    $response->assertSessionHasErrors('records', null, 'attendanceRecords');
    expect(AttendanceRecord::query()->count())->toBe(0);
});

test('user without manage absensi permission can not access attendance input', function () {
    $pengurus = tenantUser('Pengurus');
    $activity = AttendanceActivity::factory()->forTenant($pengurus->tenant)->create([
        'active_days' => [AttendanceActivity::DAY_FRIDAY],
    ]);
    $session = AttendanceSession::factory()->forActivity($activity)->create([
        'session_date' => '2026-05-22',
    ]);

    $this
        ->actingAs($pengurus)
        ->get(route('attendance.sessions.records.edit', $session))
        ->assertForbidden();
});
