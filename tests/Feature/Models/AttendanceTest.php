<?php

use App\Models\AttendanceActivity;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Santri;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('Superadmin', 'web');
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $this->actingAs($superadmin);

    $this->tenant = Tenant::factory()->activeSubscription()->create();
    $this->activity = AttendanceActivity::factory()->forTenant($this->tenant)->create([
        'name' => 'Sholat Subuh',
        'status' => 'active',
        'active_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
    ]);
    $this->session = AttendanceSession::factory()->forActivity($this->activity)->create([
        'session_date' => now()->format('Y-m-d'),
        'status' => 'open',
    ]);
    $this->santri = Santri::factory()->forTenant($this->tenant)->create();
});

it('has correct factories', function () {
    expect($this->activity)->toBeInstanceOf(AttendanceActivity::class);
    expect($this->session)->toBeInstanceOf(AttendanceSession::class);
});

it('activity belongs to tenant', function () {
    expect($this->activity->tenant)->toBeInstanceOf(Tenant::class);
    expect($this->activity->tenant->id)->toBe($this->tenant->id);
});

it('activity has many sessions', function () {
    AttendanceSession::factory()->forActivity($this->activity)->create(['session_date' => now()->addDay()->format('Y-m-d')]);
    AttendanceSession::factory()->forActivity($this->activity)->create(['session_date' => now()->addDays(2)->format('Y-m-d')]);

    expect($this->activity->sessions)->toHaveCount(3);
    expect($this->activity->sessions->first())->toBeInstanceOf(AttendanceSession::class);
});

it('activity has active_days cast as array', function () {
    expect($this->activity->active_days)->toBeArray();
    expect($this->activity->active_days)->toContain('monday');
});

it('activity returns available statuses', function () {
    expect(AttendanceActivity::availableStatuses())->toBe(['active', 'inactive']);
});

it('activity returns available day keys', function () {
    $days = AttendanceActivity::availableDayKeys();
    expect($days)->toContain('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
});

it('activity returns day labels', function () {
    $labels = AttendanceActivity::dayLabels();
    expect($labels['monday'])->toBe('Senin');
    expect($labels['sunday'])->toBe('Ahad');
});

it('activity resolves status label', function () {
    expect($this->activity->statusLabel())->toBe('Aktif');

    $inactive = AttendanceActivity::factory()->forTenant($this->tenant)->create(['status' => 'inactive']);
    expect($inactive->statusLabel())->toBe('Nonaktif');
});

it('activity belongs to responsible user', function () {
    $user = User::factory()->forTenant($this->tenant)->create();
    $activity = AttendanceActivity::factory()->forTenant($this->tenant)->create([
        'responsible_user_id' => $user->id,
    ]);

    expect($activity->responsibleUser)->toBeInstanceOf(User::class);
    expect($activity->responsibleUser->id)->toBe($user->id);
});

it('activity belongs to creator', function () {
    $user = User::factory()->forTenant($this->tenant)->create();
    $activity = AttendanceActivity::factory()->forTenant($this->tenant)->create([
        'created_by' => $user->id,
    ]);

    expect($activity->creator)->toBeInstanceOf(User::class);
    expect($activity->creator->id)->toBe($user->id);
});

it('session belongs to activity', function () {
    expect($this->session->activity)->toBeInstanceOf(AttendanceActivity::class);
    expect($this->session->activity->id)->toBe($this->activity->id);
});

it('session belongs to tenant', function () {
    expect($this->session->tenant)->toBeInstanceOf(Tenant::class);
    expect($this->session->tenant->id)->toBe($this->tenant->id);
});

it('session has many records', function () {
    $santriB = Santri::factory()->forTenant($this->tenant)->create();
    AttendanceRecord::factory()->forSessionAndSantri($this->session, $this->santri)->create(['status' => 'present']);
    AttendanceRecord::factory()->forSessionAndSantri($this->session, $santriB)->create(['status' => 'late']);

    expect($this->session->records)->toHaveCount(2);
    expect($this->session->records->first())->toBeInstanceOf(AttendanceRecord::class);
});

it('session belongs to creator', function () {
    $user = User::factory()->forTenant($this->tenant)->create();
    $session = AttendanceSession::factory()->forActivity($this->activity)->create([
        'session_date' => now()->addDay()->format('Y-m-d'),
        'created_by' => $user->id,
    ]);

    expect($session->creator)->toBeInstanceOf(User::class);
    expect($session->creator->id)->toBe($user->id);
});

it('session has session_date cast as date', function () {
    expect($this->session->session_date)->toBeInstanceOf(Carbon::class);
});

it('session returns available statuses', function () {
    expect(AttendanceSession::availableStatuses())->toBe(['draft', 'open', 'completed']);
});

it('session resolves status label', function () {
    $labels = ['draft' => 'Draft', 'open' => 'Dibuka', 'completed' => 'Selesai'];
    $i = 0;
    foreach ($labels as $status => $label) {
        $session = AttendanceSession::factory()->forActivity($this->activity)->create([
            'session_date' => now()->addDays(++$i)->format('Y-m-d'),
            'status' => $status,
        ]);
        expect($session->statusLabel())->toBe($label);
    }
});

it('record belongs to session', function () {
    $record = AttendanceRecord::factory()->forSessionAndSantri($this->session, $this->santri)->create(['status' => 'present']);
    expect($record->session)->toBeInstanceOf(AttendanceSession::class);
    expect($record->session->id)->toBe($this->session->id);
});

it('record belongs to santri', function () {
    $record = AttendanceRecord::factory()->forSessionAndSantri($this->session, $this->santri)->create(['status' => 'present']);
    expect($record->santri)->toBeInstanceOf(Santri::class);
    expect($record->santri->id)->toBe($this->santri->id);
});

it('record has recorded_at cast as datetime', function () {
    $record = AttendanceRecord::factory()->forSessionAndSantri($this->session, $this->santri)->create(['status' => 'present']);
    expect($record->recorded_at)->toBeInstanceOf(Carbon::class);
});

it('record returns available statuses', function () {
    expect(AttendanceRecord::availableStatuses())->toBe(['present', 'permission', 'sick', 'absent', 'late']);
});

it('record returns status options', function () {
    $options = AttendanceRecord::statusOptions();
    expect($options)->toHaveCount(5);
    expect($options[0]['value'])->toBe('present');
    expect($options[0]['label'])->toBe('Hadir');
});

it('record resolves status label', function () {
    $labels = ['present' => 'Hadir', 'permission' => 'Izin', 'sick' => 'Sakit', 'absent' => 'Alpa', 'late' => 'Terlambat'];
    $i = 0;
    foreach ($labels as $status => $label) {
        $s = Santri::factory()->forTenant($this->tenant)->create(['nis' => 'S'.$i++]);
        $record = AttendanceRecord::factory()->forSessionAndSantri($this->session, $s)->create(['status' => $status]);
        expect($record->statusLabel())->toBe($label);
    }
});

it('record belongs to recorder', function () {
    $user = User::factory()->forTenant($this->tenant)->create();
    $record = AttendanceRecord::factory()->forSessionAndSantri($this->session, $this->santri)->create([
        'recorded_by' => $user->id,
        'status' => 'present',
    ]);

    expect($record->recorder)->toBeInstanceOf(User::class);
    expect($record->recorder->id)->toBe($user->id);
});
