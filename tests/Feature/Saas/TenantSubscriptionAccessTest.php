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
