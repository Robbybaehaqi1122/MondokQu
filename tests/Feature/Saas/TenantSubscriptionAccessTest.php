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
