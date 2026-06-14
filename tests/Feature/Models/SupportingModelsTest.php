<?php

use App\Models\ActivityLog;
use App\Models\Communication;
use App\Models\DataExport;
use App\Models\LeaveRequest;
use App\Models\MataPelajaran;
use App\Models\NilaiSantri;
use App\Models\Pelanggaran;
use App\Models\PelanggaranKategori;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomTransfer;
use App\Models\Santri;
use App\Models\SantriGuardian;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use App\Models\SantriPaymentConfirmation;
use App\Models\TahfidzRecord;
use App\Models\TahfidzSession;
use App\Models\TahfidzSurah;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('Superadmin', 'web');

    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $this->actingAs($superadmin);

    $this->tenant = Tenant::factory()->activeSubscription()->create();
    $this->santri = Santri::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
});

//
// Room
//
it('room belongs to tenant and creator', function () {
    $room = Room::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Al-Ghazali',
        'capacity' => 20,
        'created_by' => $this->user->id,
        'status' => 'active',
    ]);

    expect($room->tenant)->toBeInstanceOf(Tenant::class);
    expect($room->tenant->id)->toBe($this->tenant->id);
    expect($room->creator)->toBeInstanceOf(User::class);
    expect($room->creator->id)->toBe($this->user->id);
    expect($room->capacity)->toBeInt();
});

it('room checks available capacity', function () {
    $room = Room::factory()->create(['tenant_id' => $this->tenant->id, 'capacity' => 5, 'name' => 'Test Room']);
    Santri::factory()->count(3)->create([
        'tenant_id' => $this->tenant->id,
        'room_id' => $room->id,
        'status' => 'active',
    ]);
    $room->unsetRelation('santris');

    expect($room->hasAvailableCapacity())->toBeTrue();
    expect($room->hasAvailableCapacity(3))->toBeFalse();
    expect($room->hasAvailableCapacity(2))->toBeTrue();

    $noLimit = Room::factory()->create(['tenant_id' => $this->tenant->id, 'capacity' => null]);
    expect($noLimit->hasAvailableCapacity())->toBeTrue();
    expect($noLimit->hasAvailableCapacity(100))->toBeTrue();
});

it('room returns available statuses and label', function () {
    expect(Room::availableStatuses())->toBe(['active', 'inactive']);

    $room = Room::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'active']);
    expect($room->statusLabel())->toBe('Aktif');

    $room = Room::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'inactive']);
    expect($room->statusLabel())->toBe('Nonaktif');
});

//
// SantriInvoice
//
it('santri invoice has all relationships', function () {
    $invoice = SantriInvoice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'created_by' => $this->user->id,
    ]);

    expect($invoice->santri)->toBeInstanceOf(Santri::class);
    expect($invoice->creator)->toBeInstanceOf(User::class);
    expect($invoice->creator->id)->toBe($this->user->id);
    expect($invoice->amount)->toBeInt();
    expect($invoice->paid_amount)->toBeInt();
    expect($invoice->due_date)->toBeInstanceOf(Carbon::class);
});

it('santri invoice calculates outstanding amount', function () {
    $invoice = SantriInvoice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'amount' => 500000,
        'paid_amount' => 200000,
    ]);

    expect($invoice->outstandingAmount())->toBe(300000);

    $paid = SantriInvoice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'amount' => 500000,
        'paid_amount' => 500000,
        'status' => 'paid',
    ]);
    expect($paid->outstandingAmount())->toBe(0);
});

it('santri invoice checks overdue status', function () {
    $overdue = SantriInvoice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'due_date' => now()->subDay(),
        'status' => 'pending',
    ]);
    expect($overdue->isOverdue())->toBeTrue();

    $notOverdue = SantriInvoice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'due_date' => now()->addDay(),
        'status' => 'pending',
    ]);
    expect($notOverdue->isOverdue())->toBeFalse();

    $paid = SantriInvoice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'due_date' => now()->subDay(),
        'status' => 'paid',
    ]);
    expect($paid->isOverdue())->toBeFalse();
});

