<?php

use App\Models\LeaveRequest;
use App\Models\Santri;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('pengurus dashboard displays real santri statistics', function () {
    Role::findOrCreate('Pengurus', 'web');

    $tenant = Tenant::factory()->create();
    $pengurus = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);
    $pengurus->assignRole('Pengurus');

    // Create some santri
    Santri::factory()->count(5)->create([
        'tenant_id' => $tenant->id,
        'status' => Santri::STATUS_ACTIVE,
    ]);
    Santri::factory()->count(2)->create([
        'tenant_id' => $tenant->id,
        'status' => Santri::STATUS_LEAVE,
    ]);

    $response = $this->actingAs($pengurus)->get(route('pengurus.dashboard'));

    $response->assertStatus(200);
    $response->assertViewHas('stats', function ($stats) {
        return $stats['total_santri'] === 7 &&
               $stats['active_santri'] === 5 &&
               $stats['leave_santri'] === 2;
    });
});

test('pengurus dashboard displays scoped leave request summary', function () {
    Role::findOrCreate('Pengurus', 'web');

    $tenant = Tenant::factory()->create();
    $pengurus = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);
    $pengurus->assignRole('Pengurus');
    $santri = Santri::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => Santri::STATUS_ACTIVE,
    ]);

    LeaveRequest::query()->create([
        'tenant_id' => $tenant->id,
        'santri_id' => $santri->id,
        'start_date' => today()->addDay()->toDateString(),
        'end_date' => today()->addDays(2)->toDateString(),
        'reason' => 'Menunggu persetujuan.',
        'status' => LeaveRequest::STATUS_PENDING,
        'created_by' => $pengurus->id,
    ]);
    LeaveRequest::query()->create([
        'tenant_id' => $tenant->id,
        'santri_id' => $santri->id,
        'start_date' => today()->subDay()->toDateString(),
        'end_date' => today()->addDay()->toDateString(),
        'reason' => 'Disetujui dan sedang berjalan.',
        'status' => LeaveRequest::STATUS_APPROVED,
        'approved_by' => $pengurus->id,
        'approved_at' => now(),
        'created_by' => $pengurus->id,
    ]);
    LeaveRequest::query()->create([
        'tenant_id' => $tenant->id,
        'santri_id' => $santri->id,
        'start_date' => today()->addDay()->toDateString(),
        'end_date' => today()->addDays(3)->toDateString(),
        'reason' => 'Disetujui untuk tanggal mendatang.',
        'status' => LeaveRequest::STATUS_APPROVED,
        'approved_by' => $pengurus->id,
        'approved_at' => now()->subDay(),
        'created_by' => $pengurus->id,
    ]);
    LeaveRequest::query()->create([
        'tenant_id' => $tenant->id,
        'santri_id' => $santri->id,
        'start_date' => today()->subDays(3)->toDateString(),
        'end_date' => today()->subDay()->toDateString(),
        'reason' => 'Lewat tanggal kembali.',
        'status' => LeaveRequest::STATUS_APPROVED,
        'approved_by' => $pengurus->id,
        'approved_at' => now()->subDays(2),
        'created_by' => $pengurus->id,
    ]);

    $otherTenant = Tenant::factory()->create();
    $otherSantri = Santri::factory()->create([
        'tenant_id' => $otherTenant->id,
        'status' => Santri::STATUS_ACTIVE,
    ]);
    LeaveRequest::query()->withoutTenantScope()->create([
        'tenant_id' => $otherTenant->id,
        'santri_id' => $otherSantri->id,
        'start_date' => today()->toDateString(),
        'end_date' => today()->addDay()->toDateString(),
        'reason' => 'Izin tenant lain.',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($pengurus)->get(route('pengurus.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Izin Menunggu Approval');
    $response->assertSee('Izin Disetujui Hari Ini');
    $response->assertSee('Santri Sedang Izin');
    $response->assertSee('Izin Lewat Tanggal Kembali');
    $response->assertViewHas('leaveStats', function ($leaveStats) {
        return $leaveStats['pending_approval'] === 1 &&
               $leaveStats['approved_today'] === 1 &&
               $leaveStats['currently_on_leave'] === 1 &&
               $leaveStats['overdue_return'] === 1;
    });
});
