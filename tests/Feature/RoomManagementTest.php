<?php

use App\Models\Room;
use App\Models\RoomTransfer;
use App\Models\Santri;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');

    Permission::findOrCreate('manage kamar', 'web');
});

test('user with manage kamar permission can view room management page', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('manage kamar');

    Room::factory()->forTenant($pengurus->tenant)->create([
        'name' => 'Asrama A1',
    ]);

    $response = $this
        ->actingAs($pengurus)
        ->get(route('rooms.index'));

    $response->assertOk();
    $response->assertSee('Manajemen Kamar');
    $response->assertSee('Asrama A1');
});

test('rooms are scoped to current tenant', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('manage kamar');
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    Room::factory()->forTenant($pengurus->tenant)->create([
        'name' => 'Kamar Tenant Sendiri',
    ]);
    Room::factory()->forTenant($otherTenant)->create([
        'name' => 'Kamar Tenant Lain',
    ]);

    $response = $this
        ->actingAs($pengurus)
        ->get(route('rooms.index'));

    $response->assertOk();
    $response->assertSee('Kamar Tenant Sendiri');
    $response->assertDontSee('Kamar Tenant Lain');
});

test('user can create update and delete an empty room', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage kamar');

    $this
        ->actingAs($admin)
        ->post(route('rooms.store'), [
            'name' => 'Asrama Baru',
            'capacity' => 20,
            'status' => Room::STATUS_ACTIVE,
            'description' => 'Lantai satu.',
        ])
        ->assertRedirect(route('rooms.index', absolute: false));

    $room = Room::query()->where('name', 'Asrama Baru')->first();

    expect($room)->not->toBeNull();
    expect($room?->tenant_id)->toBe($admin->tenant_id);
    expect((int) $room?->capacity)->toBe(20);

    $this
        ->actingAs($admin)
        ->patch(route('rooms.update', $room), [
            'name' => 'Asrama Baru Updated',
            'capacity' => 25,
            'status' => Room::STATUS_INACTIVE,
            'description' => 'Renovasi ringan.',
            'editing_room_id' => $room->id,
        ])
        ->assertRedirect(route('rooms.index', absolute: false));

    $room->refresh();

    expect($room->name)->toBe('Asrama Baru Updated');
    expect((int) $room->capacity)->toBe(25);
    expect($room->status)->toBe(Room::STATUS_INACTIVE);

    $this
        ->actingAs($admin)
        ->delete(route('rooms.destroy', $room))
        ->assertRedirect(route('rooms.index', absolute: false));

    expect(Room::query()->whereKey($room->id)->exists())->toBeFalse();
});

test('room rename updates linked santri mirror inside the room update flow', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage kamar');
    $room = Room::factory()->forTenant($admin->tenant)->create([
        'name' => 'Asrama Lama',
        'capacity' => 10,
    ]);
    $santri = Santri::factory()->forTenant($admin->tenant)->create([
        'full_name' => 'Santri Rename Kamar',
        'room_id' => $room->id,
        'status' => Santri::STATUS_ACTIVE,
    ]);

    $this
        ->actingAs($admin)
        ->patch(route('rooms.update', $room), [
            'name' => 'Asrama Baru',
            'capacity' => 10,
            'status' => Room::STATUS_ACTIVE,
            'editing_room_id' => $room->id,
        ])
        ->assertRedirect(route('rooms.index', absolute: false));

    $santri->refresh();

    expect($room->fresh()->name)->toBe('Asrama Baru');
    expect($santri->room_id)->toBe($room->id);
    expect($santri->room?->name)->toBe('Asrama Baru');
    expect($santri->load('room')->displayRoomName())->toBe('Asrama Baru');
});

test('room name must be unique per tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage kamar');
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    Room::factory()->forTenant($admin->tenant)->create([
        'name' => 'Asrama Sama',
    ]);
    Room::factory()->forTenant($otherTenant)->create([
        'name' => 'Asrama Sama',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('rooms.index'))
        ->post(route('rooms.store'), [
            'name' => 'Asrama Sama',
            'capacity' => 10,
            'status' => Room::STATUS_ACTIVE,
        ]);

    $response->assertRedirect(route('rooms.index', absolute: false));
    $response->assertSessionHasErrors('name', null, 'createRoom');
    expect(Room::query()->withoutTenantScope()->where('name', 'Asrama Sama')->count())->toBe(2);
});