it('santri invoice returns available statuses', function () {
    expect(SantriInvoice::availableStatuses())->toBe(['pending', 'partial', 'paid']);
});

it('santri invoice returns status labels', function () {
    foreach (['paid' => 'Lunas', 'partial' => 'Sebagian', 'pending' => 'Menunggu Bayar'] as $status => $label) {
        $invoice = SantriInvoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'santri_id' => $this->santri->id,
            'status' => $status,
        ]);
        expect($invoice->statusLabel())->toBe($label);
    }
});

//
// SantriPayment
//
it('santri payment has all relationships', function () {
    $invoice = SantriInvoice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
    ]);
    $payment = SantriPayment::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_invoice_id' => $invoice->id,
        'santri_id' => $this->santri->id,
        'recorded_by' => $this->user->id,
    ]);

    expect($payment->invoice)->toBeInstanceOf(SantriInvoice::class);
    expect($payment->santri)->toBeInstanceOf(Santri::class);
    expect($payment->recorder)->toBeInstanceOf(User::class);
    expect($payment->amount)->toBeInt();
    expect($payment->paid_at)->toBeInstanceOf(Carbon::class);
});

it('santri payment returns payment methods', function () {
    expect(SantriPayment::paymentMethods())->toBe(['transfer bank', 'cash', 'e-wallet', 'qris', 'lainnya']);
});

//
// SantriPaymentConfirmation
//
it('santri payment confirmation has all relationships', function () {
    $invoice = SantriInvoice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
    ]);
    $confirmation = SantriPaymentConfirmation::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_invoice_id' => $invoice->id,
        'santri_id' => $this->santri->id,
        'submitted_by' => $this->user->id,
    ]);

    expect($confirmation->invoice)->toBeInstanceOf(SantriInvoice::class);
    expect($confirmation->santri)->toBeInstanceOf(Santri::class);
    expect($confirmation->submittedBy)->toBeInstanceOf(User::class);
    expect($confirmation->amount)->toBeInt();
});

it('santri payment confirmation returns status label', function () {
    $invoice = SantriInvoice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
    ]);
    $pending = SantriPaymentConfirmation::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_invoice_id' => $invoice->id,
        'santri_id' => $this->santri->id,
        'status' => 'pending',
    ]);
    expect($pending->statusLabel())->toBe('Menunggu Verifikasi');

    $approved = SantriPaymentConfirmation::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_invoice_id' => $invoice->id,
        'santri_id' => $this->santri->id,
        'status' => 'approved',
    ]);
    expect($approved->statusLabel())->toBe('Diterima');

    $rejected = SantriPaymentConfirmation::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_invoice_id' => $invoice->id,
        'santri_id' => $this->santri->id,
        'status' => 'rejected',
    ]);
    expect($rejected->statusLabel())->toBe('Ditolak');
});

//
// SantriGuardian
//
it('santri guardian has all relationships', function () {
    $wali = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $guardian = SantriGuardian::factory()->create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'user_id' => $wali->id,
        'is_primary' => true,
    ]);

    expect($guardian->santri)->toBeInstanceOf(Santri::class);
    expect($guardian->user)->toBeInstanceOf(User::class);
    expect($guardian->user->id)->toBe($wali->id);
    expect($guardian->is_primary)->toBeTrue();
    expect($guardian->relationship)->not->toBeNull();
});

