<?php

use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\TenantSubscriptionHistory;
use Illuminate\Support\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

test('subscription lifecycle command expires overdue trials', function () {
    Carbon::setTestNow('2026-05-10 01:00:00');

    $tenant = Tenant::factory()->create([
        'subscription_status' => Tenant::SUBSCRIPTION_TRIAL,
        'trial_ends_at' => now()->subMinute(),
    ]);

    $this->artisan('saas:expire-subscriptions')
        ->assertSuccessful();

    $tenant = $tenant->fresh();

    expect($tenant->subscription_status)->toBe(Tenant::SUBSCRIPTION_EXPIRED);
    expect(TenantSubscriptionHistory::query()
        ->where('tenant_id', $tenant->id)
        ->where('action', 'mark_expired')
        ->exists())->toBeTrue();
    expect(ActivityLog::query()
        ->withoutTenantScope()
        ->where('action', 'subscription_auto_expired')
        ->where('properties->reason', 'trial_expired')
        ->exists())->toBeTrue();
});

test('subscription lifecycle command moves overdue active subscriptions to grace period', function () {
    config(['saas.grace_days' => 5]);
    Carbon::setTestNow('2026-05-10 01:00:00');

    $tenant = Tenant::factory()->activeSubscription()->create([
        'subscription_ends_at' => now()->subMinute(),
        'grace_ends_at' => null,
    ]);

    $this->artisan('saas:expire-subscriptions')
        ->assertSuccessful();

    $tenant = $tenant->fresh();

    expect($tenant->subscription_status)->toBe(Tenant::SUBSCRIPTION_GRACE);
    expect($tenant->grace_ends_at?->format('Y-m-d H:i:s'))->toBe(now()->addDays(5)->format('Y-m-d H:i:s'));
    expect(TenantSubscriptionHistory::query()
        ->where('tenant_id', $tenant->id)
        ->where('action', 'mark_grace')
        ->exists())->toBeTrue();
    expect(ActivityLog::query()
        ->withoutTenantScope()
        ->where('action', 'subscription_auto_grace_started')
        ->exists())->toBeTrue();
});

test('subscription lifecycle command expires overdue grace periods', function () {
    Carbon::setTestNow('2026-05-10 01:00:00');

    $tenant = Tenant::factory()->onGracePeriod()->create([
        'grace_ends_at' => now()->subMinute(),
    ]);

    $this->artisan('saas:expire-subscriptions')
        ->assertSuccessful();

    $tenant = $tenant->fresh();

    expect($tenant->subscription_status)->toBe(Tenant::SUBSCRIPTION_EXPIRED);
    expect(TenantSubscriptionHistory::query()
        ->where('tenant_id', $tenant->id)
        ->where('action', 'mark_expired')
        ->exists())->toBeTrue();
    expect(ActivityLog::query()
        ->withoutTenantScope()
        ->where('action', 'subscription_auto_expired')
        ->where('properties->reason', 'grace_period_expired')
        ->exists())->toBeTrue();
});

test('subscription lifecycle command keeps tenants with future access unchanged', function () {
    Carbon::setTestNow('2026-05-10 01:00:00');

    $trialTenant = Tenant::factory()->create([
        'subscription_status' => Tenant::SUBSCRIPTION_TRIAL,
        'trial_ends_at' => now()->addDay(),
    ]);
    $activeTenant = Tenant::factory()->activeSubscription()->create([
        'subscription_ends_at' => now()->addDay(),
    ]);
    $graceTenant = Tenant::factory()->onGracePeriod()->create([
        'grace_ends_at' => now()->addDay(),
    ]);

    $this->artisan('saas:expire-subscriptions')
        ->assertSuccessful();

    expect($trialTenant->fresh()->subscription_status)->toBe(Tenant::SUBSCRIPTION_TRIAL);
    expect($activeTenant->fresh()->subscription_status)->toBe(Tenant::SUBSCRIPTION_ACTIVE);
    expect($graceTenant->fresh()->subscription_status)->toBe(Tenant::SUBSCRIPTION_GRACE);
    expect(TenantSubscriptionHistory::query()->count())->toBe(0);
    expect(ActivityLog::query()->withoutTenantScope()->count())->toBe(0);
});