test('user can assign santri to a room and room capacity is enforced', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('manage kamar');
    $room = Room::factory()->forTenant($pengurus->tenant)->create([
        'name' => 'Asrama Kapasitas',
        'capacity' => 2,
    ]);
    $santriA = Santri::factory()->forTenant($pengurus->tenant)->create([
        'full_name' => 'Santri A',
        'status' => Santri::STATUS_ACTIVE,
    ]);
    $santriB = Santri::factory()->forTenant($pengurus->tenant)->create([
        'full_name' => 'Santri B',
        'status' => Santri::STATUS_ACTIVE,
    ]);
    $santriC = Santri::factory()->forTenant($pengurus->tenant)->create([
        'full_name' => 'Santri C',
        'status' => Santri::STATUS_ACTIVE,
    ]);

    $this
        ->actingAs($pengurus)
        ->post(route('rooms.santris.assign', $room), [
            'santri_ids' => [$santriA->id, $santriB->id],
            'assigning_room_id' => $room->id,
        ])
        ->assertRedirect(route('rooms.index', absolute: false));

    expect($santriA->fresh()->room_id)->toBe($room->id);
    expect($santriA->fresh()->room?->name)->toBe('Asrama Kapasitas');
    expect($santriB->fresh()->room_id)->toBe($room->id);

    $response = $this
        ->actingAs($pengurus)
        ->from(route('rooms.index'))
        ->post(route('rooms.santris.assign', $room), [
            'santri_ids' => [$santriC->id],
            'assigning_room_id' => $room->id,
        ]);

    $response->assertRedirect(route('rooms.index', absolute: false));
    $response->assertSessionHasErrors('santri_ids', null, 'assignRoomSantri');
    expect($santriC->fresh()->room_id)->toBeNull();
});

test('room transfer history is recorded and scoped to current tenant', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('manage kamar');
    $roomA = Room::factory()->forTenant($pengurus->tenant)->create([
        'name' => 'Asrama Lama',
        'capacity' => 5,
    ]);
    $roomB = Room::factory()->forTenant($pengurus->tenant)->create([
        'name' => 'Asrama Baru',
        'capacity' => 5,
    ]);
    $santri = Santri::factory()->forTenant($pengurus->tenant)->create([
        'full_name' => 'Santri Mutasi',
        'status' => Santri::STATUS_ACTIVE,
        'room_id' => null,
    ]);

    $this
        ->actingAs($pengurus)
        ->post(route('rooms.santris.assign', $roomA), [
            'santri_ids' => [$santri->id],
            'assigning_room_id' => $roomA->id,
        ])
        ->assertRedirect(route('rooms.index', absolute: false));

    expect(RoomTransfer::query()->count())->toBe(1);

    $initialTransfer = RoomTransfer::query()->first();
    expect($initialTransfer?->from_room_id)->toBeNull();
    expect($initialTransfer?->from_room_name)->toBeNull();
    expect($initialTransfer?->to_room_id)->toBe($roomA->id);
    expect($initialTransfer?->to_room_name)->toBe('Asrama Lama');
    expect($initialTransfer?->moved_by)->toBe($pengurus->id);

    $this
        ->actingAs($pengurus)
        ->post(route('rooms.santris.assign', $roomA), [
            'santri_ids' => [$santri->id],
            'assigning_room_id' => $roomA->id,
        ])
        ->assertRedirect(route('rooms.index', absolute: false));

    expect(RoomTransfer::query()->count())->toBe(1);

    $this
        ->actingAs($pengurus)
        ->post(route('rooms.santris.assign', $roomB), [
            'santri_ids' => [$santri->id],
            'assigning_room_id' => $roomB->id,
        ])
        ->assertRedirect(route('rooms.index', absolute: false));

    $latestTransfer = RoomTransfer::query()->latest('id')->first();

    expect(RoomTransfer::query()->count())->toBe(2);
    expect($latestTransfer?->from_room_id)->toBe($roomA->id);
    expect($latestTransfer?->from_room_name)->toBe('Asrama Lama');
    expect($latestTransfer?->to_room_id)->toBe($roomB->id);
    expect($latestTransfer?->to_room_name)->toBe('Asrama Baru');

    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $otherRoom = Room::factory()->forTenant($otherTenant)->create([
        'name' => 'Kamar Histori Tenant Lain',
    ]);
    $otherSantri = Santri::factory()->forTenant($otherTenant)->create([
        'full_name' => 'Santri Histori Tenant Lain',
    ]);
    RoomTransfer::query()->withoutTenantScope()->create([
        'tenant_id' => $otherTenant->id,
        'santri_id' => $otherSantri->id,
        'to_room_id' => $otherRoom->id,
        'to_room_name' => $otherRoom->name,
        'moved_at' => now(),
    ]);

    $response = $this
        ->actingAs($pengurus)
        ->get(route('rooms.index'));

    $response->assertOk();
    $response->assertSee('Riwayat Pindah Kamar');
    $response->assertSee('Santri Mutasi');
    $response->assertSee('Asrama Lama');
    $response->assertSee('Asrama Baru');
    $response->assertSee($pengurus->name);
    $response->assertDontSee('Santri Histori Tenant Lain');
    $response->assertDontSee('Kamar Histori Tenant Lain');
});