//
// RoomTransfer
//
it('room transfer has all relationships', function () {
    $roomA = Room::factory()->create(['tenant_id' => $this->tenant->id]);
    $roomB = Room::factory()->create(['tenant_id' => $this->tenant->id]);
    $transfer = RoomTransfer::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'from_room_id' => $roomA->id,
        'to_room_id' => $roomB->id,
        'moved_by' => $this->user->id,
        'moved_at' => now(),
    ]);

    expect($transfer->santri)->toBeInstanceOf(Santri::class);
    expect($transfer->fromRoom)->toBeInstanceOf(Room::class);
    expect($transfer->fromRoom->id)->toBe($roomA->id);
    expect($transfer->toRoom)->toBeInstanceOf(Room::class);
    expect($transfer->toRoom->id)->toBe($roomB->id);
    expect($transfer->mover)->toBeInstanceOf(User::class);
    expect($transfer->mover->id)->toBe($this->user->id);
    expect($transfer->moved_at)->toBeInstanceOf(Carbon::class);
});

//
// LeaveRequest
//
it('leave request has all relationships and methods', function () {
    $leave = LeaveRequest::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addDay()->format('Y-m-d'),
        'reason' => 'Test leave',
        'status' => 'pending',
        'created_by' => $this->user->id,
    ]);

    expect($leave->santri)->toBeInstanceOf(Santri::class);
    expect($leave->creator)->toBeInstanceOf(User::class);
    expect($leave->creator->id)->toBe($this->user->id);
    expect($leave->start_date)->toBeInstanceOf(Carbon::class);
    expect($leave->end_date)->toBeInstanceOf(Carbon::class);
});

it('leave request belongs to approver', function () {
    $leave = LeaveRequest::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addDay()->format('Y-m-d'),
        'reason' => 'Test',
        'status' => 'approved',
        'approved_by' => $this->user->id,
        'approved_at' => now(),
        'created_by' => $this->user->id,
    ]);

    expect($leave->approver)->toBeInstanceOf(User::class);
    expect($leave->approver->id)->toBe($this->user->id);
    expect($leave->approved_at)->toBeInstanceOf(Carbon::class);
});

it('leave request scope activeOnDate works', function () {
    LeaveRequest::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'start_date' => now()->subDay()->format('Y-m-d'),
        'end_date' => now()->addDay()->format('Y-m-d'),
        'reason' => 'Test',
        'status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    $active = LeaveRequest::query()->activeOnDate(now())->get();
    expect($active)->toHaveCount(1);

    $notActive = LeaveRequest::query()->activeOnDate(now()->addWeek())->get();
    expect($notActive)->toHaveCount(0);
});

it('leave request resolves status label', function () {
    foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'completed' => 'Selesai'] as $status => $label) {
        $leave = LeaveRequest::create([
            'tenant_id' => $this->tenant->id,
            'santri_id' => $this->santri->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'reason' => 'Test',
            'status' => $status,
            'created_by' => $this->user->id,
        ]);
        expect($leave->statusLabel())->toBe($label);
    }
});

it('leave request returns available statuses', function () {
    expect(LeaveRequest::availableStatuses())->toBe(['pending', 'approved', 'rejected', 'completed']);
});

//
// Communication
//
it('communication has all relationships', function () {
    $comm = Communication::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'user_id' => $this->user->id,
        'message' => 'Test message',
        'direction' => 'incoming',
        'is_read' => false,
        'is_replied' => false,
    ]);

    expect($comm->santri)->toBeInstanceOf(Santri::class);
    expect($comm->user)->toBeInstanceOf(User::class);
    expect($comm->sender)->toBeInstanceOf(User::class);
    expect($comm->tenant)->toBeInstanceOf(Tenant::class);
    expect($comm->is_read)->toBeBool();
    expect($comm->is_replied)->toBeBool();
});

it('communication has reply thread relationships', function () {
    $parent = Communication::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'user_id' => $this->user->id,
        'message' => 'Parent message',
        'direction' => 'incoming',
    ]);
    $reply = Communication::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'user_id' => $this->user->id,
        'message' => 'Reply message',
        'direction' => 'incoming',
        'parent_id' => $parent->id,
    ]);

    expect($parent->replies)->toHaveCount(1);
    expect($parent->replies->first()->id)->toBe($reply->id);
    expect($reply->parent)->toBeInstanceOf(Communication::class);
    expect($reply->parent->id)->toBe($parent->id);
});

