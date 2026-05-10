<?php

use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Superadmin', 'web');
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');
});

test('superadmin can impersonate a tenant user and return to superadmin account', function () {
    $superadmin = User::factory()->create(['name' => 'Platform Support']);
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->activeSubscription()->create([
        'name' => 'Pondok Support',
    ]);
    $tenantAdmin = User::factory()->forTenant($tenant)->create([
        'name' => 'Admin Tenant',
    ]);
    $tenantAdmin->assignRole('Admin');

    $this
        ->actingAs($superadmin)
        ->post(route('saas.tenants.users.impersonate', [$tenant, $tenantAdmin]))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($tenantAdmin);
    $this->assertTrue(session()->has('impersonation.impersonator_id'));

    $this
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Mode Impersonation Aktif')
        ->assertSee('Pondok Support')
        ->assertSee('Kembali ke Superadmin');

    expect(ActivityLog::query()
        ->withoutTenantScope()
        ->where('action', 'tenant_impersonation_started')
        ->exists())->toBeTrue();

    $this
        ->post(route('impersonation.stop'))
        ->assertRedirect(route('saas.tenants.show', $tenant, absolute: false));

    $this->assertAuthenticatedAs($superadmin);
    $this->assertFalse(session()->has('impersonation.impersonator_id'));
    expect(ActivityLog::query()
        ->withoutTenantScope()
        ->where('action', 'tenant_impersonation_stopped')
        ->exists())->toBeTrue();
});

test('non superadmin can not start tenant impersonation', function () {
    $tenant = Tenant::factory()->activeSubscription()->create();
    $admin = User::factory()->forTenant($tenant)->create();
    $admin->assignRole('Admin');
    $targetUser = User::factory()->forTenant($tenant)->create();
    $targetUser->assignRole('Pengurus');

    $this
        ->actingAs($admin)
        ->post(route('saas.tenants.users.impersonate', [$tenant, $targetUser]))
        ->assertForbidden();

    $this->assertAuthenticatedAs($admin);
    $this->assertFalse(session()->has('impersonation.impersonator_id'));
});

test('superadmin can not impersonate a user from another tenant', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->activeSubscription()->create();
    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $otherTenantUser = User::factory()->forTenant($otherTenant)->create();
    $otherTenantUser->assignRole('Admin');

    $this
        ->actingAs($superadmin)
        ->from(route('saas.tenants.show', $tenant))
        ->post(route('saas.tenants.users.impersonate', [$tenant, $otherTenantUser]))
        ->assertRedirect(route('saas.tenants.show', $tenant, absolute: false))
        ->assertSessionHas('error', 'User tenant yang dipilih tidak valid untuk impersonation.');

    $this->assertAuthenticatedAs($superadmin);
    $this->assertFalse(session()->has('impersonation.impersonator_id'));
});
