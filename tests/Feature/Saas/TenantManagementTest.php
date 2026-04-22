<?php

use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\TenantBillingNote;
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

test('superadmin can filter tenants by search and status', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    Tenant::factory()->create([
        'name' => 'Pondok Filter Trial',
        'slug' => 'pondok-filter-trial',
        'subscription_status' => Tenant::SUBSCRIPTION_TRIAL,
    ]);

    Tenant::factory()->activeSubscription()->create([
        'name' => 'Pondok Filter Active',
        'slug' => 'pondok-filter-active',
    ]);

    $response = $this
        ->actingAs($superadmin)
        ->get(route('saas.tenants.index', [
            'search' => 'Filter Active',
            'status' => 'active',
        ]));

    $response->assertOk();
    $response->assertSee('Pondok Filter Active');
    $response->assertDontSee('Pondok Filter Trial');
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
    expect($owner->password_change_required)->toBeTrue();

    $response->assertRedirect(route('saas.tenants.show', $tenant, absolute: false));
});

test('tenant creation writes platform activity logs', function () {
    Notification::fake();

    $superadmin = User::factory()->create(['name' => 'Platform Logger']);
    $superadmin->assignRole('Superadmin');

    $this
        ->actingAs($superadmin)
        ->post(route('saas.tenants.store'), [
            'name' => 'Pondok Log Tenant',
            'slug' => 'pondok-log-tenant',
            'create_owner_account' => '1',
            'owner_name' => 'Owner Log',
            'owner_username' => 'ownerlog',
            'owner_email' => 'ownerlog@example.com',
            'owner_password' => 'password123',
            'owner_password_confirmation' => 'password123',
        ])
        ->assertRedirect();

    expect(ActivityLog::query()->where('action', 'tenant_created')->exists())->toBeTrue();
    expect(ActivityLog::query()->where('action', 'tenant_owner_created')->exists())->toBeTrue();
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
    expect(ActivityLog::query()->where('action', 'subscription_updated')->exists())->toBeTrue();
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

test('superadmin can filter subscription history records', function () {
    $superadmin = User::factory()->create(['name' => 'History Admin']);
    $superadmin->assignRole('Superadmin');

    $tenantA = Tenant::factory()->create(['name' => 'Pondok Histori A']);
    $tenantB = Tenant::factory()->create(['name' => 'Pondok Histori B']);

    TenantSubscriptionHistory::query()->create([
        'tenant_id' => $tenantA->id,
        'action' => 'activate_subscription',
        'period_starts_at' => now(),
        'period_ends_at' => now()->addMonth(),
        'admin_note' => 'Pembayaran paket bulanan diterima.',
        'changed_by' => $superadmin->id,
    ]);

    TenantSubscriptionHistory::query()->create([
        'tenant_id' => $tenantB->id,
        'action' => 'mark_grace',
        'period_starts_at' => now(),
        'period_ends_at' => now()->addDays(5),
        'admin_note' => 'Menunggu pembayaran susulan.',
        'changed_by' => $superadmin->id,
    ]);

    $response = $this
        ->actingAs($superadmin)
        ->get(route('saas.subscription-histories.index', [
            'tenant_id' => $tenantA->id,
            'action' => 'activate_subscription',
            'search' => 'bulanan',
        ]));

    $response->assertOk();
    $response->assertSee('Pondok Histori A');
    $response->assertSee('Pembayaran paket bulanan diterima.');
    $response->assertDontSee('Menunggu pembayaran susulan.');
});

test('superadmin can view billing notes page', function () {
    $superadmin = User::factory()->create(['name' => 'Billing Admin']);
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->create([
        'name' => 'Pondok Billing',
        'slug' => 'pondok-billing',
    ]);

    TenantBillingNote::query()->create([
        'tenant_id' => $tenant->id,
        'paid_at' => now(),
        'amount' => 450000,
        'payment_method' => 'transfer bank',
        'period_starts_at' => now()->toDateString(),
        'period_ends_at' => now()->addMonths(3)->toDateString(),
        'admin_note' => 'Pembayaran triwulan pertama.',
        'recorded_by' => $superadmin->id,
    ]);

    $response = $this
        ->actingAs($superadmin)
        ->get(route('saas.billing-notes.index'));

    $response->assertOk();
    $response->assertSee('Billing Notes');
    $response->assertSee('Pondok Billing');
    $response->assertSee('Pembayaran triwulan pertama.');
    $response->assertSee('Billing Admin');
});

test('superadmin can filter billing notes', function () {
    $superadmin = User::factory()->create(['name' => 'Billing Filter']);
    $superadmin->assignRole('Superadmin');

    $tenantA = Tenant::factory()->create(['name' => 'Pondok Billing A']);
    $tenantB = Tenant::factory()->create(['name' => 'Pondok Billing B']);

    TenantBillingNote::query()->create([
        'tenant_id' => $tenantA->id,
        'paid_at' => now(),
        'amount' => 500000,
        'payment_method' => 'qris',
        'period_starts_at' => now()->toDateString(),
        'period_ends_at' => now()->addMonth()->toDateString(),
        'admin_note' => 'Pembayaran QRIS tenant A.',
        'recorded_by' => $superadmin->id,
    ]);

    TenantBillingNote::query()->create([
        'tenant_id' => $tenantB->id,
        'paid_at' => now(),
        'amount' => 700000,
        'payment_method' => 'cash',
        'period_starts_at' => now()->toDateString(),
        'period_ends_at' => now()->addMonth()->toDateString(),
        'admin_note' => 'Pembayaran cash tenant B.',
        'recorded_by' => $superadmin->id,
    ]);

    $response = $this
        ->actingAs($superadmin)
        ->get(route('saas.billing-notes.index', [
            'tenant_id' => $tenantA->id,
            'payment_method' => 'qris',
            'search' => 'tenant A',
        ]));

    $response->assertOk();
    $response->assertSee('Pondok Billing A');
    $response->assertSee('Pembayaran QRIS tenant A.');
    $response->assertDontSee('Pembayaran cash tenant B.');
});

test('superadmin can store billing notes for a tenant', function () {
    $superadmin = User::factory()->create(['name' => 'Billing Recorder']);
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->create();

    $response = $this
        ->actingAs($superadmin)
        ->post(route('saas.billing-notes.store'), [
            'tenant_id' => $tenant->id,
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'amount' => '600000',
            'payment_method' => 'qris',
            'period_starts_at' => now()->toDateString(),
            'period_ends_at' => now()->addMonths(6)->toDateString(),
            'admin_note' => 'Pembayaran paket 6 bulan via QRIS.',
        ]);

    $response->assertRedirect(route('saas.billing-notes.index', absolute: false));

    $billingNote = TenantBillingNote::query()->latest()->first();

    expect($billingNote)->not->toBeNull();
    expect($billingNote->tenant_id)->toBe($tenant->id);
    expect((float) $billingNote->amount)->toBe(600000.0);
    expect($billingNote->payment_method)->toBe('qris');
    expect($billingNote->admin_note)->toBe('Pembayaran paket 6 bulan via QRIS.');
    expect($billingNote->recorded_by)->toBe($superadmin->id);
    expect(ActivityLog::query()->where('action', 'billing_note_created')->exists())->toBeTrue();
});

test('non superadmin can not access tenant management page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->get(route('saas.tenants.index'));

    $response->assertForbidden();
});

test('non superadmin can not view tenant detail page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $tenant = Tenant::factory()->create();

    $response = $this
        ->actingAs($admin)
        ->get(route('saas.tenants.show', $tenant));

    $response->assertForbidden();
});

test('non superadmin can not create tenant', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->post(route('saas.tenants.store'), [
            'name' => 'Pondok Tanpa Izin',
        ]);

    $response->assertForbidden();
});

test('non superadmin can not update tenant subscription', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $tenant = Tenant::factory()->create();

    $response = $this
        ->actingAs($admin)
        ->patch(route('saas.tenants.update-subscription', $tenant), [
            'action' => 'mark_expired',
        ]);

    $response->assertForbidden();
});

test('non superadmin can not access subscription histories page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->get(route('saas.subscription-histories.index'));

    $response->assertForbidden();
});

test('non superadmin can not access billing notes page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->get(route('saas.billing-notes.index'));

    $response->assertForbidden();
});

test('non superadmin can not store billing notes', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $tenant = Tenant::factory()->create();

    $response = $this
        ->actingAs($admin)
        ->post(route('saas.billing-notes.store'), [
            'tenant_id' => $tenant->id,
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'amount' => '100000',
            'payment_method' => 'cash',
            'period_starts_at' => now()->toDateString(),
            'period_ends_at' => now()->addMonth()->toDateString(),
        ]);

    $response->assertForbidden();
});
