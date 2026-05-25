<?php

use App\Models\Room;
use App\Models\DataExport;
use App\Models\Santri;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\DataExportCompletedNotification;
use App\Jobs\GenerateDataExportJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Superadmin', 'web');
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');
    Role::findOrCreate('Musyrif/Ustadz', 'web');
    Role::findOrCreate('Wali Santri', 'web');

    Permission::findOrCreate('view santri', 'web');
    Permission::findOrCreate('create santri', 'web');
    Permission::findOrCreate('update santri', 'web');
    Permission::findOrCreate('delete santri', 'web');
});

test('user with santri permission can view the santri management page', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('view santri');

    $response = $this
        ->actingAs($pengurus)
        ->get(route('santri.index'));

    $response->assertOk();
    $response->assertSee('Manajemen Santri');
});

test('superadmin can view santri across tenants', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $superadmin->givePermissionTo('view santri');

    $tenantA = Tenant::factory()->activeSubscription()->create();
    $tenantB = Tenant::factory()->activeSubscription()->create();

    Santri::factory()->forTenant($tenantA)->create([
        'full_name' => 'Santri Tenant A',
    ]);

    Santri::factory()->forTenant($tenantB)->create([
        'full_name' => 'Santri Tenant B',
    ]);

    $response = $this
        ->actingAs($superadmin)
        ->get(route('santri.index'));

    $response->assertOk();
    $response->assertSee('Santri Tenant A');
    $response->assertSee('Santri Tenant B');
});

test('superadmin without tenant can not create tenantless santri', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $superadmin->givePermissionTo('create santri');

    $response = $this
        ->actingAs($superadmin)
        ->post(route('santri.store'), [
            'nis' => 'NIS-SUPERADMIN',
            'full_name' => 'Santri Tenantless',
            'gender' => Santri::GENDER_MALE,
            'birth_place' => 'Bandung',
            'birth_date' => '2012-01-10',
            'address' => 'Alamat',
            'guardian_name' => 'Bapak Tenantless',
            'father_name' => 'Ayah Tenantless',
            'mother_name' => 'Ibu Tenantless',
            'guardian_phone_number' => '081234567890',
            'emergency_contact' => '081234567891',
            'entry_date' => '2024-01-01',
            'entry_year' => 2024,
            'room_name' => 'Asrama A1',
            'status' => Santri::STATUS_ACTIVE,
        ]);

    $response->assertForbidden();
    expect(Santri::query()->where('nis', 'NIS-SUPERADMIN')->exists())->toBeFalse();
    expect(Santri::query()->whereNull('tenant_id')->exists())->toBeFalse();
});

test('superadmin can update or delete santri from tenant operations', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $superadmin->givePermissionTo(['update santri', 'delete santri']);

    $tenant = Tenant::factory()->activeSubscription()->create();
    $santri = Santri::factory()->forTenant($tenant)->create([
        'nis' => 'NIS-READONLY',
        'full_name' => 'Santri Read Only',
        'gender' => Santri::GENDER_MALE,
        'birth_place' => 'Bandung',
        'birth_date' => '2012-01-10',
        'address' => 'Alamat awal',
        'guardian_name' => 'Bapak Readonly',
        'father_name' => 'Ayah Readonly',
        'mother_name' => 'Ibu Readonly',
        'guardian_phone_number' => '081234567890',
        'emergency_contact' => '081234567891',
        'entry_date' => '2024-01-01',
        'entry_year' => 2024,
        'room_name' => 'Asrama A1',
        'status' => Santri::STATUS_ACTIVE,
    ]);

    $response = $this
        ->actingAs($superadmin)
        ->patch(route('santri.update', $santri), [
            'nis' => 'NIS-READONLY-UPDATED',
            'full_name' => 'Santri Updated By Superadmin',
            'gender' => Santri::GENDER_FEMALE,
            'birth_place' => 'Garut',
            'birth_date' => '2012-01-10',
            'address' => 'Alamat baru',
            'guardian_name' => 'Ibu Readonly',
            'father_name' => 'Ayah Baru',
            'mother_name' => 'Ibu Baru',
            'guardian_phone_number' => '081234567892',
            'emergency_contact' => '081234567893',
            'entry_date' => '2024-01-01',
            'entry_year' => 2024,
            'room_name' => 'Asrama B1',
            'status' => Santri::STATUS_ALUMNI,
        ]);

    $response->assertRedirect(route('santri.index', absolute: false));
    expect($santri->fresh()->full_name)->toBe('Santri Updated By Superadmin');

    $response = $this
        ->actingAs($superadmin)
        ->delete(route('santri.destroy', $santri));

    $response->assertRedirect(route('santri.index', absolute: false));
    expect(Santri::find($santri->id))->toBeNull();
});