it('communication has forward relationships', function () {
    $original = Communication::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'user_id' => $this->user->id,
        'message' => 'Original message',
        'direction' => 'incoming',
    ]);
    $forwarded = Communication::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'user_id' => $this->user->id,
        'message' => 'Forwarded message',
        'direction' => 'incoming',
        'forwarded_from_id' => $original->id,
    ]);

    expect($original->forwardedMessages)->toHaveCount(1);
    expect($original->forwardedMessages->first()->id)->toBe($forwarded->id);
    expect($forwarded->forwardedFrom)->toBeInstanceOf(Communication::class);
    expect($forwarded->forwardedFrom->id)->toBe($original->id);
});

//
// Pelanggaran & PelanggaranKategori
//
it('pelanggaran has all relationships', function () {
    $kategori = PelanggaranKategori::create([
        'tenant_id' => $this->tenant->id,
        'nama' => 'Terlambat',
        'poin' => 10,
        'created_by' => $this->user->id,
    ]);
    $pelanggaran = Pelanggaran::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'kategori_id' => $kategori->id,
        'keterangan' => 'Terlambat sholat',
        'poin' => 10,
        'dicatat_oleh' => $this->user->id,
        'tanggal' => now()->format('Y-m-d'),
    ]);

    expect($pelanggaran->santri)->toBeInstanceOf(Santri::class);
    expect($pelanggaran->kategori)->toBeInstanceOf(PelanggaranKategori::class);
    expect($pelanggaran->pencatat)->toBeInstanceOf(User::class);
    expect($pelanggaran->poin)->toBeInt();
    expect($pelanggaran->tanggal)->toBeInstanceOf(Carbon::class);
});

it('pelanggaran kategori has creator and pelanggarans', function () {
    $kategori = PelanggaranKategori::create([
        'tenant_id' => $this->tenant->id,
        'nama' => 'Merokok',
        'poin' => 25,
        'created_by' => $this->user->id,
    ]);
    Pelanggaran::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'kategori_id' => $kategori->id,
        'keterangan' => 'Test',
        'poin' => 25,
        'dicatat_oleh' => $this->user->id,
        'tanggal' => now()->format('Y-m-d'),
    ]);

    expect($kategori->creator)->toBeInstanceOf(User::class);
    expect($kategori->creator->id)->toBe($this->user->id);
    expect($kategori->pelanggarans)->toHaveCount(1);
    expect($kategori->poin)->toBeInt();
});

//
// TahfidzSurah
//
it('tahfidz surah has records', function () {
    $surah = TahfidzSurah::create([
        'number' => 1,
        'name' => 'Al-Fatihah',
        'name_arabic' => 'الفاتحة',
        'verses_count' => 7,
        'juz' => 1,
    ]);
    $session = TahfidzSession::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'musyrif_id' => $this->user->id,
        'session_date' => now()->format('Y-m-d'),
        'status' => 'completed',
    ]);
    TahfidzRecord::create([
        'tenant_id' => $this->tenant->id,
        'tahfidz_session_id' => $session->id,
        'surah_id' => $surah->id,
        'verse_start' => 1,
        'verse_end' => 7,
        'evaluation' => 'lancar',
    ]);

    expect($surah->records)->toHaveCount(1);
    expect($surah->records->first()->surah_id)->toBe($surah->id);
});

//
// TahfidzSession
//
it('tahfidz session has all relationships', function () {
    $session = TahfidzSession::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'musyrif_id' => $this->user->id,
        'session_date' => now()->format('Y-m-d'),
        'status' => 'completed',
    ]);

    expect($session->santri)->toBeInstanceOf(Santri::class);
    expect($session->musyrif)->toBeInstanceOf(User::class);
    expect($session->musyrif->id)->toBe($this->user->id);
    expect($session->session_date)->toBeInstanceOf(Carbon::class);
    expect(TahfidzSession::availableStatuses())->toBe(['draft', 'completed']);
});

