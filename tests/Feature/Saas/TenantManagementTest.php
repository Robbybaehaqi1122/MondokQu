<?php

use App\Models\Tenant;
use App\Models\TenantSubscriptionHistory;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Superadmin', 'web');
    Role::findOrCreate('Admin', 'web');
});

test('superadmin can view the tenant management page', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    Tenant::factory()->create([
        'name' => 'Pondok Nurul Ilmi',
        'slug' => 'pondok-nurul-ilmi',
    ]);

    $response = $this
        ->actingAs($superadmin)
        ->get(route('saas.tenants.index'));

    $response->assertOk();
    $response->assertSee('Tenant Management');
    $response->assertSee('Pondok Nurul Ilmi');
});

test('superadmin can create a tenant with trial status', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    $response = $this
        ->actingAs($superadmin)
        ->post(route('saas.tenants.store'), [
            'name' => 'Pondok Al Hikmah',
            'slug' => 'pondok-al-hikmah',
            'contact_email' => 'info@alhikmah.test',
            'contact_phone_number' => '081234567890',
        ]);

    $tenant = Tenant::query()->where('slug', 'pondok-al-hikmah')->first();

    expect($tenant)->not->toBeNull();
    expect($tenant->subscription_status)->toBe(Tenant::SUBSCRIPTION_TRIAL);
    expect($tenant->trial_ends_at)->not->toBeNull();

    $response->assertRedirect(route('saas.tenants.show', $tenant, absolute: false));
});

test('superadmin can create a tenant together with its owner admin account', function () {
    Notification::fake();

    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    $response = $this
        ->actingAs($superadmin)
        ->post(route('saas.tenants.store'), [
            'name' => 'Pondok As Sunnah',
            'slug' => 'pondok-as-sunnah',
            'contact_email' => 'info@assunnah.test',
            'create_owner_account' => '1',
            'owner_name' => 'Admin Pondok',
            'owner_username' => 'adminpondok',
            'owner_email' => 'adminpondok@example.com',
            'owner_phone_number' => '081234567890',
            'owner_password' => 'password123',
            'owner_password_confirmation' => 'password123',
        ]);

    $response->assertSessionHasNoErrors();
    expect($response->status())->toBe(302);

    $tenant = Tenant::query()
        ->where('name', 'Pondok As Sunnah')
        ->orWhere('slug', 'pondok-as-sunnah')
        ->latest('id')
        ->first();
    $owner = User::query()->where('email', 'adminpondok@example.com')->first();

    expect($tenant)->not->toBeNull();
    expect($owner)->not->toBeNull();
    expect($owner->tenant_id)->toBe($tenant->id);
    expect($tenant->owner_id)->toBe($owner->id);
    expect($owner->hasRole('Admin'))->toBeTrue();

    $response->assertRedirect(route('saas.tenants.show', $tenant, absolute: false));
});

test('superadmin can view tenant detail page', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->create([
        'name' => 'Pondok Darussalam',
    ]);

    $response = $this
        ->actingAs($superadmin)
        ->get(route('saas.tenants.show', $tenant));

    $response->assertOk();
    $response->assertSee('Pondok Darussalam');
    $response->assertSee('Informasi Tenant');
});

test('superadmin can activate subscription for a tenant', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->create();

    $response = $this
        ->actingAs($superadmin)
        ->from(route('saas.tenants.index'))
        ->patch(route('saas.tenants.update-subscription', $tenant), [
            'action' => 'activate_subscription',
            'subscription_duration' => '3_months',
        ]);

    $response->assertRedirect(route('saas.tenants.index', absolute: false));

    $tenant = $tenant->fresh();

    expect($tenant->subscription_status)->toBe(Tenant::SUBSCRIPTION_ACTIVE);
    expect($tenant->subscription_starts_at)->not->toBeNull();
    expect($tenant->subscription_ends_at)->not->toBeNull();
    expect($tenant->subscription_ends_at->greaterThan(now()->addMonths(2)))->toBeTrue();
    expect(TenantSubscriptionHistory::query()->where('tenant_id', $tenant->id)->where('action', 'activate_subscription')->exists())->toBeTrue();
});