test('user can release santri from room and history records empty destination', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('manage kamar');
    $room = Room::factory()->forTenant($pengurus->tenant)->create([
        'name' => 'Asrama Release',
    ]);
    $santri = Santri::factory()->forTenant($pengurus->tenant)->create([
        'full_name' => 'Santri Dikeluarkan',
        'status' => Santri::STATUS_ACTIVE,
        'room_id' => $room->id,
    ]);

    $this
        ->actingAs($pengurus)
        ->delete(route('rooms.santris.release', [$room, $santri]))
        ->assertRedirect(route('rooms.index', absolute: false));

    $santri->refresh();
    $transfer = RoomTransfer::query()->first();

    expect($santri->room_id)->toBeNull();
    expect($santri->fresh()->room_id)->toBeNull();
    expect(RoomTransfer::query()->count())->toBe(1);
    expect($transfer?->from_room_id)->toBe($room->id);
    expect($transfer?->from_room_name)->toBe('Asrama Release');
    expect($transfer?->to_room_id)->toBeNull();
    expect($transfer?->to_room_name)->toBeNull();
    expect($transfer?->moved_by)->toBe($pengurus->id);

    $response = $this
        ->actingAs($pengurus)
        ->get(route('rooms.index'));

    $response->assertOk();
    $response->assertSee('Santri Dikeluarkan');
    $response->assertSee('Asrama Release');
    $response->assertSee('Belum berkamar');
});

test('release santri does not run when santri is not assigned to selected room', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('manage kamar');
    $roomA = Room::factory()->forTenant($pengurus->tenant)->create([
        'name' => 'Asrama A',
    ]);
    $roomB = Room::factory()->forTenant($pengurus->tenant)->create([
        'name' => 'Asrama B',
    ]);
    $santri = Santri::factory()->forTenant($pengurus->tenant)->create([
        'status' => Santri::STATUS_ACTIVE,
        'room_id' => $roomB->id,
    ]);

    $this
        ->actingAs($pengurus)
        ->delete(route('rooms.santris.release', [$roomA, $santri]))
        ->assertRedirect(route('rooms.index', absolute: false));

    $santri->refresh();

    expect($santri->room_id)->toBe($roomB->id);
    expect($santri->room?->name)->toBe('Asrama B');
    expect(RoomTransfer::query()->count())->toBe(0);
});

test('room with assigned santri can not be deleted', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('manage kamar');
    $room = Room::factory()->forTenant($admin->tenant)->create([
        'name' => 'Asrama Terisi',
    ]);
    Santri::factory()->forTenant($admin->tenant)->create([
        'room_id' => $room->id,
    ]);

    $this
        ->actingAs($admin)
        ->delete(route('rooms.destroy', $room))
        ->assertRedirect(route('rooms.index', absolute: false));

    expect(Room::query()->whereKey($room->id)->exists())->toBeTrue();
});

test('user without manage kamar permission can not access room management', function () {
    $pengurus = tenantUser('Pengurus');

    $this
        ->actingAs($pengurus)
        ->get(route('rooms.index'))
        ->assertForbidden();
});
