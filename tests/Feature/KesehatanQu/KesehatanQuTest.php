<?php

use App\Models\KesehatanObat;
use App\Models\KesehatanPemeriksaan;
use App\Models\Santri;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Superadmin', 'web');
    Role::findOrCreate('Admin', 'web');
    Permission::findOrCreate('manage kesehatan', 'web');
    $this->user = User::factory()->create();
    $this->user->assignRole('Admin');
    $this->user->givePermissionTo('manage kesehatan');

    $this->tenant = Tenant::factory()->create();
    $this->user->update(['tenant_id' => $this->tenant->id]);
    $this->user->refresh();

    $this->santri = Santri::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);
});

test('user can view kesehatan dashboard', function () {
    KesehatanPemeriksaan::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
    ]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('kesehatan.dashboard'));

    $response->assertOk();
    $response->assertSee('Dashboard Kesehatan');
    $response->assertSee('Kunjungan UKS Terbaru');
});

test('user can view rekam medis index page', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('kesehatan.rekam-medis.index'));

    $response->assertOk();
    $response->assertSee('Rekam Medis Santri');
});

test('user can create and update rekam medis', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('kesehatan.rekam-medis.store'), [
            'santri_id' => $this->santri->id,
            'golongan_darah' => 'A',
            'riwayat_penyakit' => 'Asma',
            'alergi_obat' => 'Paracetamol',
            'alergi_makanan' => null,
            'tinggi_badan' => 160.5,
            'berat_badan' => 55.0,
        ]);

    $response->assertRedirect(route('kesehatan.rekam-medis.show', $this->santri));

    $this->assertDatabaseHas('kesehatan_rekam_medis', [
        'santri_id' => $this->santri->id,
        'golongan_darah' => 'A',
        'riwayat_penyakit' => 'Asma',
    ]);

    $response = $this
        ->actingAs($this->user)
        ->patch(route('kesehatan.rekam-medis.update', $this->santri), [
            'santri_id' => $this->santri->id,
            'golongan_darah' => 'B',
            'riwayat_penyakit' => 'Asma',
            'alergi_obat' => null,
            'alergi_makanan' => null,
            'tinggi_badan' => null,
            'berat_badan' => null,
        ]);

    $response->assertRedirect(route('kesehatan.rekam-medis.show', $this->santri));

    $this->assertDatabaseHas('kesehatan_rekam_medis', [
        'santri_id' => $this->santri->id,
        'golongan_darah' => 'B',
    ]);
});

test('user can view pemeriksaan index page', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('kesehatan.pemeriksaan.index'));

    $response->assertOk();
    $response->assertSee('Kunjungan UKS');
});

test('user can create pemeriksaan with obat and rujukan', function () {
    $obat = KesehatanObat::factory()->create([
        'tenant_id' => $this->tenant->id,
        'stok' => 10,
    ]);

    $response = $this
        ->actingAs($this->user)
        ->post(route('kesehatan.pemeriksaan.store'), [
            'santri_id' => $this->santri->id,
            'tanggal_pemeriksaan' => now()->toDateString(),
            'keluhan' => 'Demam dan batuk',
            'diagnosis' => 'Flu',
            'tindakan' => 'Istirahat',
            'rujuk' => '1',
            'tempat_rujukan' => 'RS Umum',
            'tanggal_rujuk' => now()->toDateString(),
            'obat_ids' => [$obat->id],
            'obat_jumlahs' => [2],
            'obat_catatans' => ['Sesuai resep'],
        ]);

    $response->assertRedirect(route('kesehatan.pemeriksaan.index'));

    $this->assertDatabaseHas('kesehatan_pemeriksaans', [
        'santri_id' => $this->santri->id,
        'keluhan' => 'Demam dan batuk',
        'diagnosis' => 'Flu',
    ]);

    $this->assertDatabaseHas('kesehatan_rujukans', [
        'tempat_rujukan' => 'RS Umum',
    ]);

    $this->assertDatabaseHas('kesehatan_pemakaian_obats', [
        'obat_id' => $obat->id,
        'jumlah' => 2,
    ]);

    $this->assertDatabaseHas('kesehatan_obats', [
        'id' => $obat->id,
        'stok' => 8,
    ]);
});

test('user can view detail pemeriksaan', function () {
    $pemeriksaan = KesehatanPemeriksaan::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
    ]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('kesehatan.pemeriksaan.show', $pemeriksaan));

    $response->assertOk();
    $response->assertSee('Detail Pemeriksaan');
});

test('user can delete pemeriksaan', function () {
    $pemeriksaan = KesehatanPemeriksaan::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
    ]);

    $response = $this
        ->actingAs($this->user)
        ->delete(route('kesehatan.pemeriksaan.destroy', $pemeriksaan));

    $response->assertRedirect(route('kesehatan.pemeriksaan.index'));
    $this->assertDatabaseMissing('kesehatan_pemeriksaans', ['id' => $pemeriksaan->id]);
});

test('user can view obat index page', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('kesehatan.obat.index'));

    $response->assertOk();
    $response->assertSee('Stok Obat UKS');
});

test('user can create and update obat', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('kesehatan.obat.store'), [
            'nama_obat' => 'Paracetamol',
            'jenis' => 'Tablet',
            'stok' => 100,
            'satuan' => 'strip',
            'expired_date' => now()->addYear()->toDateString(),
        ]);

    $response->assertRedirect(route('kesehatan.obat.index'));

    $this->assertDatabaseHas('kesehatan_obats', [
        'nama_obat' => 'Paracetamol',
        'stok' => 100,
    ]);

    $obat = KesehatanObat::where('nama_obat', 'Paracetamol')->first();

    $response = $this
        ->actingAs($this->user)
        ->patch(route('kesehatan.obat.update', $obat), [
            'nama_obat' => 'Paracetamol 500mg',
            'jenis' => 'Tablet',
            'stok' => 50,
            'satuan' => 'strip',
        ]);

    $response->assertRedirect(route('kesehatan.obat.index'));

    $this->assertDatabaseHas('kesehatan_obats', [
        'id' => $obat->id,
        'nama_obat' => 'Paracetamol 500mg',
        'stok' => 50,
    ]);
});

test('user can delete obat', function () {
    $obat = KesehatanObat::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);

    $response = $this
        ->actingAs($this->user)
        ->delete(route('kesehatan.obat.destroy', $obat));

    $response->assertRedirect(route('kesehatan.obat.index'));
    $this->assertDatabaseMissing('kesehatan_obats', ['id' => $obat->id]);
});

test('user can view laporan page', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('kesehatan.laporan.index'));

    $response->assertOk();
    $response->assertSee('Laporan Kesehatan');
});

test('user without manage kesehatan permission cannot access kesehatan module', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);
    $user->assignRole('Admin');

    $response = $this
        ->actingAs($user)
        ->get(route('kesehatan.dashboard'));

    $response->assertForbidden();
});
