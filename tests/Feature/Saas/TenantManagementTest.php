<?php

use App\Models\ActivityLog;
use App\Models\Santri;
use App\Models\Tenant;
use App\Models\TenantBillingNote;
use App\Models\TenantSubscriptionHistory;
use App\Models\User;
use App\Modules\Saas\Actions\DeleteTenantAction;
use Illuminate\Support\Facades\DB;
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

test('superadmin can filter billing notes by tenant status and date ranges', function () {
    $superadmin = User::factory()->create(['name' => 'Billing Date Filter']);
    $superadmin->assignRole('Superadmin');

    $activeTenant = Tenant::factory()->activeSubscription()->create([
        'name' => 'Pondok Billing Aktif',
    ]);
    $expiredTenant = Tenant::factory()->expired()->create([
        'name' => 'Pondok Billing Expired',
    ]);

    TenantBillingNote::query()->create([
        'tenant_id' => $activeTenant->id,
        'paid_at' => '2026-05-02 10:00:00',
        'amount' => 500000,
        'payment_method' => 'transfer bank',
        'period_starts_at' => '2026-05-01',
        'period_ends_at' => '2026-05-31',
        'admin_note' => 'Masuk filter billing aktif.',
        'recorded_by' => $superadmin->id,
    ]);

    TenantBillingNote::query()->create([
        'tenant_id' => $expiredTenant->id,
        'paid_at' => '2026-04-15 10:00:00',
        'amount' => 700000,
        'payment_method' => 'cash',
        'period_starts_at' => '2026-04-01',
        'period_ends_at' => '2026-04-30',
        'admin_note' => 'Di luar filter billing aktif.',
        'recorded_by' => $superadmin->id,
    ]);

    $response = $this
        ->actingAs($superadmin)
        ->get(route('saas.billing-notes.index', [
            'tenant_status' => Tenant::SUBSCRIPTION_ACTIVE,
            'paid_from' => '2026-05-01',
            'paid_to' => '2026-05-03',
            'period_from' => '2026-05-01',
            'period_to' => '2026-05-31',
        ]));

    $response->assertOk();
    $response->assertSee('Masuk filter billing aktif.');
    $response->assertDontSee('Di luar filter billing aktif.');

    $summary = $response->viewData('summary');

    expect($summary['total_notes'])->toBe(1);
    expect((float) $summary['total_amount'])->toBe(500000.0);
    expect($summary['status_counts'][Tenant::SUBSCRIPTION_ACTIVE])->toBe(1);
    expect($summary['status_counts'][Tenant::SUBSCRIPTION_EXPIRED])->toBe(0);
});

test('superadmin can store billing notes for a tenant', function () {
    $superadmin = User::factory()->create(['name' => 'Billing Recorder']);
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->expired()->create();

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
    expect($tenant->fresh()->subscription_status)->toBe(Tenant::SUBSCRIPTION_EXPIRED);
    expect(TenantSubscriptionHistory::query()->where('tenant_id', $tenant->id)->exists())->toBeFalse();
    expect(ActivityLog::query()->where('action', 'billing_note_created')->exists())->toBeTrue();
});

test('superadmin can store billing note and activate tenant subscription', function () {
    $superadmin = User::factory()->create(['name' => 'Billing Activator']);
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->expired()->create();
    $periodStart = now()->toDateString();
    $periodEnd = now()->addMonths(3)->toDateString();

    $response = $this
        ->actingAs($superadmin)
        ->post(route('saas.billing-notes.store'), [
            'tenant_id' => $tenant->id,
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'amount' => '450000',
            'payment_method' => 'transfer bank',
            'period_starts_at' => $periodStart,
            'period_ends_at' => $periodEnd,
            'apply_subscription' => '1',
            'admin_note' => 'Pembayaran paket 3 bulan via transfer.',
        ]);

    $response->assertRedirect(route('saas.billing-notes.index', absolute: false));
    $response->assertSessionHas('success', 'Billing note berhasil disimpan dan subscription tenant sudah diperbarui.');

    $tenant = $tenant->fresh();
    $billingNote = TenantBillingNote::query()->latest()->first();
    $history = TenantSubscriptionHistory::query()->where('tenant_id', $tenant->id)->latest()->first();

    expect($billingNote)->not->toBeNull();
    expect($billingNote->tenant_id)->toBe($tenant->id);
    expect($tenant->subscription_status)->toBe(Tenant::SUBSCRIPTION_ACTIVE);
    expect($tenant->subscription_starts_at?->toDateString())->toBe($periodStart);
    expect($tenant->subscription_ends_at?->toDateString())->toBe($periodEnd);
    expect($tenant->hasAccess())->toBeTrue();
    expect($history)->not->toBeNull();
    expect($history->action)->toBe('activate_subscription');
    expect($history->period_starts_at?->toDateString())->toBe($periodStart);
    expect($history->period_ends_at?->toDateString())->toBe($periodEnd);
    expect($history->admin_note)->toContain('billing note #'.$billingNote->id);
    expect(ActivityLog::query()->where('action', 'billing_note_created')->exists())->toBeTrue();
    expect(ActivityLog::query()->where('action', 'subscription_updated')->where('description', 'Status subscription tenant diperbarui dari billing note.')->exists())->toBeTrue();
});