test('user with view santri permission can search and filter santri', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('view santri');
    $tenant = $pengurus->tenant;

    Santri::factory()->forTenant($tenant)->create([
        'nis' => 'NIS0001',
        'full_name' => 'Ahmad Santri',
        'gender' => Santri::GENDER_MALE,
        'status' => Santri::STATUS_ACTIVE,
    ]);

    Santri::factory()->forTenant($tenant)->create([
        'nis' => 'NIS0002',
        'full_name' => 'Aisyah Santri',
        'gender' => Santri::GENDER_FEMALE,
        'status' => Santri::STATUS_ALUMNI,
    ]);

    $response = $this
        ->actingAs($pengurus)
        ->get(route('santri.index', [
            'q' => 'Ahmad',
            'gender' => Santri::GENDER_MALE,
            'status' => Santri::STATUS_ACTIVE,
        ]));

    $response->assertOk();
    $response->assertSee('Ahmad Santri');
    $response->assertDontSee('Aisyah Santri');
});

test('santri views prefer structured room relationship over stale legacy room name', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('view santri');
    $room = Room::factory()->forTenant($pengurus->tenant)->create([
        'name' => 'Asrama Master Baru',
    ]);
    $santri = Santri::factory()->forTenant($pengurus->tenant)->create([
        'full_name' => 'Santri Kamar Relasi',
        'room_id' => $room->id,
        'room_name' => 'Asrama Lama Stale',
    ]);

    $this
        ->actingAs($pengurus)
        ->get(route('santri.index'))
        ->assertOk()
        ->assertSee('Asrama Master Baru')
        ->assertDontSee('Asrama Lama Stale');

    $this
        ->actingAs($pengurus)
        ->get(route('santri.show', $santri))
        ->assertOk()
        ->assertSee('Asrama Master Baru')
        ->assertDontSee('Asrama Lama Stale');

    $csv = $this
        ->actingAs($pengurus)
        ->get(route('santri.export'))
        ->streamedContent();

    expect($csv)->toContain('Asrama Master Baru');
    expect($csv)->not->toContain('Asrama Lama Stale');
});

test('santri list is scoped to the current tenant', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('view santri');
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    Santri::factory()->forTenant($pengurus->tenant)->create([
        'full_name' => 'Santri Tenant Sendiri',
    ]);

    Santri::factory()->forTenant($otherTenant)->create([
        'full_name' => 'Santri Tenant Lain',
    ]);

    $response = $this
        ->actingAs($pengurus)
        ->get(route('santri.index'));

    $response->assertOk();
    $response->assertSee('Santri Tenant Sendiri');
    $response->assertDontSee('Santri Tenant Lain');
});

