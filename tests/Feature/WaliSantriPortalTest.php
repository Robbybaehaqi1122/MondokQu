<?php

use App\Models\Santri;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Wali Santri', 'web');

    Permission::findOrCreate('view user details', 'web');
    Permission::findOrCreate('update users', 'web');
    Permission::findOrCreate('view portal wali', 'web');
});

test('wali santri dashboard shows only linked santri and payment summary', function () {
    $wali = tenantUser('Wali Santri');
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    $linkedSantri = Santri::factory()->forTenant($wali->tenant)->create([
        'nis' => 'WALI001',
        'full_name' => 'Ahmad Anak Wali',
        'room_name' => 'Asrama A1',
    ]);
    $unlinkedSantri = Santri::factory()->forTenant($wali->tenant)->create([
        'full_name' => 'Santri Tidak Terhubung',
    ]);
    $otherTenantSantri = Santri::factory()->forTenant($otherTenant)->create([
        'full_name' => 'Santri Tenant Lain',
    ]);

    $wali->guardianSantris()->attach($linkedSantri->id, [
        'tenant_id' => $wali->tenant_id,
        'relationship' => 'Ayah',
    ]);

    $invoice = SantriInvoice::factory()->forSantri($linkedSantri)->create([
        'invoice_number' => 'INV-WALI-001',
        'title' => 'SPP Mei Wali',
        'amount' => 500000,
        'paid_amount' => 150000,
        'status' => SantriInvoice::STATUS_PARTIAL,
        'due_date' => now()->subDay(),
    ]);
    SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 150000,
        'paid_at' => now(),
    ]);
    SantriInvoice::factory()->forSantri($unlinkedSantri)->create([
        'title' => 'Tagihan Tidak Terlihat',
        'amount' => 999000,
    ]);
    SantriInvoice::factory()->forSantri($otherTenantSantri)->create([
        'title' => 'Tagihan Tenant Lain',
        'amount' => 888000,
    ]);

    $response = $this
        ->actingAs($wali)
        ->get(route('wali-santri.dashboard'));

    $response->assertOk();
    $response->assertSee('Ahmad Anak Wali');
    $response->assertSee('Ayah');
    $response->assertSee('SPP Mei Wali');
    $response->assertSee('Rp 350.000');
    $response->assertSee('data-mobile-invoice-list', false);
    $response->assertSee('data-mobile-invoice-card', false);
    $response->assertSee('data-mobile-payment-list', false);
    $response->assertSee('data-mobile-payment-card', false);
    $response->assertSee(route('wali-santri.invoices.show', $invoice), false);
    $response->assertDontSee('Santri Tidak Terhubung');
    $response->assertDontSee('Santri Tenant Lain');
    $response->assertDontSee('Tagihan Tidak Terlihat');
    $response->assertDontSee('Tagihan Tenant Lain');
});

test('dashboard route redirects wali santri to portal', function () {
    $wali = tenantUser('Wali Santri');

    $response = $this
        ->actingAs($wali)
        ->get(route('dashboard'));

    $response->assertRedirect(route('wali-santri.dashboard', absolute: false));
});

test('wali santri can view linked invoice detail with payment history', function () {
    $wali = tenantUser('Wali Santri');
    $linkedSantri = Santri::factory()->forTenant($wali->tenant)->create([
        'nis' => 'WALI-DETAIL',
        'full_name' => 'Fatimah Anak Wali',
    ]);

    $wali->guardianSantris()->attach($linkedSantri->id, [
        'tenant_id' => $wali->tenant_id,
        'relationship' => 'Ibu',
    ]);

    $invoice = SantriInvoice::factory()->forSantri($linkedSantri)->create([
        'invoice_number' => 'INV-WALI-DETAIL',
        'title' => 'SPP Detail Wali',
        'amount' => 500000,
        'paid_amount' => 150000,
        'status' => SantriInvoice::STATUS_PARTIAL,
        'notes' => 'Dibayarkan sebelum akhir bulan.',
    ]);

    SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 100000,
        'payment_method' => 'transfer bank',
        'reference_number' => 'REF-WALI-1',
        'note' => 'Pembayaran awal wali',
        'paid_at' => now()->subDays(2),
    ]);
    SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 50000,
        'payment_method' => 'cash',
        'reference_number' => 'REF-WALI-2',
        'paid_at' => now()->subDay(),
    ]);

    $response = $this
        ->actingAs($wali)
        ->get(route('wali-santri.invoices.show', $invoice));

    $response->assertOk();
    $response->assertSee('Detail Tagihan');
    $response->assertSee(route('wali-santri.invoices.receipt', $invoice), false);
    $response->assertSee('SPP Detail Wali');
    $response->assertSee('INV-WALI-DETAIL');
    $response->assertSee('Fatimah Anak Wali');
    $response->assertSee('Rp 500.000');
    $response->assertSee('Rp 150.000');
    $response->assertSee('Rp 350.000');
    $response->assertSee('Transfer Bank');
    $response->assertSee('Cash');
    $response->assertSee('REF-WALI-1');
    $response->assertSee('Pembayaran awal wali');
    $response->assertSee('Dibayarkan sebelum akhir bulan.');
});

