<?php

use App\Models\AttendanceActivity;
use App\Models\AttendanceSession;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');
    Role::findOrCreate('Musyrif/Ustadz', 'web');

    Permission::findOrCreate('manage absensi', 'web');
});

test('user with manage absensi permission can manage attendance sessions', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $activity = AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'name' => 'Halaqah Jumat',
        'start_time' => '05:30',
        'end_time' => '06:30',
        'active_days' => [AttendanceActivity::DAY_FRIDAY],
    ]);

    $this
        ->actingAs($admin)
        ->get(route('attendance.sessions.index'))
        ->assertOk()
        ->assertSee('AbsenQu')
        ->assertSee('Sesi Absensi Harian')
        ->assertSee('Halaqah Jumat');

    $response = $this
        ->actingAs($admin)
        ->post(route('attendance.sessions.store'), [
            'attendance_activity_id' => $activity->id,
            'session_date' => '2026-05-22',
            'status' => AttendanceSession::STATUS_DRAFT,
            'notes' => 'Sesi awal untuk halaqah Jumat.',
        ]);

    $response->assertRedirect(route('attendance.sessions.index', absolute: false));

    $session = AttendanceSession::query()->where('attendance_activity_id', $activity->id)->first();

    expect($session)->not->toBeNull();
    expect($session?->tenant_id)->toBe($admin->tenant_id);
    expect($session?->session_date?->toDateString())->toBe('2026-05-22');
    expect($session?->status)->toBe(AttendanceSession::STATUS_DRAFT);
    expect($session?->created_by)->toBe($admin->id);

    $this
        ->actingAs($admin)
        ->patch(route('attendance.sessions.update', $session), [
            'attendance_activity_id' => $activity->id,
            'session_date' => '2026-05-29',
            'status' => AttendanceSession::STATUS_OPEN,
            'notes' => 'Sesi dibuka untuk input absensi.',
            'editing_attendance_session_id' => $session->id,
        ])
        ->assertRedirect(route('attendance.sessions.index', absolute: false));

    $session->refresh();

    expect($session->session_date?->toDateString())->toBe('2026-05-29');
    expect($session->status)->toBe(AttendanceSession::STATUS_OPEN);
    expect($session->notes)->toBe('Sesi dibuka untuk input absensi.');

    $this
        ->actingAs($admin)
        ->delete(route('attendance.sessions.destroy', $session))
        ->assertRedirect(route('attendance.sessions.index', absolute: false));

    expect($session->fresh())->toBeNull();
});

test('attendance sessions are scoped to the current tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $ownActivity = AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'name' => 'Sesi Tenant Sendiri',
        'active_days' => [AttendanceActivity::DAY_FRIDAY],
    ]);
    $otherActivity = AttendanceActivity::factory()->forTenant($otherTenant)->create([
        'name' => 'Sesi Tenant Lain',
        'active_days' => [AttendanceActivity::DAY_FRIDAY],
    ]);

    AttendanceSession::factory()->forActivity($ownActivity)->create([
        'session_date' => '2026-05-22',
    ]);
    $otherSession = AttendanceSession::factory()->forActivity($otherActivity)->create([
        'session_date' => '2026-05-22',
        'notes' => 'Jangan terlihat tenant lain.',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('attendance.sessions.index'))
        ->assertOk()
        ->assertSee('Sesi Tenant Sendiri')
        ->assertDontSee('Sesi Tenant Lain')
        ->assertDontSee('Jangan terlihat tenant lain.');

    $this
        ->actingAs($admin)
        ->patch(route('attendance.sessions.update', $otherSession), [
            'attendance_activity_id' => $otherActivity->id,
            'session_date' => '2026-05-29',
            'status' => AttendanceSession::STATUS_OPEN,
            'editing_attendance_session_id' => $otherSession->id,
        ])
        ->assertNotFound();

    expect($otherSession->fresh()->session_date?->toDateString())->toBe('2026-05-22');
});

test('attendance session can not use activity from another tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $otherActivity = AttendanceActivity::factory()->forTenant($otherTenant)->create([
        'active_days' => [AttendanceActivity::DAY_FRIDAY],
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('attendance.sessions.index'))
        ->post(route('attendance.sessions.store'), [
            'attendance_activity_id' => $otherActivity->id,
            'session_date' => '2026-05-22',
            'status' => AttendanceSession::STATUS_DRAFT,
        ]);

    $response->assertRedirect(route('attendance.sessions.index', absolute: false));
    $response->assertSessionHasErrors('attendance_activity_id', null, 'createAttendanceSession');
    expect(AttendanceSession::query()->count())->toBe(0);
});

test('attendance session date must match activity active day', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $activity = AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'active_days' => [AttendanceActivity::DAY_MONDAY],
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('attendance.sessions.index'))
        ->post(route('attendance.sessions.store'), [
            'attendance_activity_id' => $activity->id,
            'session_date' => '2026-05-22',
            'status' => AttendanceSession::STATUS_DRAFT,
        ]);

    $response->assertRedirect(route('attendance.sessions.index', absolute: false));
    $response->assertSessionHasErrors('session_date', null, 'createAttendanceSession');
    expect(AttendanceSession::query()->count())->toBe(0);
});

test('attendance session is unique per activity and date within a tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $activity = AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'active_days' => [AttendanceActivity::DAY_FRIDAY],
    ]);

    AttendanceSession::factory()->forActivity($activity)->create([
        'session_date' => '2026-05-22',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('attendance.sessions.index'))
        ->post(route('attendance.sessions.store'), [
            'attendance_activity_id' => $activity->id,
            'session_date' => '2026-05-22',
            'status' => AttendanceSession::STATUS_DRAFT,
        ]);

    $response->assertRedirect(route('attendance.sessions.index', absolute: false));
    $response->assertSessionHasErrors('session_date', null, 'createAttendanceSession');
    expect(AttendanceSession::query()->where('attendance_activity_id', $activity->id)->count())->toBe(1);
});

test('user without manage absensi permission can not access attendance sessions', function () {
    $pengurus = tenantUser('Pengurus');

    $this
        ->actingAs($pengurus)
        ->get(route('attendance.sessions.index'))
        ->assertForbidden();
});