test('user can export filtered santri data scoped to current tenant', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('view santri');
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    Santri::factory()->forTenant($pengurus->tenant)->create([
        'nis' => 'EXP001',
        'full_name' => 'Export Ahmad',
        'gender' => Santri::GENDER_MALE,
        'status' => Santri::STATUS_ACTIVE,
    ]);

    Santri::factory()->forTenant($pengurus->tenant)->create([
        'nis' => 'EXP002',
        'full_name' => 'Export Aisyah',
        'gender' => Santri::GENDER_FEMALE,
        'status' => Santri::STATUS_ALUMNI,
    ]);

    Santri::factory()->forTenant($otherTenant)->create([
        'nis' => 'EXP999',
        'full_name' => 'Export Tenant Lain',
        'gender' => Santri::GENDER_MALE,
        'status' => Santri::STATUS_ACTIVE,
    ]);

    $response = $this
        ->actingAs($pengurus)
        ->get(route('santri.export', [
            'q' => 'Export',
            'gender' => Santri::GENDER_MALE,
            'status' => Santri::STATUS_ACTIVE,
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $csv = $response->streamedContent();

    expect($csv)->toContain('Export Ahmad');
    expect($csv)->toContain('EXP001');
    expect($csv)->not->toContain('Export Aisyah');
    expect($csv)->not->toContain('Export Tenant Lain');
});

test('large santri export is queued instead of streamed', function () {
    config(['exports.inline_threshold' => 1]);
    Queue::fake();

    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('view santri');

    Santri::factory()->count(2)->forTenant($pengurus->tenant)->create([
        'status' => Santri::STATUS_ACTIVE,
    ]);

    $response = $this
        ->actingAs($pengurus)
        ->get(route('santri.export'));

    $response->assertRedirect(route('santri.index', absolute: false));
    $response->assertSessionHas('success');

    $export = DataExport::query()->first();

    expect($export)->not->toBeNull();
    expect($export?->type)->toBe(DataExport::TYPE_SANTRI);
    expect($export?->status)->toBe(DataExport::STATUS_QUEUED);
    expect($export?->row_count)->toBe(2);
    expect($export?->user_id)->toBe($pengurus->id);

    Queue::assertPushed(GenerateDataExportJob::class, fn (GenerateDataExportJob $job) => $job->dataExportId === $export?->id);

    $this
        ->actingAs($pengurus)
        ->get(route('santri.index'))
        ->assertOk()
        ->assertSee('1 export sedang diproses')
        ->assertSee('25%');
});

test('queued santri export job writes csv file', function () {
    Storage::fake('local');

    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('view santri');

    Santri::factory()->forTenant($pengurus->tenant)->create([
        'nis' => 'JOB-EXP-001',
        'full_name' => 'Santri Job Export',
    ]);

    $export = DataExport::query()->create([
        'tenant_id' => $pengurus->tenant_id,
        'user_id' => $pengurus->id,
        'type' => DataExport::TYPE_SANTRI,
        'name' => 'Export Data Santri',
        'status' => DataExport::STATUS_QUEUED,
        'disk' => 'local',
        'filename' => 'data-santri-job.csv',
        'filters' => [],
        'row_count' => 1,
    ]);

    (new GenerateDataExportJob($export->id))->handle(app(\App\Services\ActivityLogger::class));

    $export->refresh();

    expect($export->status)->toBe(DataExport::STATUS_COMPLETED);
    expect($export->path)->not->toBeNull();

    Storage::disk('local')->assertExists($export->path);
    expect(Storage::disk('local')->get($export->path))
        ->toContain('JOB-EXP-001')
        ->toContain('Santri Job Export');

    $notification = $pengurus->notifications()->first();

    expect($notification)->not->toBeNull();
    expect($notification?->type)->toBe(DataExportCompletedNotification::class);
    expect($notification?->data['title'])->toBe('Export selesai');
    expect($notification?->data['url'])->toBe(route('exports.download', $export, false));

    $response = $this
        ->actingAs($pengurus)
        ->get(route('notifications.show', $notification));

    $response->assertRedirect(route('exports.download', $export, absolute: false));
    expect($notification->fresh()?->read_at)->not->toBeNull();
});

test('santri model queries are scoped to the current tenant by default', function () {
    $pengurus = tenantUser('Pengurus');
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    Santri::factory()->forTenant($pengurus->tenant)->create([
        'full_name' => 'Santri Query Tenant Sendiri',
    ]);

    Santri::factory()->forTenant($otherTenant)->create([
        'full_name' => 'Santri Query Tenant Lain',
    ]);

    $this->actingAs($pengurus);

    expect(Santri::query()->pluck('full_name')->all())
        ->toBe(['Santri Query Tenant Sendiri']);

    expect(Santri::query()->withoutTenantScope()->count())->toBe(2);
});

test('user can not view santri detail from another tenant', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('view santri');
    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $otherSantri = Santri::factory()->forTenant($otherTenant)->create();

    $response = $this
        ->actingAs($pengurus)
        ->get(route('santri.show', $otherSantri));

    $response->assertNotFound();
});

test('nis can be reused by different tenants but remains unique within one tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('create santri');
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    Santri::factory()->forTenant($admin->tenant)->create([
        'nis' => 'NIS-SAMA',
    ]);

    Santri::factory()->forTenant($otherTenant)->create([
        'nis' => 'NIS-SAMA',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('santri.index'))
        ->post(route('santri.store'), [
            'nis' => 'NIS-SAMA',
            'full_name' => 'Duplikat Tenant Sama',
            'gender' => Santri::GENDER_MALE,
            'birth_place' => 'Bandung',
            'birth_date' => '2012-01-10',
            'address' => 'Alamat',
            'guardian_name' => 'Bapak Duplikat',
            'father_name' => 'Ayah Duplikat',
            'mother_name' => 'Ibu Duplikat',
            'guardian_phone_number' => '081234567890',
            'emergency_contact' => '081234567891',
            'entry_date' => '2024-01-01',
            'entry_year' => 2024,
            'room_name' => 'Asrama A1',
            'status' => Santri::STATUS_ACTIVE,
        ]);

    $response->assertRedirect(route('santri.index', absolute: false));
    $response->assertSessionHasErrors(['nis'], null, 'createSantri');
    expect(Santri::query()->withoutTenantScope()->where('nis', 'NIS-SAMA')->count())->toBe(2);
});

test('user with permission can create santri', function () {
    Storage::fake('public');

    $admin = tenantUser('Admin');
    $admin->givePermissionTo('view santri');
    $admin->givePermissionTo('create santri');

    $photo = function_exists('imagecreatetruecolor')
        ? UploadedFile::fake()->image('santri.png', 400, 400)->size(512)
        : null;

    $payload = [
        'nis' => 'NIS1001',
        'full_name' => 'Muhammad Fulan',
        'gender' => Santri::GENDER_MALE,
        'birth_place' => 'Bandung',
        'birth_date' => '2011-05-01',
        'address' => 'Jl. Pesantren No. 1',
        'guardian_name' => 'Bapak Fulan',
        'father_name' => 'Fulan Senior',
        'mother_name' => 'Ibu Fulan',
        'guardian_phone_number' => '081234567890',
        'emergency_contact' => '081298765432',
        'entry_date' => '2024-07-10',
        'entry_year' => 2024,
        'room_name' => 'Asrama A1',
        'notes' => 'Perlu pemantauan adaptasi awal.',
        'status' => Santri::STATUS_ACTIVE,
    ];

    if ($photo) {
        $payload['photo'] = $photo;
    }

    $response = $this
        ->actingAs($admin)
        ->post(route('santri.store'), $payload);

    $response->assertRedirect(route('santri.index', absolute: false));

    $santri = Santri::query()->where('nis', 'NIS1001')->first();

    expect($santri)->not->toBeNull();
    expect($santri->full_name)->toBe('Muhammad Fulan');
    expect($santri->created_by)->toBe($admin->id);
    expect($santri->father_name)->toBe('Fulan Senior');
    expect($santri->mother_name)->toBe('Ibu Fulan');
    expect($santri->room?->name)->toBe('Asrama A1');
    expect($santri->room_id)->not->toBeNull();
    expect($santri->room?->name)->toBe('Asrama A1');
    expect($santri->entry_year)->toBe(2024);

    if ($photo) {
        expect($santri->photo_path)->toStartWith('santri-photos/');
        Storage::disk('public')->assertExists($santri->photo_path);
    } else {
        expect($santri->photo_path)->toBeNull();
    }
});

test('user can create santri by selecting an existing room', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view santri', 'create santri']);
    $room = Room::factory()->forTenant($admin->tenant)->create([
        'name' => 'Asrama Dropdown A',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('santri.index'))
        ->assertOk()
        ->assertSee('Asrama Dropdown A');

    $response = $this
        ->actingAs($admin)
        ->post(route('santri.store'), [
            'nis' => 'NIS-ROOM-ID',
            'full_name' => 'Santri Pilih Kamar',
            'gender' => Santri::GENDER_MALE,
            'birth_place' => 'Bandung',
            'birth_date' => '2012-01-10',
            'address' => 'Alamat',
            'guardian_name' => 'Bapak Pilih',
            'father_name' => 'Ayah Pilih',
            'mother_name' => 'Ibu Pilih',
            'guardian_phone_number' => '081234567890',
            'emergency_contact' => '081234567891',
            'entry_date' => '2024-01-01',
            'entry_year' => 2024,
            'room_id' => $room->id,
            'status' => Santri::STATUS_ACTIVE,
        ]);

    $response->assertRedirect(route('santri.index', absolute: false));

    $santri = Santri::query()->where('nis', 'NIS-ROOM-ID')->first();

    expect($santri)->not->toBeNull();
    expect($santri->room_id)->toBe($room->id);
    expect($santri->room?->name)->toBe('Asrama Dropdown A');
    expect(Room::query()->withoutTenantScope()->where('tenant_id', $admin->tenant_id)->where('name', 'Asrama Dropdown A')->count())->toBe(1);
});

test('user can not assign santri to room from another tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('create santri');
    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $otherRoom = Room::factory()->forTenant($otherTenant)->create([
        'name' => 'Asrama Tenant Lain',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('santri.index'))
        ->post(route('santri.store'), [
            'nis' => 'NIS-ROOM-OTHER',
            'full_name' => 'Santri Salah Kamar',
            'gender' => Santri::GENDER_MALE,
            'birth_place' => 'Bandung',
            'birth_date' => '2012-01-10',
            'address' => 'Alamat',
            'guardian_name' => 'Bapak Salah',
            'father_name' => 'Ayah Salah',
            'mother_name' => 'Ibu Salah',
            'guardian_phone_number' => '081234567890',
            'emergency_contact' => '081234567891',
            'entry_date' => '2024-01-01',
            'entry_year' => 2024,
            'room_id' => $otherRoom->id,
            'status' => Santri::STATUS_ACTIVE,
        ]);

    $response->assertRedirect(route('santri.index', absolute: false));
    $response->assertSessionHasErrors(['room_id'], null, 'createSantri');
    expect(Santri::query()->where('nis', 'NIS-ROOM-OTHER')->exists())->toBeFalse();
});

test('user can create santri with wali portal account', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view santri', 'create santri']);
    $wali = User::factory()->forTenant($admin->tenant)->create([
        'name' => 'Wali Portal Ahmad',
    ]);
    $wali->assignRole('Wali Santri');

    $response = $this
        ->actingAs($admin)
        ->post(route('santri.store'), [
            'nis' => 'NIS-WALI-001',
            'full_name' => 'Ahmad Portal Wali',
            'gender' => Santri::GENDER_MALE,
            'birth_place' => 'Bandung',
            'birth_date' => '2012-01-10',
            'address' => 'Alamat Wali',
            'guardian_name' => 'Bapak Portal',
            'father_name' => 'Ayah Portal',
            'mother_name' => 'Ibu Portal',
            'guardian_phone_number' => '081234567890',
            'emergency_contact' => '081234567891',
            'entry_date' => '2024-01-01',
            'entry_year' => 2024,
            'room_name' => 'Asrama A1',
            'status' => Santri::STATUS_ACTIVE,
            'guardian_user_ids' => [$wali->id],
        ]);

    $response->assertRedirect(route('santri.index', absolute: false));

    $santri = Santri::query()->where('nis', 'NIS-WALI-001')->first();

    expect($santri)->not->toBeNull();
    $this->assertDatabaseHas('santri_guardians', [
        'tenant_id' => $admin->tenant_id,
        'santri_id' => $santri->id,
        'user_id' => $wali->id,
        'relationship' => 'Wali',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('santri.index'))
        ->assertOk()
        ->assertSee('Wali Portal Ahmad');

    $this
        ->actingAs($admin)
        ->get(route('santri.show', $santri))
        ->assertOk()
        ->assertSee('Wali Portal Ahmad');
});

test('santri can not be created with entry date before birth date', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('create santri');

    $response = $this
        ->actingAs($admin)
        ->from(route('santri.index'))
        ->post(route('santri.store'), [
            'nis' => 'NIS1002',
            'full_name' => 'Tanggal Tidak Valid',
            'gender' => Santri::GENDER_MALE,
            'birth_place' => 'Bandung',
            'birth_date' => '2012-01-10',
            'address' => 'Alamat',
            'guardian_name' => 'Bapak Tanggal',
            'father_name' => 'Ayah Tanggal',
            'mother_name' => 'Ibu Tanggal',
            'guardian_phone_number' => '081234567891',
            'emergency_contact' => '081234567892',
            'entry_date' => '2010-01-01',
            'entry_year' => 2024,
            'room_name' => 'Asrama B1',
            'status' => Santri::STATUS_ACTIVE,
        ]);

    $response->assertRedirect(route('santri.index', absolute: false));
    $response->assertSessionHasErrors(['entry_date'], null, 'createSantri');
    expect(Santri::query()->where('nis', 'NIS1002')->exists())->toBeFalse();
});

