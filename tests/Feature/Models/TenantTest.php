<?php

use App\Models\ActivityLog;
use App\Models\AttendanceActivity;
use App\Models\DataExport;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomTransfer;
use App\Models\Santri;
use App\Models\SantriGuardian;
use App\Models\SantriInvoice;
use App\Models\Tenant;
use App\Models\TenantBillingNote;
use App\Models\TenantSubscriptionHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('Superadmin', 'web');
    Role::findOrCreate('Admin', 'web');

    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $this->actingAs($superadmin);

    $this->tenant = Tenant::factory()->create([
        'name' => 'Pondok Test',
        'slug' => 'pondok-test',
        'subscription_status' => 'trial',
        'trial_ends_at' => now()->addDays(10),
    ]);
});

it('has correct factory', function () {
    expect($this->tenant)->toBeInstanceOf(Tenant::class);
    expect($this->tenant->name)->toBe('Pondok Test');
});

it('belongs to owner', function () {
    $owner = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->tenant->update(['owner_id' => $owner->id]);
    $this->tenant->refresh();

    expect($this->tenant->owner)->toBeInstanceOf(User::class);
    expect($this->tenant->owner->id)->toBe($owner->id);
});

it('has many users', function () {
    User::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

    expect($this->tenant->users)->toHaveCount(3);
    expect($this->tenant->users->first())->toBeInstanceOf(User::class);
});

it('has many santris', function () {
    Santri::factory()->count(2)->create(['tenant_id' => $this->tenant->id]);

    expect($this->tenant->santris)->toHaveCount(2);
});

it('has many rooms', function () {
    Room::factory()->count(2)->create(['tenant_id' => $this->tenant->id]);

    expect($this->tenant->rooms)->toHaveCount(2);
});

it('has many attendance activities', function () {
    AttendanceActivity::factory()->count(2)->create(['tenant_id' => $this->tenant->id]);

    expect($this->tenant->attendanceActivities)->toHaveCount(2);
});

it('has many leave requests', function () {
    $santri = Santri::factory()->create(['tenant_id' => $this->tenant->id]);
    LeaveRequest::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $santri->id,
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addDay()->format('Y-m-d'),
        'reason' => 'Test',
        'status' => 'pending',
    ]);
    LeaveRequest::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $santri->id,
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addDay()->format('Y-m-d'),
        'reason' => 'Test 2',
        'status' => 'pending',
    ]);

    expect($this->tenant->leaveRequests)->toHaveCount(2);
});

it('has many santri invoices', function () {
    $santri = Santri::factory()->create(['tenant_id' => $this->tenant->id]);
    SantriInvoice::factory()->count(2)->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $santri->id,
    ]);

    expect($this->tenant->santriInvoices)->toHaveCount(2);
});

it('has many data exports', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    DataExport::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $user->id,
        'type' => 'santri',
        'status' => 'queued',
        'name' => 'Test export',
        'filename' => 'test.csv',
        'format' => 'csv',
    ]);
    DataExport::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $user->id,
        'type' => 'santri_invoices',
        'status' => 'queued',
        'name' => 'Test export 2',
        'filename' => 'test2.csv',
        'format' => 'csv',
    ]);

    expect($this->tenant->dataExports)->toHaveCount(2);
});

it('has cast attributes', function () {
    expect($this->tenant->trial_ends_at)->toBeInstanceOf(Carbon::class);
    $this->tenant->update(['settings' => ['theme' => 'light']]);
    $this->tenant->refresh();
    expect($this->tenant->settings)->toBeArray();
    expect($this->tenant->settings['theme'])->toBe('light');
});

it('detects trial period', function () {
    expect($this->tenant->onTrial())->toBeTrue();
    expect($this->tenant->hasAccess())->toBeTrue();

    $expiredTrial = Tenant::factory()->create([
        'subscription_status' => 'trial',
        'trial_ends_at' => now()->subDay(),
    ]);
    expect($expiredTrial->onTrial())->toBeFalse();
    expect($expiredTrial->hasAccess())->toBeFalse();
});

it('detects active subscription', function () {
    $active = Tenant::factory()->activeSubscription()->create();
    expect($active->hasPaidSubscription())->toBeTrue();
    expect($active->hasAccess())->toBeTrue();
});

it('detects grace period', function () {
    $grace = Tenant::factory()->onGracePeriod()->create();
    expect($grace->onGracePeriod())->toBeTrue();
    expect($grace->hasAccess())->toBeTrue();
});

it('detects expired subscription', function () {
    $expired = Tenant::factory()->expired()->create();
    expect($expired->hasAccess())->toBeFalse();
    expect($expired->requiresPayment())->toBeTrue();
});

it('detects deleting status', function () {
    $deleting = Tenant::factory()->create(['subscription_status' => 'deleting']);
    expect($deleting->isDeleting())->toBeTrue();
});

it('returns subscription statuses', function () {
    expect(Tenant::subscriptionStatuses())->toBe([
        'trial', 'active', 'grace', 'expired', 'deleting',
    ]);
});

it('has room transfers relationship', function () {
    $room = Room::factory()->create(['tenant_id' => $this->tenant->id]);
    $santri = Santri::factory()->create(['tenant_id' => $this->tenant->id]);
    RoomTransfer::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $santri->id,
        'from_room_id' => $room->id,
        'to_room_id' => $room->id,
        'moved_by' => null,
        'moved_at' => now(),
    ]);

    expect($this->tenant->roomTransfers)->toHaveCount(1);
});

it('has santri guardians relationship', function () {
    $santri = Santri::factory()->create(['tenant_id' => $this->tenant->id]);
    SantriGuardian::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $santri->id,
        'user_id' => User::factory()->create(['tenant_id' => $this->tenant->id])->id,
    ]);

    expect($this->tenant->santriGuardians)->toHaveCount(1);
});

it('handles settings via trait', function () {
    $this->tenant->setSetting('notif_email', true);
    expect($this->tenant->getSetting('notif_email'))->toBeTrue();

    $this->tenant->setSettings(['theme' => 'dark', 'locale' => 'id']);
    expect($this->tenant->getSetting('theme'))->toBe('dark');
    expect($this->tenant->getSetting('locale'))->toBe('id');
    expect($this->tenant->getSetting('nonexistent', 'default'))->toBe('default');
});

it('has activity logs relationship', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    ActivityLog::factory()->count(2)->create([
        'tenant_id' => $this->tenant->id,
        'actor_id' => $user->id,
        'actor_name' => $user->name,
        'action' => 'test_action',
    ]);

    expect($this->tenant->activityLogs)->toHaveCount(2);
});

it('has subscription histories relationship', function () {
    TenantSubscriptionHistory::create([
        'tenant_id' => $this->tenant->id,
        'action' => 'created',
        'changed_by' => null,
    ]);

    expect($this->tenant->subscriptionHistories)->toHaveCount(1);
});

it('has billing notes relationship', function () {
    TenantBillingNote::create([
        'tenant_id' => $this->tenant->id,
        'amount' => 100000,
        'payment_method' => 'transfer bank',
        'paid_at' => now(),
        'period_starts_at' => now()->format('Y-m-d'),
        'period_ends_at' => now()->addMonth()->format('Y-m-d'),
        'recorded_by' => null,
    ]);

    expect($this->tenant->billingNotes)->toHaveCount(1);
});