it('tahfidz session resolves status label', function () {
    $session = TahfidzSession::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'musyrif_id' => $this->user->id,
        'session_date' => now()->format('Y-m-d'),
        'status' => 'draft',
    ]);
    expect($session->statusLabel())->toBe('Draft');

    $session->update(['status' => 'completed']);
    $session->refresh();
    expect($session->statusLabel())->toBe('Selesai');
});

it('tahfidz record has all relationships', function () {
    $surah = TahfidzSurah::create([
        'number' => 36, 'name' => 'Ya Sin', 'name_arabic' => 'يس', 'verses_count' => 83, 'juz' => 22,
    ]);
    $session = TahfidzSession::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'musyrif_id' => $this->user->id,
        'session_date' => now()->format('Y-m-d'),
        'status' => 'completed',
    ]);
    $record = TahfidzRecord::create([
        'tenant_id' => $this->tenant->id,
        'tahfidz_session_id' => $session->id,
        'surah_id' => $surah->id,
        'verse_start' => 1,
        'verse_end' => 5,
        'evaluation' => 'lancar',
    ]);

    expect($record->session)->toBeInstanceOf(TahfidzSession::class);
    expect($record->session->id)->toBe($session->id);
    expect($record->surah)->toBeInstanceOf(TahfidzSurah::class);
    expect($record->surah->id)->toBe($surah->id);
    expect($record->verse_start)->toBeInt();
    expect($record->verse_end)->toBeInt();
});

it('tahfidz record resolves evaluation label', function () {
    $surah = TahfidzSurah::create([
        'number' => 36, 'name' => 'Ya Sin', 'name_arabic' => 'يس', 'verses_count' => 83, 'juz' => 22,
    ]);
    $session = TahfidzSession::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'musyrif_id' => $this->user->id,
        'session_date' => now()->format('Y-m-d'),
        'status' => 'completed',
    ]);

    foreach (['lancar' => 'Lancar', 'perlu_pengulangan' => 'Perlu Pengulangan', 'belum_lancar' => 'Belum Lancar'] as $value => $label) {
        $record = TahfidzRecord::create([
            'tenant_id' => $this->tenant->id,
            'tahfidz_session_id' => $session->id,
            'surah_id' => $surah->id,
            'verse_start' => 1,
            'verse_end' => 5,
            'evaluation' => $value,
        ]);
        expect($record->evaluationLabel())->toBe($label);
    }
});

it('tahfidz record resolves verse range label', function () {
    $surah = TahfidzSurah::create([
        'number' => 36, 'name' => 'Ya Sin', 'name_arabic' => 'يس', 'verses_count' => 83, 'juz' => 22,
    ]);
    $session = TahfidzSession::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'musyrif_id' => $this->user->id,
        'session_date' => now()->format('Y-m-d'),
        'status' => 'completed',
    ]);

    $record = TahfidzRecord::create([
        'tenant_id' => $this->tenant->id,
        'tahfidz_session_id' => $session->id,
        'surah_id' => $surah->id,
        'verse_start' => 1,
        'verse_end' => 5,
        'evaluation' => 'lancar',
    ]);
    expect($record->verseRangeLabel())->toBe('Ayat 1-5');

    $single = TahfidzRecord::create([
        'tenant_id' => $this->tenant->id,
        'tahfidz_session_id' => $session->id,
        'surah_id' => $surah->id,
        'verse_start' => 3,
        'verse_end' => 3,
        'evaluation' => 'lancar',
    ]);
    expect($single->verseRangeLabel())->toBe('Ayat 3');
});

it('tahfidz record returns available evaluations', function () {
    expect(TahfidzRecord::availableEvaluations())->toBe(['lancar', 'perlu_pengulangan', 'belum_lancar']);
});