test('santri can not be created with invalid guardian phone number', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('create santri');

    $response = $this
        ->actingAs($admin)
        ->from(route('santri.index'))
        ->post(route('santri.store'), [
            'nis' => 'NIS1003',
            'full_name' => 'Nomor Wali Tidak Valid',
            'gender' => Santri::GENDER_MALE,
            'birth_place' => 'Bandung',
            'birth_date' => '2012-01-10',
            'address' => 'Alamat',
            'guardian_name' => 'Bapak Nomor',
            'father_name' => 'Ayah Nomor',
            'mother_name' => 'Ibu Nomor',
            'guardian_phone_number' => 'nomorwaliabc',
            'emergency_contact' => '081234567893',
            'entry_date' => '2024-01-01',
            'entry_year' => 2024,
            'room_name' => 'Asrama B2',
            'status' => Santri::STATUS_ACTIVE,
        ]);

    $response->assertRedirect(route('santri.index', absolute: false));
    $response->assertSessionHasErrors(['guardian_phone_number'], null, 'createSantri');
    expect(Santri::query()->where('nis', 'NIS1003')->exists())->toBeFalse();
});

test('santri can be created with indonesian guardian phone number formats', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('create santri');

    foreach (['081234567890', '6281234567890', '+6281234567890'] as $index => $phoneNumber) {
        $response = $this
            ->actingAs($admin)
            ->post(route('santri.store'), [
                'nis' => 'NIS20'.$index,
                'full_name' => 'Format Nomor '.$index,
                'gender' => Santri::GENDER_MALE,
                'birth_place' => 'Bandung',
                'birth_date' => '2012-01-10',
                'address' => 'Alamat',
                'guardian_name' => 'Bapak Format '.$index,
                'father_name' => 'Ayah Format '.$index,
                'mother_name' => 'Ibu Format '.$index,
                'guardian_phone_number' => $phoneNumber,
                'emergency_contact' => '08123000000'.$index,
                'entry_date' => '2024-01-01',
                'entry_year' => 2024,
                'room_name' => 'Asrama C'.$index,
                'status' => Santri::STATUS_ACTIVE,
            ]);

        $response->assertRedirect(route('santri.index', absolute: false));
    }
});

