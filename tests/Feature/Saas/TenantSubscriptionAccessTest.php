<?php

use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Pengurus', 'web');
    Permission::findOrCreate('view santri', 'web');
});

test('tenant user with expired subscription is redirected to the subscription expired page', function () {
    $tenant = Tenant::factory()->expired()->create();
    $user = User::factory()->forTenant($tenant)->create();
    $user->assignRole('Pengurus');
    $user->givePermissionTo('view santri');

    $response = $this
        ->actingAs($user)
        ->get(route('santri.index'));

    $response->assertRedirect(route('subscription.expired', absolute: false));
});

test('expired subscription page shows actionable tenant guidance', function () {
    $tenant = Tenant::factory()->expired()->create([
        'name' => 'Pondok Lifecycle',
    ]);
    $user = User::factory()->forTenant($tenant)->create();
    $user->assignRole('Pengurus');

    $response = $this
        ->actingAs($user)
        ->get(route('subscription.expired'));

    $response->assertOk();
    $response->assertSee('Status Akses Tenant');
    $response->assertSee('Pondok Lifecycle');
    $response->assertSee('Subscription Expired');
    $response->assertSee('Hubungi admin platform untuk perpanjangan paket atau aktivasi ulang tenant.');
});

test('tenant user on trial can still access the application', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant)->create();
    $user->assignRole('Pengurus');
    $user->givePermissionTo('view santri');

    $response = $this
        ->actingAs($user)
        ->get(route('santri.index'));

    $response->assertOk();
});

test('tenant queued for deletion is blocked with deletion guidance', function () {
    $tenant = Tenant::factory()->activeSubscription()->create([
        'name' => 'Pondok Dalam Penghapusan',
        'subscription_status' => Tenant::SUBSCRIPTION_DELETING,
    ]);
    $user = User::factory()->forTenant($tenant)->create();
    $user->assignRole('Pengurus');
    $user->givePermissionTo('view santri');

    $this
        ->actingAs($user)
        ->get(route('santri.index'))
        ->assertRedirect(route('subscription.expired', absolute: false));

    $response = $this
        ->actingAs($user)
        ->get(route('subscription.expired'));

    $response->assertOk();
    $response->assertSee('Dalam Penghapusan');
    $response->assertSee('Akses operasional diblokir selama proses penghapusan data berjalan di background queue.');
});

test('non superadmin user without tenant is redirected away from tenant operations', function () {
    $user = User::factory()->create();
    $user->assignRole('Pengurus');
    $user->givePermissionTo('view santri');

    $response = $this
        ->actingAs($user)
        ->get(route('santri.index'));

    $response->assertRedirect(route('subscription.expired', absolute: false));
    $response->assertSessionHas('error');
});

test('expired subscription page explains when user is not linked to a tenant', function () {
    $user = User::factory()->create();
    $user->assignRole('Pengurus');

    $response = $this
        ->actingAs($user)
        ->get(route('subscription.expired'));

    $response->assertOk();
    $response->assertSee('Tenant Belum Terhubung');
    $response->assertSee('Hubungi admin platform agar akun Anda ditautkan ke tenant yang benar');
});

test('expired subscription page explains expired trial tenants clearly', function () {
    $tenant = Tenant::factory()->expired()->create([
        'name' => 'Pondok Trial Selesai',
        'trial_ends_at' => now()->subDay(),
        'subscription_ends_at' => null,
        'grace_ends_at' => null,
    ]);
    $user = User::factory()->forTenant($tenant)->create();
    $user->assignRole('Pengurus');

    $response = $this
        ->actingAs($user)
        ->get(route('subscription.expired'));

    $response->assertOk();
    $response->assertSee('Pondok Trial Selesai');
    $response->assertSee('Trial terakhir tercatat sampai');
});