//
// NilaiSantri & MataPelajaran
//
it('nilai santri has all relationships and accessors', function () {
    $mapel = MataPelajaran::create([
        'tenant_id' => $this->tenant->id,
        'nama' => 'Bahasa Arab',
        'kkm' => 70,
        'is_active' => true,
    ]);
    $nilai = NilaiSantri::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'mata_pelajaran_id' => $mapel->id,
        'semester' => '2024/2025 Ganjil',
        'nilai_pengetahuan' => 80,
        'nilai_keterampilan' => 90,
        'input_by' => $this->user->id,
    ]);

    expect($nilai->santri)->toBeInstanceOf(Santri::class);
    expect($nilai->mataPelajaran)->toBeInstanceOf(MataPelajaran::class);
    expect($nilai->mataPelajaran->id)->toBe($mapel->id);
    expect($nilai->inputBy)->toBeInstanceOf(User::class);
    expect($nilai->nilai_pengetahuan)->toBeInt();
    expect($nilai->nilai_keterampilan)->toBeInt();
    expect($nilai->nilai_akhir)->toBe(85);
    expect($nilai->predikat)->toBe('B');
});

it('nilai santri computes nilah akhir and predikat correctly', function () {
    $mapel = MataPelajaran::create([
        'tenant_id' => $this->tenant->id,
        'nama' => 'Matematika',
        'kkm' => 70,
        'is_active' => true,
    ]);

    $nilai = NilaiSantri::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'mata_pelajaran_id' => $mapel->id,
        'semester' => '2024/2025 Genap',
        'nilai_pengetahuan' => 100,
        'nilai_keterampilan' => 100,
        'input_by' => $this->user->id,
    ]);
    expect($nilai->nilai_akhir)->toBe(100);
    expect($nilai->predikat)->toBe('A');

    $nilai2 = NilaiSantri::create([
        'tenant_id' => $this->tenant->id,
        'santri_id' => $this->santri->id,
        'mata_pelajaran_id' => $mapel->id,
        'semester' => '2024/2025 Genap 2',
        'nilai_pengetahuan' => 60,
        'nilai_keterampilan' => 60,
        'input_by' => $this->user->id,
    ]);
    expect($nilai2->nilai_akhir)->toBe(60);
    expect($nilai2->predikat)->toBe('C');
});

it('mata pelajaran scope active works', function () {
    MataPelajaran::create(['tenant_id' => $this->tenant->id, 'nama' => 'Active 1', 'kkm' => 70, 'is_active' => true]);
    MataPelajaran::create(['tenant_id' => $this->tenant->id, 'nama' => 'Active 2', 'kkm' => 70, 'is_active' => true]);
    MataPelajaran::create(['tenant_id' => $this->tenant->id, 'nama' => 'Inactive', 'kkm' => 70, 'is_active' => false]);

    expect(MataPelajaran::query()->active()->count())->toBe(2);
});

//
// DataExport
//
it('data export has relationships', function () {
    $export = DataExport::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'type' => 'santri',
        'status' => 'queued',
        'name' => 'Test export',
        'filename' => 'test.csv',
        'format' => 'csv',
    ]);

    expect($export->tenant)->toBeInstanceOf(Tenant::class);
    expect($export->user)->toBeInstanceOf(User::class);
    expect($export->filters)->toBeNull();
});

it('data export status methods work', function () {
    $export = DataExport::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'type' => 'santri',
        'status' => 'queued',
        'name' => 'Test',
        'filename' => 'test.csv',
        'format' => 'csv',
    ]);

    expect($export->isCompleted())->toBeFalse();

    $export->markProcessing();
    expect($export->status)->toBe('processing');

    $export->markCompleted('exports/test.csv', 'test.csv', 10);
    expect($export->isCompleted())->toBeTrue();
    expect($export->filename)->toBe('test.csv');
    expect($export->row_count)->toBe(10);

    $export2 = DataExport::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'type' => 'santri',
        'status' => 'queued',
        'name' => 'Test 2',
        'filename' => 'test2.csv',
        'format' => 'csv',
    ]);
    $export2->markFailed('Something went wrong');
    expect($export2->status)->toBe('failed');
    expect($export2->failure_message)->toBe('Something went wrong');
});