test('santri can be created without guardian data', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('create santri');

    $response = $this
        ->actingAs($admin)
        ->post(route('santri.store'), [
            'nis' => 'NIS2010',
            'full_name' => 'Tanpa Wali Opsional',
            'gender' => Santri::GENDER_MALE,
            'birth_place' => 'Bandung',
            'birth_date' => '2012-01-10',
            'address' => 'Alamat',
            'guardian_name' => '',
            'father_name' => 'Ayah Opsional',
            'mother_name' => 'Ibu Opsional',
            'guardian_phone_number' => '',
            'emergency_contact' => '081234567894',
            'entry_date' => '2024-01-01',
            'entry_year' => 2024,
            'room_name' => 'Asrama C1',
            'status' => Santri::STATUS_ACTIVE,
        ]);

    $response->assertRedirect(route('santri.index', absolute: false));

    $santri = Santri::query()->where('nis', 'NIS2010')->first();

    expect($santri)->not->toBeNull();
    expect($santri->guardian_name)->toBeNull();
    expect($santri->guardian_phone_number)->toBeNull();
});

test('guardian phone number is required when guardian name is filled', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('create santri');

    $response = $this
        ->actingAs($admin)
        ->from(route('santri.index'))
        ->post(route('santri.store'), [
            'nis' => 'NIS2011',
            'full_name' => 'Wali Parsial',
            'gender' => Santri::GENDER_MALE,
            'birth_place' => 'Bandung',
            'birth_date' => '2012-01-10',
            'address' => 'Alamat',
            'guardian_name' => 'Paman Santri',
            'father_name' => 'Ayah Parsial',
            'mother_name' => 'Ibu Parsial',
            'guardian_phone_number' => '',
            'emergency_contact' => '081234567895',
            'entry_date' => '2024-01-01',
            'entry_year' => 2024,
            'room_name' => 'Asrama C2',
            'status' => Santri::STATUS_ACTIVE,
        ]);

    $response->assertRedirect(route('santri.index', absolute: false));
    $response->assertSessionHasErrors(['guardian_phone_number'], null, 'createSantri');
});