test('superadmin can move a tenant to grace period', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->activeSubscription()->create();

    $response = $this
        ->actingAs($superadmin)
        ->from(route('saas.tenants.index'))
        ->patch(route('saas.tenants.update-subscription', $tenant), [
            'action' => 'mark_grace',
            'grace_ends_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
        ]);

    $response->assertRedirect(route('saas.tenants.index', absolute: false));

    $tenant = $tenant->fresh();

    expect($tenant->subscription_status)->toBe(Tenant::SUBSCRIPTION_GRACE);
    expect($tenant->grace_ends_at)->not->toBeNull();
    expect($tenant->grace_ends_at->greaterThan(now()->addDays(9)))->toBeTrue();
});

test('superadmin can mark a tenant as expired', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->onGracePeriod()->create();

    $response = $this
        ->actingAs($superadmin)
        ->from(route('saas.tenants.index'))
        ->patch(route('saas.tenants.update-subscription', $tenant), [
            'action' => 'mark_expired',
        ]);

    $response->assertRedirect(route('saas.tenants.index', absolute: false));

    expect($tenant->fresh()->subscription_status)->toBe(Tenant::SUBSCRIPTION_EXPIRED);
});

test('superadmin can activate trial with custom end date', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->expired()->create();
    $trialEnd = now()->addDays(21);

    $response = $this
        ->actingAs($superadmin)
        ->from(route('saas.tenants.index'))
        ->patch(route('saas.tenants.update-subscription', $tenant), [
            'action' => 'activate_trial',
            'trial_ends_at' => $trialEnd->format('Y-m-d H:i:s'),
        ]);

    $response->assertRedirect(route('saas.tenants.index', absolute: false));

    $tenant = $tenant->fresh();

    expect($tenant->subscription_status)->toBe(Tenant::SUBSCRIPTION_TRIAL);
    expect($tenant->trial_ends_at?->format('Y-m-d H:i'))->toBe($trialEnd->format('Y-m-d H:i'));
});

test('subscription history stores admin note and actor for tenant changes', function () {
    $superadmin = User::factory()->create(['name' => 'Platform Owner']);
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->expired()->create();

    $this
        ->actingAs($superadmin)
        ->from(route('saas.tenants.index'))
        ->patch(route('saas.tenants.update-subscription', $tenant), [
            'action' => 'activate_trial',
            'trial_ends_at' => now()->addDays(14)->format('Y-m-d H:i:s'),
            'admin_note' => 'Trial diaktifkan kembali untuk onboarding tenant baru.',
        ])
        ->assertRedirect(route('saas.tenants.index', absolute: false));

    $history = TenantSubscriptionHistory::query()->where('tenant_id', $tenant->id)->latest()->first();

    expect($history)->not->toBeNull();
    expect($history->action)->toBe('activate_trial');
    expect($history->admin_note)->toBe('Trial diaktifkan kembali untuk onboarding tenant baru.');
    expect($history->changed_by)->toBe($superadmin->id);
    expect($history->period_starts_at)->not->toBeNull();
    expect($history->period_ends_at)->not->toBeNull();
});

test('superadmin can view subscription history page', function () {
    $superadmin = User::factory()->create(['name' => 'Platform Owner']);
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->create([
        'name' => 'Pondok Riwayat',
    ]);

    TenantSubscriptionHistory::query()->create([
        'tenant_id' => $tenant->id,
        'action' => 'mark_grace',
        'period_starts_at' => now(),
        'period_ends_at' => now()->addDays(5),
        'admin_note' => 'Menunggu konfirmasi pembayaran.',
        'changed_by' => $superadmin->id,
    ]);

    $response = $this
        ->actingAs($superadmin)
        ->get(route('saas.subscription-histories.index'));

    $response->assertOk();
    $response->assertSee('Riwayat Subscription');
    $response->assertSee('Menunggu konfirmasi pembayaran.');
    $response->assertSee('Platform Owner');
    $response->assertSee('Pondok Riwayat');
});

test('non superadmin can not access tenant management page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->get(route('saas.tenants.index'));

    $response->assertForbidden();
});
