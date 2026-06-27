<?php

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Saas\Actions\DeleteTenantAction;

test('returns error when actor belongs to the tenant being deleted', function (): void {
    $tenant = Tenant::factory()->create();
    $actor = User::factory()->create(['tenant_id' => $tenant->id]);

    $result = app(DeleteTenantAction::class)->handle($tenant, $actor);

    expect($result['deleted'])->toBeFalse();
    expect($result['message'])->toContain('tidak bisa dihapus permanen');
    expect($result['snapshot'])->toBeNull();
});

test('returns success result structure when deleting a tenant', function (): void {
    $tenant = Tenant::factory()->activeSubscription()->create();

    $result = app(DeleteTenantAction::class)->handle($tenant, null);

    expect($result['deleted'])->toBeTrue();
    expect($result['message'])->toContain('berhasil dihapus permanen');
    expect($result['snapshot'])->toBeArray();
    expect($result['snapshot'])->toHaveKeys([
        'tenant_id', 'tenant_name', 'tenant_slug', 'subscription_status',
        'delete_reason',
    ]);
    expect($result['snapshot']['tenant_id'])->toBe($tenant->id);
});

test('includes delete reason in snapshot', function (): void {
    $tenant = Tenant::factory()->activeSubscription()->create();

    $result = app(DeleteTenantAction::class)->handle($tenant, null, 'Testing delete');

    expect($result['snapshot']['delete_reason'])->toBe('Testing delete');
});