test('santri can not be updated with future entry date', function () {
    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('update santri');

    $santri = Santri::factory()->forTenant($pengurus->tenant)->create([
        'birth_date' => '2010-01-01',
        'entry_date' => '2024-01-01',
    ]);

    $response = $this
        ->actingAs($pengurus)
        ->from(route('santri.index'))
        ->patch(route('santri.update', $santri), [
            'nis' => $santri->nis,
            'full_name' => $santri->full_name,
            'gender' => $santri->gender,
            'birth_place' => $santri->birth_place,
            'birth_date' => '2010-01-01',
            'address' => $santri->address,
            'guardian_name' => $santri->guardian_name,
            'father_name' => $santri->father_name,
            'mother_name' => $santri->mother_name,
            'guardian_phone_number' => $santri->guardian_phone_number,
            'emergency_contact' => $santri->emergency_contact,
            'entry_date' => now()->addDay()->format('Y-m-d'),
            'entry_year' => $santri->entry_year,
            'room_name' => $santri->room?->name,
            'notes' => $santri->notes,
            'status' => $santri->status,
            'editing_santri_id' => $santri->id,
        ]);

    $response->assertRedirect(route('santri.index', absolute: false));
    $response->assertSessionHasErrors(['entry_date'], null, 'updateSantri');
    expect($santri->fresh()->entry_date?->format('Y-m-d'))->toBe('2024-01-01');
});

test('user with permission can view santri detail', function () {
    $musyrif = tenantUser('Musyrif/Ustadz');
    $musyrif->givePermissionTo('view santri');

    $santri = Santri::factory()->forTenant($musyrif->tenant)->create([
        'full_name' => 'Santri Detail',
        'nis' => 'NIS2001',
    ]);

    $response = $this
        ->actingAs($musyrif)
        ->get(route('santri.show', $santri));

    $response->assertOk();
    $response->assertSee('Santri Detail');
    $response->assertSee('NIS2001');
    $response->assertSee((string) $santri->entry_year);
    $response->assertSee($santri->displayRoomName());
    $response->assertSee($santri->father_name);
    $response->assertSee($santri->mother_name);
});