it('data export scope visibleTo works', function () {
    $otherUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

    DataExport::create(['tenant_id' => $this->tenant->id, 'user_id' => $this->user->id, 'type' => 'santri', 'status' => 'queued', 'name' => 'E1', 'filename' => 'e1.csv', 'format' => 'csv']);
    DataExport::create(['tenant_id' => $this->tenant->id, 'user_id' => $this->user->id, 'type' => 'santri', 'status' => 'queued', 'name' => 'E2', 'filename' => 'e2.csv', 'format' => 'csv']);
    DataExport::create(['tenant_id' => $this->tenant->id, 'user_id' => $otherUser->id, 'type' => 'santri', 'status' => 'queued', 'name' => 'E3', 'filename' => 'e3.csv', 'format' => 'csv']);

    $owned = DataExport::query()->visibleTo($this->user)->get();
    expect($owned)->toHaveCount(2);
});

it('data export scope expired works', function () {
    DataExport::create(['tenant_id' => $this->tenant->id, 'user_id' => $this->user->id, 'type' => 'santri', 'status' => 'completed', 'name' => 'E1', 'filename' => 'e1.csv', 'format' => 'csv', 'expires_at' => now()->subDay()]);
    DataExport::create(['tenant_id' => $this->tenant->id, 'user_id' => $this->user->id, 'type' => 'santri', 'status' => 'completed', 'name' => 'E2', 'filename' => 'e2.csv', 'format' => 'csv', 'expires_at' => now()->addDay()]);
    DataExport::create(['tenant_id' => $this->tenant->id, 'user_id' => $this->user->id, 'type' => 'santri', 'status' => 'completed', 'name' => 'E3', 'filename' => 'e3.csv', 'format' => 'csv', 'expires_at' => null]);

    expect(DataExport::query()->expired()->count())->toBe(1);
});

it('data export scope forType works', function () {
    DataExport::create(['tenant_id' => $this->tenant->id, 'user_id' => $this->user->id, 'type' => 'santri', 'status' => 'queued', 'name' => 'E1', 'filename' => 'e1.csv', 'format' => 'csv']);
    DataExport::create(['tenant_id' => $this->tenant->id, 'user_id' => $this->user->id, 'type' => 'santri_invoices', 'status' => 'queued', 'name' => 'E2', 'filename' => 'e2.csv', 'format' => 'csv']);

    expect(DataExport::query()->forType('santri')->count())->toBe(1);
});

it('data export isOwnedBy works', function () {
    $export = DataExport::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'type' => 'santri',
        'status' => 'queued',
        'name' => 'Test',
        'filename' => 'test.csv',
        'format' => 'csv',
    ]);

    expect($export->isOwnedBy($this->user))->toBeTrue();

    $other = User::factory()->create(['tenant_id' => $this->tenant->id]);
    expect($export->isOwnedBy($other))->toBeFalse();
});

//
// ActivityLog
//
it('activity log has relationships', function () {
    $log = ActivityLog::factory()->create([
        'tenant_id' => $this->tenant->id,
        'actor_id' => $this->user->id,
        'actor_name' => $this->user->name,
        'action' => 'user_created',
    ]);

    expect($log->actor)->toBeInstanceOf(User::class);
    expect($log->actor->id)->toBe($this->user->id);
    expect($log->tenant)->toBeInstanceOf(Tenant::class);
    expect($log->tenant->id)->toBe($this->tenant->id);
    expect($log->properties)->toBeArray();
});

it('activity log logs without actor', function () {
    $log = ActivityLog::factory()->create([
        'tenant_id' => $this->tenant->id,
        'actor_id' => null,
        'actor_name' => 'System',
        'action' => 'system_action',
    ]);

    expect($log->actor)->toBeNull();
    expect($log->actor_name)->toBe('System');
});
