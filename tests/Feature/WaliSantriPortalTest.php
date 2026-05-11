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