test('user with permission can delete santri photo when requested during update', function () {
    Storage::fake('public');

    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('view santri');
    $pengurus->givePermissionTo('update santri');

    $existingPhoto = function_exists('imagecreatetruecolor')
        ? UploadedFile::fake()->image('santri-lama.png', 300, 300)->size(300)
        : UploadedFile::fake()->create('santri-lama.png', 300, 'image/png');
    $existingPath = $existingPhoto->store('santri-photos', 'public');

    $santri = Santri::factory()->forTenant($pengurus->tenant)->create([
        'nis' => 'NIS4001',
        'full_name' => 'Nama Lama',
        'status' => Santri::STATUS_ACTIVE,
        'photo_path' => $existingPath,
    ]);

    $payload = [
        'nis' => 'NIS4001',
        'full_name' => 'Nama Lama Tetap',
        'gender' => Santri::GENDER_MALE,
        'birth_place' => 'Garut',
        'birth_date' => '2010-01-01',
        'address' => 'Alamat Baru',
        'guardian_name' => 'Ibu Contact',
        'father_name' => 'Ayah Contact',
        'mother_name' => 'Ibu Contact',
        'guardian_phone_number' => '089999999998',
        'emergency_contact' => '081277777778',
        'entry_date' => '2025-01-05',
        'entry_year' => 2025,
        'room_name' => 'Asrama Putra 1',
        'notes' => 'Hapus foto lama saja.',
        'status' => Santri::STATUS_ACTIVE,
        'delete_photo' => '1',
        'editing_santri_id' => $santri->id,
    ];

    $response = $this
        ->actingAs($pengurus)
        ->patch(route('santri.update', $santri), $payload);

    $response->assertRedirect(route('santri.index', absolute: false));

    $santri = $santri->fresh();

    expect($santri->photo_path)->toBeNull();
    Storage::disk('public')->assertMissing($existingPath);
});

test('user with permission can update santri', function () {
    Storage::fake('public');

    $pengurus = tenantUser('Pengurus');
    $pengurus->givePermissionTo('view santri');
    $pengurus->givePermissionTo('update santri');

    $photo = function_exists('imagecreatetruecolor')
        ? UploadedFile::fake()->image('santri-baru.png', 500, 500)->size(700)
        : null;
    $existingPhotoPath = $photo
        ? UploadedFile::fake()->image('santri-lama-update.png', 300, 300)->store('santri-photos', 'public')
        : null;

    $santri = Santri::factory()->forTenant($pengurus->tenant)->create([
        'nis' => 'NIS3001',
        'full_name' => 'Nama Lama',
        'status' => Santri::STATUS_ACTIVE,
        'photo_path' => $existingPhotoPath,
    ]);

    $payload = [
        'nis' => 'NIS3001X',
        'full_name' => 'Nama Baru',
        'gender' => Santri::GENDER_FEMALE,
        'birth_place' => 'Garut',
        'birth_date' => '2010-01-01',
        'address' => 'Alamat Baru',
        'guardian_name' => 'Ibu Baru',
        'father_name' => 'Ayah Baru',
        'mother_name' => 'Ibu Kandung Baru',
        'guardian_phone_number' => '089999999999',
        'emergency_contact' => '081277777777',
        'entry_date' => '2025-01-05',
        'entry_year' => 2025,
        'room_name' => 'Asrama Putri 2',
        'notes' => 'Santri pindah kamar setelah semester pertama.',
        'status' => Santri::STATUS_ALUMNI,
    ];

    if ($photo) {
        $payload['photo'] = $photo;
    }

    $response = $this
        ->actingAs($pengurus)
        ->patch(route('santri.update', $santri), $payload);

    $response->assertRedirect(route('santri.index', absolute: false));

    $santri = $santri->fresh();

    expect($santri->nis)->toBe('NIS3001X');
    expect($santri->full_name)->toBe('Nama Baru');
    expect($santri->status)->toBe(Santri::STATUS_ALUMNI);
    expect($santri->room?->name)->toBe('Asrama Putri 2');
    expect($santri->room_id)->not->toBeNull();
    expect($santri->room?->name)->toBe('Asrama Putri 2');
    expect($santri->entry_year)->toBe(2025);
    expect($santri->father_name)->toBe('Ayah Baru');
    expect($santri->mother_name)->toBe('Ibu Kandung Baru');
    expect($santri->emergency_contact)->toBe('081277777777');

    if ($photo) {
        expect($santri->photo_path)->toStartWith('santri-photos/');
        Storage::disk('public')->assertExists($santri->photo_path);
        Storage::disk('public')->assertMissing($existingPhotoPath);
    }
});

