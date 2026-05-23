<?php

use App\Models\AttendanceActivity;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');
    Role::findOrCreate('Musyrif/Ustadz', 'web');

    Permission::findOrCreate('manage absensi', 'web');
    Permission::findOrCreate('view santri', 'web');
});

test('user with manage absensi permission can manage attendance activities', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['manage absensi', 'view santri']);
    $responsibleUser = User::factory()->forTenant($admin->tenant)->create([
        'name' => 'Ustadz Absensi',
        'username' => 'ustadzabsensi',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('attendance.activities.index'))
        ->assertOk()
        ->assertSee('AbsenQu')
        ->assertSee('SantriQu')
        ->assertSee('Master Kegiatan Absensi');

    $response = $this
        ->actingAs($admin)
        ->post(route('attendance.activities.store'), [
            'name' => 'Halaqah Pagi',
            'start_time' => '05:30',
            'end_time' => '06:30',
            'active_days' => [
                AttendanceActivity::DAY_MONDAY,
                AttendanceActivity::DAY_WEDNESDAY,
                AttendanceActivity::DAY_FRIDAY,
            ],
            'responsible_user_id' => $responsibleUser->id,
            'status' => AttendanceActivity::STATUS_ACTIVE,
            'description' => 'Absensi halaqah pagi.',
        ]);

    $response->assertRedirect(route('attendance.activities.index', absolute: false));

    $activity = AttendanceActivity::query()->where('name', 'Halaqah Pagi')->first();

    expect($activity)->not->toBeNull();
    expect($activity?->tenant_id)->toBe($admin->tenant_id);
    expect($activity?->responsible_user_id)->toBe($responsibleUser->id);
    expect($activity?->active_days)->toBe([
        AttendanceActivity::DAY_MONDAY,
        AttendanceActivity::DAY_WEDNESDAY,
        AttendanceActivity::DAY_FRIDAY,
    ]);

    $this
        ->actingAs($admin)
        ->patch(route('attendance.activities.update', $activity), [
            'name' => 'Tahfidz Malam',
            'start_time' => '19:30',
            'end_time' => '20:45',
            'active_days' => [
                AttendanceActivity::DAY_TUESDAY,
                AttendanceActivity::DAY_THURSDAY,
            ],
            'responsible_user_id' => '',
            'status' => AttendanceActivity::STATUS_INACTIVE,
            'description' => 'Dipindah ke jadwal malam.',
            'editing_attendance_activity_id' => $activity->id,
        ])
        ->assertRedirect(route('attendance.activities.index', absolute: false));

    $activity->refresh();

    expect($activity->name)->toBe('Tahfidz Malam');
    expect($activity->status)->toBe(AttendanceActivity::STATUS_INACTIVE);
    expect($activity->responsible_user_id)->toBeNull();
    expect($activity->active_days)->toBe([
        AttendanceActivity::DAY_TUESDAY,
        AttendanceActivity::DAY_THURSDAY,
    ]);

    $this
        ->actingAs($admin)
        ->delete(route('attendance.activities.destroy', $activity))
        ->assertRedirect(route('attendance.activities.index', absolute: false));

    expect($activity->fresh())->toBeNull();
});

test('attendance activities are scoped to the current tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'name' => 'Kegiatan Tenant Sendiri',
    ]);
    $otherActivity = AttendanceActivity::factory()->forTenant($otherTenant)->create([
        'name' => 'Kegiatan Tenant Lain',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('attendance.activities.index'))
        ->assertOk()
        ->assertSee('Kegiatan Tenant Sendiri')
        ->assertDontSee('Kegiatan Tenant Lain');

    $this
        ->actingAs($admin)
        ->patch(route('attendance.activities.update', $otherActivity), [
            'name' => 'Percobaan Ambil Tenant',
            'start_time' => '07:00',
            'end_time' => '08:00',
            'active_days' => [AttendanceActivity::DAY_MONDAY],
            'status' => AttendanceActivity::STATUS_ACTIVE,
            'editing_attendance_activity_id' => $otherActivity->id,
        ])
        ->assertNotFound();

    expect($otherActivity->fresh()->name)->toBe('Kegiatan Tenant Lain');
});

test('attendance activity validation keeps responsible user inside current tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $otherUser = User::factory()->forTenant($otherTenant)->create();

    $response = $this
        ->actingAs($admin)
        ->from(route('attendance.activities.index'))
        ->post(route('attendance.activities.store'), [
            'name' => 'Subuh Berjamaah',
            'start_time' => '04:30',
            'end_time' => '05:00',
            'active_days' => [AttendanceActivity::DAY_MONDAY],
            'responsible_user_id' => $otherUser->id,
            'status' => AttendanceActivity::STATUS_ACTIVE,
        ]);

    $response->assertRedirect(route('attendance.activities.index', absolute: false));
    $response->assertSessionHasErrors('responsible_user_id', null, 'createAttendanceActivity');
    expect(AttendanceActivity::query()->where('name', 'Subuh Berjamaah')->exists())->toBeFalse();
});

test('attendance activity name must be unique per tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage absensi');
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    AttendanceActivity::factory()->forTenant($admin->tenant)->create([
        'name' => 'Halaqah Pagi',
    ]);
    AttendanceActivity::factory()->forTenant($otherTenant)->create([
        'name' => 'Halaqah Pagi',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('attendance.activities.index'))
        ->post(route('attendance.activities.store'), [
            'name' => 'Halaqah Pagi',
            'start_time' => '05:30',
            'end_time' => '06:30',
            'active_days' => [AttendanceActivity::DAY_MONDAY],
            'status' => AttendanceActivity::STATUS_ACTIVE,
        ]);

    $response->assertRedirect(route('attendance.activities.index', absolute: false));
    $response->assertSessionHasErrors('name', null, 'createAttendanceActivity');
    expect(AttendanceActivity::query()->where('name', 'Halaqah Pagi')->count())->toBe(1);
});

test('user without manage absensi permission can not access attendance activities', function () {
    $pengurus = tenantUser('Pengurus');

    $this
        ->actingAs($pengurus)
        ->get(route('attendance.activities.index'))
        ->assertForbidden();
});
