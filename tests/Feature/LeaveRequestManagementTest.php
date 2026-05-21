<?php

use App\Models\LeaveRequest;
use App\Models\Santri;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');

    Permission::findOrCreate('create izin', 'web');
    Permission::findOrCreate('approve izin', 'web');
});

test('user with create izin permission can view and create leave requests', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('create izin');
    $santri = Santri::factory()->forTenant($pengurus->tenant)->create([
        'full_name' => 'Santri Izin',
        'status' => Santri::STATUS_ACTIVE,
    ]);

    $this
        ->actingAs($pengurus)
        ->get(route('pengurus.izin.index'))
        ->assertOk()
        ->assertSee('Pengajuan Izin Santri')
        ->assertSee('Santri Izin');

    $this
        ->actingAs($pengurus)
        ->post(route('pengurus.izin.store'), [
            'santri_id' => $santri->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'reason' => 'Pulang ke rumah karena acara keluarga.',
        ])
        ->assertRedirect(route('pengurus.izin.index', absolute: false));

    expect(LeaveRequest::query()->where('santri_id', $santri->id)->exists())->toBeTrue();
});

test('leave request approval stores approval metadata and can be completed', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('approve izin');
    $santri = Santri::factory()->forTenant($pengurus->tenant)->create([
        'status' => Santri::STATUS_ACTIVE,
    ]);
    $leaveRequest = LeaveRequest::query()->create([
        'tenant_id' => $pengurus->tenant_id,
        'santri_id' => $santri->id,
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'reason' => 'Keperluan keluarga.',
        'status' => LeaveRequest::STATUS_PENDING,
        'created_by' => $pengurus->id,
    ]);

    $this
        ->actingAs($pengurus)
        ->post(route('pengurus.izin.approve', $leaveRequest))
        ->assertRedirect(route('pengurus.izin.index', absolute: false));

    $leaveRequest->refresh();

    expect($leaveRequest->status)->toBe(LeaveRequest::STATUS_APPROVED);
    expect($leaveRequest->approved_by)->toBe($pengurus->id);
    expect($leaveRequest->approved_at)->not->toBeNull();

    $this
        ->actingAs($pengurus)
        ->post(route('pengurus.izin.complete', $leaveRequest))
        ->assertRedirect(route('pengurus.izin.index', absolute: false));

    expect($leaveRequest->fresh()->status)->toBe(LeaveRequest::STATUS_COMPLETED);
});

test('leave request creation rejects santri from another tenant', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('create izin');
    $otherPengurus = tenantUser('Pengurus');
    $otherSantri = Santri::factory()->forTenant($otherPengurus->tenant)->create([
        'status' => Santri::STATUS_ACTIVE,
    ]);

    $response = $this
        ->actingAs($pengurus)
        ->from(route('pengurus.izin.index'))
        ->post(route('pengurus.izin.store'), [
            'santri_id' => $otherSantri->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'reason' => 'Keperluan keluarga.',
        ]);

    $response->assertRedirect(route('pengurus.izin.index', absolute: false));
    $response->assertSessionHasErrors('santri_id', null, 'createLeaveRequest');
    expect(LeaveRequest::query()->withoutTenantScope()->count())->toBe(0);
});

test('user without izin permission can not access leave requests', function () {
    $pengurus = tenantUser('Pengurus');

    $this
        ->actingAs($pengurus)
        ->get(route('pengurus.izin.index'))
        ->assertForbidden();
});
