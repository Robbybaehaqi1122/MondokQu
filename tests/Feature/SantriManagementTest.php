<?php

use App\Models\Santri;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Superadmin', 'web');
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');
    Role::findOrCreate('Musyrif/Ustadz', 'web');

    Permission::findOrCreate('view santri', 'web');
    Permission::findOrCreate('create santri', 'web');
    Permission::findOrCreate('update santri', 'web');
    Permission::findOrCreate('delete santri', 'web');
});

test('user with santri permission can view the santri management page', function () {
    $pengurus = User::factory()->create();
    $pengurus->givePermissionTo('view santri');

    $response = $this
        ->actingAs($pengurus)
        ->get(route('santri.index'));

    $response->assertOk();
    $response->assertSee('Manajemen Santri');
});

test('user with view santri permission can search and filter santri', function () {
    $pengurus = User::factory()->create();
    $pengurus->givePermissionTo('view santri');

    Santri::factory()->create([
        'nis' => 'NIS0001',
        'full_name' => 'Ahmad Santri',
        'gender' => Santri::GENDER_MALE,
        'status' => Santri::STATUS_ACTIVE,
    ]);

    Santri::factory()->create([
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

test('user with permission can create santri', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
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
    expect($santri->room_name)->toBe('Asrama A1');
    expect($santri->entry_year)->toBe(2024);

    if ($photo) {
        expect($santri->photo_path)->toStartWith('santri-photos/');
        Storage::disk('public')->assertExists($santri->photo_path);
    } else {
        expect($santri->photo_path)->toBeNull();
    }
});

test('santri can not be created with entry date before birth date', function () {
    $admin = User::factory()->create();
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
    $admin = User::factory()->create();
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
    $admin = User::factory()->create();
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
    $admin = User::factory()->create();
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
    $admin = User::factory()->create();
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
    $pengurus = User::factory()->create();
    $pengurus->givePermissionTo('update santri');

    $santri = Santri::factory()->create([
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
            'room_name' => $santri->room_name,
            'notes' => $santri->notes,
            'status' => $santri->status,
            'editing_santri_id' => $santri->id,
        ]);

    $response->assertRedirect(route('santri.index', absolute: false));
    $response->assertSessionHasErrors(['entry_date'], null, 'updateSantri');
    expect($santri->fresh()->entry_date?->format('Y-m-d'))->toBe('2024-01-01');
});

test('user with permission can view santri detail', function () {
    $musyrif = User::factory()->create();
    $musyrif->givePermissionTo('view santri');

    $santri = Santri::factory()->create([
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
    $response->assertSee($santri->room_name);
    $response->assertSee($santri->father_name);
    $response->assertSee($santri->mother_name);
});

test('user with permission can update santri', function () {
    Storage::fake('public');

    $pengurus = User::factory()->create();
    $pengurus->givePermissionTo('view santri');
    $pengurus->givePermissionTo('update santri');

    $photo = function_exists('imagecreatetruecolor')
        ? UploadedFile::fake()->image('santri-baru.png', 500, 500)->size(700)
        : null;

    $santri = Santri::factory()->create([
        'nis' => 'NIS3001',
        'full_name' => 'Nama Lama',
        'status' => Santri::STATUS_ACTIVE,
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
    expect($santri->room_name)->toBe('Asrama Putri 2');
    expect($santri->entry_year)->toBe(2025);
    expect($santri->father_name)->toBe('Ayah Baru');
    expect($santri->mother_name)->toBe('Ibu Kandung Baru');
    expect($santri->emergency_contact)->toBe('081277777777');

    if ($photo) {
        expect($santri->photo_path)->toStartWith('santri-photos/');
        Storage::disk('public')->assertExists($santri->photo_path);
    }
});

test('user with permission can delete santri', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('view santri');
    $admin->givePermissionTo('delete santri');

    $santri = Santri::factory()->create();

    $response = $this
        ->actingAs($admin)
        ->delete(route('santri.destroy', $santri));

    $response->assertRedirect(route('santri.index', absolute: false));
    expect($santri->fresh())->toBeNull();
});

test('user without santri permission can not access santri page', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('santri.index'));

    $response->assertForbidden();
});
