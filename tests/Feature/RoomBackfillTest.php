<?php

use App\Actions\Room\BackfillRoomsFromSantriRoomNames;
use App\Models\Room;
use App\Models\Santri;
use App\Models\Tenant;

test('room backfill creates master rooms from legacy santri room names and links room ids', function () {
    $tenant = Tenant::factory()->activeSubscription()->create();
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    $existingRoom = Room::factory()->forTenant($tenant)->create([
        'name' => 'Asrama Existing',
        'capacity' => 30,
    ]);
    $manualRoom = Room::factory()->forTenant($tenant)->create([
        'name' => 'Asrama Manual',
    ]);

    $legacySantriA = Santri::factory()->forTenant($tenant)->create([
        'full_name' => 'Santri Legacy A',
        'room_name' => ' Asrama A ',
        'room_id' => null,
    ]);
    $legacySantriB = Santri::factory()->forTenant($tenant)->create([
        'full_name' => 'Santri Legacy B',
        'room_name' => 'Asrama A',
        'room_id' => null,
    ]);
    $legacySantriExistingRoom = Santri::factory()->forTenant($tenant)->create([
        'full_name' => 'Santri Existing Room',
        'room_name' => 'Asrama Existing',
        'room_id' => null,
    ]);
    $alreadyLinkedSantri = Santri::factory()->forTenant($tenant)->create([
        'full_name' => 'Santri Sudah Berkamar',
        'room_name' => 'Asrama Lama Yang Tidak Dipakai',
        'room_id' => $manualRoom->id,
    ]);
    $blankRoomNameSantri = Santri::factory()->forTenant($tenant)->create([
        'full_name' => 'Santri Kamar Kosong',
        'room_name' => '   ',
        'room_id' => null,
    ]);
    $tenantlessSantri = Santri::factory()->create([
        'full_name' => 'Santri Tanpa Tenant',
        'tenant_id' => null,
        'room_name' => 'Asrama Tanpa Tenant',
        'room_id' => null,
    ]);
    $otherTenantSantri = Santri::factory()->forTenant($otherTenant)->create([
        'full_name' => 'Santri Tenant Lain',
        'room_name' => 'Asrama A',
        'room_id' => null,
    ]);

    $result = app(BackfillRoomsFromSantriRoomNames::class)->handle();

    $createdRoom = Room::query()
        ->withoutTenantScope()
        ->where('tenant_id', $tenant->id)
        ->where('name', 'Asrama A')
        ->first();
    $otherTenantRoom = Room::query()
        ->withoutTenantScope()
        ->where('tenant_id', $otherTenant->id)
        ->where('name', 'Asrama A')
        ->first();

    expect($result)->toBe([
        'rooms_created' => 2,
        'rooms_reused' => 1,
        'santris_linked' => 4,
    ]);
    expect($createdRoom)->not->toBeNull();
    expect($otherTenantRoom)->not->toBeNull();
    expect(Room::query()->withoutTenantScope()->where('name', 'Asrama Lama Yang Tidak Dipakai')->exists())->toBeFalse();
    expect((int) $existingRoom->fresh()->capacity)->toBe(30);

    expect(Santri::query()->withoutTenantScope()->find($legacySantriA->id)?->room_id)->toBe($createdRoom->id);
    expect(Santri::query()->withoutTenantScope()->find($legacySantriB->id)?->room_id)->toBe($createdRoom->id);
    expect(Santri::query()->withoutTenantScope()->find($legacySantriExistingRoom->id)?->room_id)->toBe($existingRoom->id);
    expect(Santri::query()->withoutTenantScope()->find($otherTenantSantri->id)?->room_id)->toBe($otherTenantRoom->id);
    expect(Santri::query()->withoutTenantScope()->find($alreadyLinkedSantri->id)?->room_id)->toBe($manualRoom->id);
    expect(Santri::query()->withoutTenantScope()->find($blankRoomNameSantri->id)?->room_id)->toBeNull();
    expect(Santri::query()->withoutTenantScope()->find($tenantlessSantri->id)?->room_id)->toBeNull();

    expect(app(BackfillRoomsFromSantriRoomNames::class)->handle())->toBe([
        'rooms_created' => 0,
        'rooms_reused' => 0,
        'santris_linked' => 0,
    ]);
});
