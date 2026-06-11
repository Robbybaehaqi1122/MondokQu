<?php

use App\Models\Room;
use App\Models\RoomTransfer;
use App\Models\Santri;
use App\Models\SantriGuardian;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use App\Models\SantriPaymentConfirmation;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('Superadmin', 'web');
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Musyrif/Ustadz', 'web');

    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $this->actingAs($superadmin);

    $tenant = Tenant::factory()->activeSubscription()->create();
    $this->tenant = $tenant;
    $this->santri = Santri::factory()->create([
        'tenant_id' => $tenant->id,
        'full_name' => 'Ahmad Santri',
        'gender' => 'male',
        'status' => 'active',
        'nis' => '12345',
        'guardian_name' => 'Bpk Santri',
        'guardian_phone_number' => '08123456789',
        'father_name' => 'Bpk Santri',
        'mother_name' => 'Ibu Santri',
    ]);
});

it('has correct factory', function () {
    expect($this->santri)->toBeInstanceOf(Santri::class);
    expect($this->santri->full_name)->toBe('Ahmad Santri');
});

it('belongs to tenant', function () {
    expect($this->santri->tenant)->toBeInstanceOf(Tenant::class);
    expect($this->santri->tenant->id)->toBe($this->tenant->id);
});

it('belongs to room', function () {
    $room = Room::factory()->create(['tenant_id' => $this->tenant->id]);
    $santri = Santri::factory()->create(['tenant_id' => $this->tenant->id, 'room_id' => $room->id]);

    expect($santri->room)->toBeInstanceOf(Room::class);
    expect($santri->room->id)->toBe($room->id);
});

it('belongs to creator', function () {
    $creator = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $santri = Santri::factory()->create(['tenant_id' => $this->tenant->id, 'created_by' => $creator->id]);

    expect($santri->creator)->toBeInstanceOf(User::class);
    expect($santri->creator->id)->toBe($creator->id);
});

it('has many invoices', function () {
    $invoice = SantriInvoice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
    ]);

    expect($this->santri->invoices)->toHaveCount(1);
    expect($this->santri->invoices->first())->toBeInstanceOf(SantriInvoice::class);
    expect($this->santri->invoices->first()->id)->toBe($invoice->id);
});

it('has many guardian links', function () {
    SantriGuardian::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'user_id' => User::factory()->create(['tenant_id' => $this->tenant->id])->id,
    ]);

    expect($this->santri->guardianLinks)->toHaveCount(1);
    expect($this->santri->guardianLinks->first())->toBeInstanceOf(SantriGuardian::class);
});

it('has correct casts', function () {
    expect($this->santri->birth_date)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($this->santri->entry_date)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($this->santri->entry_year)->toBeInt();
});

it('has scope active', function () {
    Santri::factory()->count(3)->create(['tenant_id' => $this->tenant->id, 'status' => 'active']);
    Santri::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'exited']);

    $activeCount = Santri::query()->active()->count();
    expect($activeCount)->toBeGreaterThanOrEqual(4);
});

it('returns available statuses', function () {
    expect(Santri::availableStatuses())->toBe(['active', 'leave', 'exited', 'alumni']);
});

it('returns available genders', function () {
    expect(Santri::availableGenders())->toBe(['male', 'female']);
});

it('resolves gender label', function () {
    expect($this->santri->genderLabel())->toBe('Laki-laki');

    $santriWanita = Santri::factory()->create(['tenant_id' => $this->tenant->id, 'gender' => 'female']);
    expect($santriWanita->genderLabel())->toBe('Perempuan');
});

it('resolves status label', function () {
    expect($this->santri->statusLabel())->toBe('Aktif');

    foreach (['leave' => 'Cuti', 'exited' => 'Keluar', 'alumni' => 'Alumni'] as $status => $label) {
        $s = Santri::factory()->create(['tenant_id' => $this->tenant->id, 'status' => $status]);
        expect($s->statusLabel())->toBe($label);
    }
});

it('resolves display room name', function () {
    expect($this->santri->displayRoomName())->toBe('-');

    $room = Room::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Al-Ghazali']);
    $santri = Santri::factory()->create(['tenant_id' => $this->tenant->id, 'room_id' => $room->id]);
    expect($santri->displayRoomName())->toBe('Al-Ghazali');
});

it('filters by scope withFilters', function () {
    Santri::factory()->create(['tenant_id' => $this->tenant->id, 'gender' => 'male', 'full_name' => 'Budi Santri']);
    Santri::factory()->create(['tenant_id' => $this->tenant->id, 'gender' => 'female', 'full_name' => 'Siti Santri']);

    $results = Santri::query()->withFilters(search: 'Budi')->get();
    expect($results)->toHaveCount(1);
    expect($results->first()->full_name)->toBe('Budi Santri');

    $results = Santri::query()->withFilters(gender: 'female')->get();
    expect($results)->toHaveCount(1);

    $results = Santri::query()->withFilters(status: 'exited')->get();
    expect($results)->toHaveCount(0);
});

it('has room transfers relationship', function () {
    $room = Room::factory()->create(['tenant_id' => $this->tenant->id]);
    RoomTransfer::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'from_room_id' => $room->id,
        'to_room_id' => $room->id,
        'moved_by' => null,
        'moved_at' => now(),
    ]);

    expect($this->santri->roomTransfers)->toHaveCount(1);
    expect($this->santri->roomTransfers->first())->toBeInstanceOf(RoomTransfer::class);
});

it('has leave requests relationship', function () {
    \App\Models\LeaveRequest::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addDay()->format('Y-m-d'),
        'reason' => 'Test',
        'status' => 'pending',
        'created_by' => null,
    ]);

    expect($this->santri->leaveRequests)->toHaveCount(1);
});

it('creates santri with minimal attributes', function () {
    $santri = Santri::factory()->create([
        'tenant_id' => $this->tenant->id,
        'full_name' => 'Minimal Santri',
        'nis' => '99999',
    ]);

    expect($santri->exists)->toBeTrue();
    expect($santri->full_name)->toBe('Minimal Santri');
});
