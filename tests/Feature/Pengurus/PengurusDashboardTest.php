<?php

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
