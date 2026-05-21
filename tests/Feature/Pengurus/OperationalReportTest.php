<?php

use App\Models\LeaveRequest;
use App\Models\Room;
use App\Models\Santri;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Pengurus', 'web');

    Permission::findOrCreate('manage kamar', 'web');
    Permission::findOrCreate('create izin', 'web');
    Permission::findOrCreate('approve izin', 'web');
});

test('user can view room occupancy and leave request reports scoped to tenant', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo(['manage kamar', 'create izin', 'approve izin']);

    $roomA = Room::factory()->forTenant($pengurus->tenant)->create([
        'name' => 'Asrama A',
        'capacity' => 2,
    ]);
    $roomB = Room::factory()->forTenant($pengurus->tenant)->create([
        'name' => 'Asrama B',
        'capacity' => 1,
    ]);

    $santriA = Santri::factory()->forTenant($pengurus->tenant)->create([
        'nis' => 'S001',
        'full_name' => 'Ahmad Izin',
        'status' => Santri::STATUS_ACTIVE,
        'room_id' => $roomA->id,
        'room_name' => $roomA->name,
    ]);
    $santriB = Santri::factory()->forTenant($pengurus->tenant)->create([
        'nis' => 'S002',
        'full_name' => 'Budi Izin',
        'status' => Santri::STATUS_ACTIVE,
        'room_id' => $roomB->id,
        'room_name' => $roomB->name,
    ]);

    LeaveRequest::query()->create([
        'tenant_id' => $pengurus->tenant_id,
        'santri_id' => $santriA->id,
        'start_date' => '2026-05-05',
        'end_date' => '2026-05-06',
        'reason' => 'Keperluan keluarga.',
        'status' => LeaveRequest::STATUS_PENDING,
        'created_by' => $pengurus->id,
    ]);
    LeaveRequest::query()->create([
        'tenant_id' => $pengurus->tenant_id,
        'santri_id' => $santriA->id,
        'start_date' => '2026-05-10',
        'end_date' => '2026-05-11',
        'reason' => 'Berobat.',
        'status' => LeaveRequest::STATUS_APPROVED,
        'created_by' => $pengurus->id,
        'approved_by' => $pengurus->id,
        'approved_at' => '2026-05-09 08:00:00',
    ]);
    LeaveRequest::query()->create([
        'tenant_id' => $pengurus->tenant_id,
        'santri_id' => $santriB->id,
        'start_date' => '2026-05-12',
        'end_date' => '2026-05-13',
        'reason' => 'Keperluan sekolah.',
        'status' => LeaveRequest::STATUS_COMPLETED,
        'created_by' => $pengurus->id,
    ]);
    LeaveRequest::query()->create([
        'tenant_id' => $pengurus->tenant_id,
        'santri_id' => $santriB->id,
        'start_date' => '2026-04-12',
        'end_date' => '2026-04-13',
        'reason' => 'Keperluan keluarga.',
        'status' => LeaveRequest::STATUS_REJECTED,
        'created_by' => $pengurus->id,
    ]);

    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $otherRoom = Room::factory()->forTenant($otherTenant)->create([
        'name' => 'Asrama Tenant Lain',
        'capacity' => 30,
    ]);
    $otherSantri = Santri::factory()->forTenant($otherTenant)->create([
        'full_name' => 'Santri Tenant Lain',
        'room_id' => $otherRoom->id,
        'room_name' => $otherRoom->name,
    ]);
    LeaveRequest::query()->withoutTenantScope()->create([
        'tenant_id' => $otherTenant->id,
        'santri_id' => $otherSantri->id,
        'start_date' => '2026-05-05',
        'end_date' => '2026-05-06',
        'reason' => 'Data tenant lain.',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    $response = $this
        ->actingAs($pengurus)
        ->get(route('pengurus.reports.index', ['month' => '2026-05']));

    $response->assertOk();
    $response->assertSee('Laporan Kamar & Izin', false);
    $response->assertSee('Asrama A');
    $response->assertSee('Ahmad Izin');
    $response->assertDontSee('Asrama Tenant Lain');
    $response->assertDontSee('Santri Tenant Lain');

    $response->assertViewHas('roomSummary', function (array $summary): bool {
        return $summary['total'] === 2
            && $summary['capacity'] === 3
            && $summary['occupied'] === 2
            && $summary['available'] === 1
            && $summary['occupancy_percentage'] === 67;
    });

    $response->assertViewHas('roomReports', function ($roomReports): bool {
        $asramaA = $roomReports->firstWhere('name', 'Asrama A');

        return $asramaA
            && $asramaA['active_santris_count'] === 1
            && $asramaA['remaining_capacity'] === 1
            && $asramaA['occupancy_percentage'] === 50
            && ! $roomReports->contains(fn (array $room): bool => $room['name'] === 'Asrama Tenant Lain');
    });

    $response->assertViewHas('leaveStatusCounts', function ($statusCounts): bool {
        return $statusCounts->firstWhere('status', LeaveRequest::STATUS_PENDING)['count'] === 1
            && $statusCounts->firstWhere('status', LeaveRequest::STATUS_APPROVED)['count'] === 1
            && $statusCounts->firstWhere('status', LeaveRequest::STATUS_REJECTED)['count'] === 0
            && $statusCounts->firstWhere('status', LeaveRequest::STATUS_COMPLETED)['count'] === 1;
    });

    $response->assertViewHas('monthlyLeaveRecaps', function ($monthlyRecaps): bool {
        $april = $monthlyRecaps->firstWhere('month', '2026-04');
        $may = $monthlyRecaps->firstWhere('month', '2026-05');

        return $april['total'] === 1
            && $april['statuses'][LeaveRequest::STATUS_REJECTED] === 1
            && $may['total'] === 3
            && $may['statuses'][LeaveRequest::STATUS_PENDING] === 1
            && $may['statuses'][LeaveRequest::STATUS_APPROVED] === 1
            && $may['statuses'][LeaveRequest::STATUS_COMPLETED] === 1;
    });

    $response->assertViewHas('santriLeaveRecaps', function ($santriRecaps): bool {
        $ahmad = $santriRecaps->firstWhere('santri_name', 'Ahmad Izin');
        $budi = $santriRecaps->firstWhere('santri_name', 'Budi Izin');

        return $ahmad['total'] === 2
            && $ahmad['pending'] === 1
            && $ahmad['approved'] === 1
            && $budi['total'] === 1
            && $budi['completed'] === 1;
    });
});

test('operational report can be filtered by status and santri', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('create izin');
    $santri = Santri::factory()->forTenant($pengurus->tenant)->create([
        'full_name' => 'Santri Filter',
    ]);
    $otherSantri = Santri::factory()->forTenant($pengurus->tenant)->create([
        'full_name' => 'Santri Lain',
    ]);

    LeaveRequest::query()->create([
        'tenant_id' => $pengurus->tenant_id,
        'santri_id' => $santri->id,
        'start_date' => '2026-05-01',
        'end_date' => '2026-05-02',
        'reason' => 'Disetujui.',
        'status' => LeaveRequest::STATUS_APPROVED,
        'created_by' => $pengurus->id,
    ]);
    LeaveRequest::query()->create([
        'tenant_id' => $pengurus->tenant_id,
        'santri_id' => $santri->id,
        'start_date' => '2026-05-03',
        'end_date' => '2026-05-04',
        'reason' => 'Menunggu.',
        'status' => LeaveRequest::STATUS_PENDING,
        'created_by' => $pengurus->id,
    ]);
    LeaveRequest::query()->create([
        'tenant_id' => $pengurus->tenant_id,
        'santri_id' => $otherSantri->id,
        'start_date' => '2026-05-05',
        'end_date' => '2026-05-06',
        'reason' => 'Santri lain.',
        'status' => LeaveRequest::STATUS_APPROVED,
        'created_by' => $pengurus->id,
    ]);

    $response = $this
        ->actingAs($pengurus)
        ->get(route('pengurus.reports.index', [
            'month' => '2026-05',
            'status' => LeaveRequest::STATUS_APPROVED,
            'santri' => $santri->id,
        ]));

    $response->assertOk();
    $response->assertViewHas('leaveSummary', fn (array $summary): bool => $summary['total'] === 1);
    $response->assertViewHas('santriLeaveRecaps', function ($santriRecaps): bool {
        return $santriRecaps->count() === 1
            && $santriRecaps->first()['santri_name'] === 'Santri Filter'
            && $santriRecaps->first()['approved'] === 1;
    });
});

test('user without room or leave permissions can not access operational reports', function () {
    $pengurus = tenantUser('Pengurus');

    $this
        ->actingAs($pengurus)
        ->get(route('pengurus.reports.index'))
        ->assertForbidden();
});