test('billing note can not activate subscription with an expired billing period', function () {
    $superadmin = User::factory()->create(['name' => 'Billing Validator']);
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->expired()->create();

    $response = $this
        ->actingAs($superadmin)
        ->from(route('saas.billing-notes.index'))
        ->post(route('saas.billing-notes.store'), [
            'tenant_id' => $tenant->id,
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'amount' => '150000',
            'payment_method' => 'cash',
            'period_starts_at' => now()->subMonths(2)->toDateString(),
            'period_ends_at' => now()->subMonth()->toDateString(),
            'apply_subscription' => '1',
        ]);

    $response->assertRedirect(route('saas.billing-notes.index', absolute: false));
    $response->assertSessionHasErrors(['period_ends_at'], null, 'billingNote');
    expect(TenantBillingNote::query()->where('tenant_id', $tenant->id)->exists())->toBeFalse();
    expect($tenant->fresh()->subscription_status)->toBe(Tenant::SUBSCRIPTION_EXPIRED);
});

test('superadmin can permanently delete a tenant with exact slug confirmation', function () {
    $superadmin = User::factory()->create(['name' => 'Tenant Deleter']);
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->activeSubscription()->create([
        'name' => 'Pondok Demo Delete',
        'slug' => 'pondok-demo-delete',
    ]);
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    $tenantUser = User::factory()->forTenant($tenant)->create([
        'email' => 'tenant-user-delete@example.com',
    ]);
    $tenantUser->assignRole('Admin');

    $otherUser = User::factory()->forTenant($otherTenant)->create();
    $otherUser->assignRole('Admin');

    $santri = Santri::factory()->forTenant($tenant)->create([
        'created_by' => $tenantUser->id,
    ]);
    $otherSantri = Santri::factory()->forTenant($otherTenant)->create();

    ActivityLog::query()->create([
        'tenant_id' => $tenant->id,
        'actor_id' => $tenantUser->id,
        'actor_name' => $tenantUser->name,
        'action' => 'tenant_user_activity',
        'description' => 'Aktivitas tenant yang akan dihapus.',
        'target_name' => 'Tenant delete test',
        'ip_address' => '127.0.0.1',
    ]);

    TenantBillingNote::query()->create([
        'tenant_id' => $tenant->id,
        'paid_at' => now(),
        'amount' => 500000,
        'payment_method' => 'cash',
        'period_starts_at' => now()->toDateString(),
        'period_ends_at' => now()->addMonth()->toDateString(),
        'recorded_by' => $superadmin->id,
    ]);

    TenantSubscriptionHistory::query()->create([
        'tenant_id' => $tenant->id,
        'action' => 'activate_subscription',
        'period_starts_at' => now(),
        'period_ends_at' => now()->addMonth(),
        'changed_by' => $superadmin->id,
    ]);

    DB::table('sessions')->insert([
        'id' => 'tenant-user-session',
        'user_id' => $tenantUser->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);
    DB::table('password_reset_tokens')->insert([
        'email' => $tenantUser->email,
        'token' => 'delete-token',
        'created_at' => now(),
    ]);

    $response = $this
        ->actingAs($superadmin)
        ->from(route('saas.tenants.index'))
        ->delete(route('saas.tenants.destroy', $tenant), [
            'delete_tenant_id' => $tenant->id,
            'tenant_delete_confirmation' => $tenant->slug,
            'delete_reason' => 'Tenant demo tidak diperlukan lagi.',
        ]);

    $response->assertRedirect(route('saas.tenants.index', absolute: false));
    $response->assertSessionHas('success');

    expect(Tenant::query()->whereKey($tenant->id)->exists())->toBeFalse();
    expect(User::query()->whereKey($tenantUser->id)->exists())->toBeFalse();
    expect(Santri::query()->whereKey($santri->id)->exists())->toBeFalse();
    expect(ActivityLog::query()->where('tenant_id', $tenant->id)->exists())->toBeFalse();
    expect(TenantBillingNote::query()->where('tenant_id', $tenant->id)->exists())->toBeFalse();
    expect(TenantSubscriptionHistory::query()->where('tenant_id', $tenant->id)->exists())->toBeFalse();
    expect(DB::table('sessions')->where('user_id', $tenantUser->id)->exists())->toBeFalse();
    expect(DB::table('password_reset_tokens')->where('email', $tenantUser->email)->exists())->toBeFalse();
    expect(ActivityLog::query()->where('action', 'tenant_deleted_permanently')->exists())->toBeTrue();

    expect(Tenant::query()->whereKey($otherTenant->id)->exists())->toBeTrue();
    expect(User::query()->whereKey($otherUser->id)->exists())->toBeTrue();
    expect(Santri::query()->whereKey($otherSantri->id)->exists())->toBeTrue();
});

test('delete tenant action can run without an authenticated request context', function () {
    $superadmin = User::factory()->create(['name' => 'CLI Deleter']);
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->activeSubscription()->create([
        'name' => 'Pondok CLI Delete',
        'slug' => 'pondok-cli-delete',
    ]);
    $tenantUser = User::factory()->forTenant($tenant)->create();
    $santri = Santri::factory()->forTenant($tenant)->create([
        'created_by' => $tenantUser->id,
    ]);

    ActivityLog::query()->create([
        'tenant_id' => $tenant->id,
        'actor_id' => $tenantUser->id,
        'actor_name' => $tenantUser->name,
        'action' => 'tenant_user_activity',
        'description' => 'Aktivitas tenant dari action delete.',
        'target_name' => 'Tenant action delete test',
        'ip_address' => '127.0.0.1',
    ]);

    $result = app(DeleteTenantAction::class)->handle(
        tenant: $tenant,
        actor: $superadmin,
        deleteReason: 'Dihapus dari command line.',
        ipAddress: '127.0.0.1',
        userAgent: 'Pest CLI'
    );

    expect($result['deleted'])->toBeTrue();
    expect(Tenant::query()->whereKey($tenant->id)->exists())->toBeFalse();
    expect(User::query()->whereKey($tenantUser->id)->exists())->toBeFalse();
    expect(Santri::query()->withoutTenantScope()->whereKey($santri->id)->exists())->toBeFalse();
    expect(ActivityLog::query()->withoutTenantScope()->where('tenant_id', $tenant->id)->exists())->toBeFalse();
    expect(ActivityLog::query()->withoutTenantScope()->where('action', 'tenant_deleted_permanently')->exists())->toBeTrue();
});

test('tenant permanent delete requires exact slug confirmation', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->create([
        'slug' => 'pondok-konfirmasi-delete',
    ]);

    $response = $this
        ->actingAs($superadmin)
        ->from(route('saas.tenants.index'))
        ->delete(route('saas.tenants.destroy', $tenant), [
            'delete_tenant_id' => $tenant->id,
            'tenant_delete_confirmation' => 'slug-salah',
        ]);

    $response->assertRedirect(route('saas.tenants.index', absolute: false));
    $response->assertSessionHasErrors(['tenant_delete_confirmation'], null, 'deleteTenant');
    expect($tenant->fresh())->not->toBeNull();
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

test('non superadmin can not permanently delete tenant', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $tenant = Tenant::factory()->create([
        'slug' => 'pondok-tidak-boleh-delete',
    ]);

    $response = $this
        ->actingAs($admin)
        ->delete(route('saas.tenants.destroy', $tenant), [
            'delete_tenant_id' => $tenant->id,
            'tenant_delete_confirmation' => $tenant->slug,
        ]);

    $response->assertForbidden();
    expect($tenant->fresh())->not->toBeNull();
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