test('wali santri can print linked invoice receipt', function () {
    $wali = tenantUser('Wali Santri');
    $linkedSantri = Santri::factory()->forTenant($wali->tenant)->create([
        'nis' => 'WALI-PRINT',
        'full_name' => 'Hasan Anak Wali',
        'room_name' => 'Asrama Print',
    ]);

    $wali->guardianSantris()->attach($linkedSantri->id, [
        'tenant_id' => $wali->tenant_id,
        'relationship' => 'Ayah',
    ]);

    $invoice = SantriInvoice::factory()->forSantri($linkedSantri)->create([
        'invoice_number' => 'INV-WALI-PRINT',
        'title' => 'Kwitansi SPP Wali',
        'amount' => 600000,
        'paid_amount' => 600000,
        'status' => SantriInvoice::STATUS_PAID,
        'notes' => 'Lunas untuk periode berjalan.',
    ]);

    SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 600000,
        'payment_method' => 'qris',
        'reference_number' => 'QRIS-WALI-PRINT',
        'note' => 'Pembayaran penuh',
        'paid_at' => now()->subDay(),
    ]);

    $response = $this
        ->actingAs($wali)
        ->get(route('wali-santri.invoices.receipt', $invoice));

    $response->assertOk();
    $response->assertSee('Bukti Pembayaran / Kwitansi');
    $response->assertSee('INV-WALI-PRINT');
    $response->assertSee('Kwitansi SPP Wali');
    $response->assertSee('Hasan Anak Wali');
    $response->assertSee('Asrama Print');
    $response->assertSee('Rp 600.000');
    $response->assertSee('Lunas');
    $response->assertSee('Qris');
    $response->assertSee('QRIS-WALI-PRINT');
    $response->assertSee('Pembayaran penuh');
    $response->assertSee('window.print()', false);
});

test('wali santri can not view invoice detail for unlinked santri', function () {
    $wali = tenantUser('Wali Santri');
    $linkedSantri = Santri::factory()->forTenant($wali->tenant)->create();
    $unlinkedSantri = Santri::factory()->forTenant($wali->tenant)->create();

    $wali->guardianSantris()->attach($linkedSantri->id, [
        'tenant_id' => $wali->tenant_id,
        'relationship' => 'Ayah',
    ]);

    $invoice = SantriInvoice::factory()->forSantri($unlinkedSantri)->create([
        'title' => 'Tagihan Santri Lain',
        'amount' => 250000,
    ]);

    $this
        ->actingAs($wali)
        ->get(route('wali-santri.invoices.show', $invoice))
        ->assertNotFound();
});

test('wali santri can not print receipt for unlinked santri invoice', function () {
    $wali = tenantUser('Wali Santri');
    $linkedSantri = Santri::factory()->forTenant($wali->tenant)->create();
    $unlinkedSantri = Santri::factory()->forTenant($wali->tenant)->create();

    $wali->guardianSantris()->attach($linkedSantri->id, [
        'tenant_id' => $wali->tenant_id,
        'relationship' => 'Ayah',
    ]);

    $invoice = SantriInvoice::factory()->forSantri($unlinkedSantri)->create([
        'title' => 'Kwitansi Santri Lain',
        'amount' => 250000,
    ]);

    $this
        ->actingAs($wali)
        ->get(route('wali-santri.invoices.receipt', $invoice))
        ->assertNotFound();
});

test('admin can attach santri to wali account from user detail', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view user details', 'update users']);
    $wali = User::factory()->forTenant($admin->tenant)->create([
        'name' => 'Wali Portal',
    ]);
    $wali->assignRole('Wali Santri');
    $santri = Santri::factory()->forTenant($admin->tenant)->create([
        'full_name' => 'Santri Tertaut',
    ]);

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update-guardian-santri', $wali), [
            'relationship' => 'Ibu',
            'santri_ids' => [$santri->id],
        ]);

    $response->assertRedirect(route('admin.users.show', $wali, absolute: false));
    $this->assertDatabaseHas('santri_guardians', [
        'tenant_id' => $admin->tenant_id,
        'user_id' => $wali->id,
        'santri_id' => $santri->id,
        'relationship' => 'Ibu',
    ]);
});

test('admin can not attach wali account to santri from another tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view user details', 'update users']);
    $wali = User::factory()->forTenant($admin->tenant)->create();
    $wali->assignRole('Wali Santri');
    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $otherSantri = Santri::factory()->forTenant($otherTenant)->create();

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.users.show', $wali))
        ->patch(route('admin.users.update-guardian-santri', $wali), [
            'relationship' => 'Wali',
            'santri_ids' => [$otherSantri->id],
        ]);

    $response->assertRedirect(route('admin.users.show', $wali, absolute: false));
    $response->assertSessionHasErrors('santri_ids', null, 'guardianSantri');
    $this->assertDatabaseMissing('santri_guardians', [
        'user_id' => $wali->id,
        'santri_id' => $otherSantri->id,
    ]);
});