test('user can update wali portal accounts from santri management', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view santri', 'update santri']);
    $oldWali = User::factory()->forTenant($admin->tenant)->create([
        'name' => 'Wali Lama',
    ]);
    $oldWali->assignRole('Wali Santri');
    $newWali = User::factory()->forTenant($admin->tenant)->create([
        'name' => 'Wali Baru',
    ]);
    $newWali->assignRole('Wali Santri');
    $santri = Santri::factory()->forTenant($admin->tenant)->create([
        'nis' => 'NIS-WALI-EDIT',
        'full_name' => 'Santri Relasi Wali',
        'guardian_name' => 'Wali Teks',
        'guardian_phone_number' => '081234567890',
    ]);
    $santri->guardians()->attach($oldWali->id, [
        'tenant_id' => $admin->tenant_id,
        'relationship' => 'Ayah',
    ]);

    $response = $this
        ->actingAs($admin)
        ->patch(route('santri.update', $santri), [
            'nis' => 'NIS-WALI-EDIT',
            'full_name' => 'Santri Relasi Wali',
            'gender' => $santri->gender,
            'birth_place' => $santri->birth_place,
            'birth_date' => $santri->birth_date->toDateString(),
            'address' => $santri->address,
            'guardian_name' => 'Wali Teks',
            'father_name' => $santri->father_name,
            'mother_name' => $santri->mother_name,
            'guardian_phone_number' => '081234567890',
            'emergency_contact' => $santri->emergency_contact,
            'entry_date' => $santri->entry_date->toDateString(),
            'entry_year' => $santri->entry_year,
            'room_name' => $santri->room?->name,
            'notes' => $santri->notes,
            'status' => $santri->status,
            'guardian_user_ids' => [$newWali->id],
            'editing_santri_id' => $santri->id,
        ]);

    $response->assertRedirect(route('santri.index', absolute: false));

    $this->assertDatabaseMissing('santri_guardians', [
        'santri_id' => $santri->id,
        'user_id' => $oldWali->id,
    ]);
    $this->assertDatabaseHas('santri_guardians', [
        'tenant_id' => $admin->tenant_id,
        'santri_id' => $santri->id,
        'user_id' => $newWali->id,
    ]);
});

test('santri can not be linked to wali account from another tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view santri', 'update santri']);
    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $otherWali = User::factory()->forTenant($otherTenant)->create([
        'name' => 'Wali Tenant Lain',
    ]);
    $otherWali->assignRole('Wali Santri');
    $santri = Santri::factory()->forTenant($admin->tenant)->create([
        'nis' => 'NIS-WALI-TENANT',
        'guardian_name' => 'Wali Teks',
        'guardian_phone_number' => '081234567890',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('santri.index'))
        ->patch(route('santri.update', $santri), [
            'nis' => $santri->nis,
            'full_name' => $santri->full_name,
            'gender' => $santri->gender,
            'birth_place' => $santri->birth_place,
            'birth_date' => $santri->birth_date->toDateString(),
            'address' => $santri->address,
            'guardian_name' => $santri->guardian_name,
            'father_name' => $santri->father_name,
            'mother_name' => $santri->mother_name,
            'guardian_phone_number' => $santri->guardian_phone_number,
            'emergency_contact' => $santri->emergency_contact,
            'entry_date' => $santri->entry_date->toDateString(),
            'entry_year' => $santri->entry_year,
            'room_name' => $santri->room?->name,
            'notes' => $santri->notes,
            'status' => $santri->status,
            'guardian_user_ids' => [$otherWali->id],
            'editing_santri_id' => $santri->id,
        ]);

    $response->assertRedirect(route('santri.index', absolute: false));
    $response->assertSessionHasErrors('guardian_user_ids', null, 'updateSantri');
    $this->assertDatabaseMissing('santri_guardians', [
        'santri_id' => $santri->id,
        'user_id' => $otherWali->id,
    ]);
});

test('user with permission can delete santri', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('view santri');
    $admin->givePermissionTo('delete santri');

    $santri = Santri::factory()->forTenant($admin->tenant)->create();

    $response = $this
        ->actingAs($admin)
        ->delete(route('santri.destroy', $santri));

    $response->assertRedirect(route('santri.index', absolute: false));
    expect($santri->fresh())->toBeNull();
});

test('user without santri permission can not access santri page', function () {
    $user = tenantUser('Pengurus');

    $response = $this
        ->actingAs($user)
        ->get(route('santri.index'));

    $response->assertForbidden();
});
