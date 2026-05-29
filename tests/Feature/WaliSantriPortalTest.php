<?php

use App\Models\AttendanceActivity;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\LeaveRequest;
use App\Models\Room;
use App\Models\Santri;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use App\Models\SantriPaymentConfirmation;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\WaliPaymentProofSubmittedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Wali Santri', 'web');

    Permission::findOrCreate('view user details', 'web');
    Permission::findOrCreate('update users', 'web');
    Permission::findOrCreate('view portal wali', 'web');
});

test('wali santri dashboard shows only linked santri and payment summary', function () {
    $wali = tenantUser('Wali Santri');
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    $linkedSantri = Santri::factory()->forTenant($wali->tenant)->create([
        'nis' => 'WALI001',
        'full_name' => 'Ahmad Anak Wali',
    ]);
    $unlinkedSantri = Santri::factory()->forTenant($wali->tenant)->create([
        'full_name' => 'Santri Tidak Terhubung',
    ]);
    $otherTenantSantri = Santri::factory()->forTenant($otherTenant)->create([
        'full_name' => 'Santri Tenant Lain',
    ]);

    $wali->guardianSantris()->attach($linkedSantri->id, [
        'tenant_id' => $wali->tenant_id,
        'relationship' => 'Ayah',
    ]);

    $invoice = SantriInvoice::factory()->forSantri($linkedSantri)->create([
        'invoice_number' => 'INV-WALI-001',
        'title' => 'SPP Mei Wali',
        'amount' => 50000000,
        'paid_amount' => 15000000,
        'status' => SantriInvoice::STATUS_PARTIAL,
        'due_date' => now()->subDay(),
    ]);
    SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 15000000,
        'paid_at' => now(),
    ]);
    SantriInvoice::factory()->forSantri($unlinkedSantri)->create([
        'title' => 'Tagihan Tidak Terlihat',
        'amount' => 99900000,
    ]);
    SantriInvoice::factory()->forSantri($otherTenantSantri)->create([
        'title' => 'Tagihan Tenant Lain',
        'amount' => 88800000,
    ]);

    $response = $this
        ->actingAs($wali)
        ->get(route('wali-santri.dashboard'));

    $response->assertOk();
    $response->assertSee('Ahmad Anak Wali');
    $response->assertSee('Ayah');
    $response->assertSee('SPP Mei Wali');
    $response->assertSee('Rp 350.000');
    $response->assertSee('Upload Bukti Bayar');
    $response->assertSee('data-mobile-invoice-list', false);
    $response->assertSee('data-mobile-invoice-card', false);
    $response->assertSee('data-mobile-payment-list', false);
    $response->assertSee('data-mobile-payment-card', false);
    $response->assertSee(route('wali-santri.invoices.show', $invoice), false);
    $response->assertDontSee('Santri Tidak Terhubung');
    $response->assertDontSee('Santri Tenant Lain');
    $response->assertDontSee('Tagihan Tidak Terlihat');
    $response->assertDontSee('Tagihan Tenant Lain');
});

test('dashboard route redirects wali santri to portal', function () {
    $wali = tenantUser('Wali Santri');

    $response = $this
        ->actingAs($wali)
        ->get(route('dashboard'));

    $response->assertRedirect(route('wali-santri.dashboard', absolute: false));
});

test('wali santri dashboard shows leave requests only for linked santri', function () {
    $wali = tenantUser('Wali Santri');
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    $linkedSantri = Santri::factory()->forTenant($wali->tenant)->create([
        'full_name' => 'Maryam Anak Wali',
    ]);
    $unlinkedSantri = Santri::factory()->forTenant($wali->tenant)->create([
        'full_name' => 'Santri Izin Tidak Terhubung',
    ]);
    $otherTenantSantri = Santri::factory()->forTenant($otherTenant)->create([
        'full_name' => 'Santri Izin Tenant Lain',
    ]);

    $wali->guardianSantris()->attach($linkedSantri->id, [
        'tenant_id' => $wali->tenant_id,
        'relationship' => 'Ibu',
    ]);

    LeaveRequest::query()->create([
        'tenant_id' => $wali->tenant_id,
        'santri_id' => $linkedSantri->id,
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addDay()->toDateString(),
        'reason' => 'Izin pulang keluarga wali.',
        'status' => LeaveRequest::STATUS_APPROVED,
        'approved_by' => $wali->id,
        'approved_at' => now(),
        'created_by' => $wali->id,
    ]);
    LeaveRequest::query()->create([
        'tenant_id' => $wali->tenant_id,
        'santri_id' => $linkedSantri->id,
        'start_date' => now()->addDays(3)->toDateString(),
        'end_date' => now()->addDays(4)->toDateString(),
        'reason' => 'Menunggu izin kegiatan keluarga.',
        'status' => LeaveRequest::STATUS_PENDING,
        'created_by' => $wali->id,
    ]);
    LeaveRequest::query()->create([
        'tenant_id' => $wali->tenant_id,
        'santri_id' => $unlinkedSantri->id,
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'reason' => 'Izin santri tidak tertaut.',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);
    LeaveRequest::query()->create([
        'tenant_id' => $otherTenant->id,
        'santri_id' => $otherTenantSantri->id,
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'reason' => 'Izin tenant lain.',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $response = $this
        ->actingAs($wali)
        ->get(route('wali-santri.dashboard'));

    $response->assertOk();
    $response->assertSee('Riwayat Izin Santri');
    $response->assertSee('Ringkasan Izin');
    $response->assertSee('Maryam Anak Wali');
    $response->assertSee('Izin pulang keluarga wali.');
    $response->assertSee('Menunggu izin kegiatan keluarga.');
    $response->assertSee('Disetujui');
    $response->assertSee('Menunggu');
    $response->assertDontSee('Santri Izin Tidak Terhubung');
    $response->assertDontSee('Izin santri tidak tertaut.');
    $response->assertDontSee('Santri Izin Tenant Lain');
    $response->assertDontSee('Izin tenant lain.');
});

test('wali santri dashboard shows attendance records only for linked santri', function () {
    $wali = tenantUser('Wali Santri');
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    $linkedSantri = Santri::factory()->forTenant($wali->tenant)->create([
        'full_name' => 'Umar Anak Wali Absensi',
    ]);
    $unlinkedSantri = Santri::factory()->forTenant($wali->tenant)->create([
        'full_name' => 'Santri Absensi Tidak Tertaut',
    ]);
    $otherTenantSantri = Santri::factory()->forTenant($otherTenant)->create([
        'full_name' => 'Santri Absensi Tenant Lain',
    ]);

    $wali->guardianSantris()->attach($linkedSantri->id, [
        'tenant_id' => $wali->tenant_id,
        'relationship' => 'Ayah',
    ]);

    $activity = AttendanceActivity::factory()->forTenant($wali->tenant)->create([
        'name' => 'Halaqah Wali Absensi',
    ]);
    $session = AttendanceSession::factory()->forActivity($activity)->create([
        'session_date' => now()->toDateString(),
    ]);
    $previousSession = AttendanceSession::factory()->forActivity($activity)->create([
        'session_date' => now()->subDay()->toDateString(),
    ]);
    $otherTenantActivity = AttendanceActivity::factory()->forTenant($otherTenant)->create([
        'name' => 'Halaqah Tenant Lain Absensi',
    ]);
    $otherTenantSession = AttendanceSession::factory()->forActivity($otherTenantActivity)->create([
        'session_date' => now()->toDateString(),
    ]);

    AttendanceRecord::factory()->forSessionAndSantri($session, $linkedSantri)->create([
        'status' => AttendanceRecord::STATUS_LATE,
        'notes' => 'Catatan telat khusus portal wali.',
        'recorded_at' => now(),
    ]);
    AttendanceRecord::factory()->forSessionAndSantri($previousSession, $linkedSantri)->create([
        'status' => AttendanceRecord::STATUS_PERMISSION,
        'notes' => 'Catatan izin khusus portal wali.',
        'recorded_at' => now()->subHour(),
    ]);
    AttendanceRecord::factory()->forSessionAndSantri($session, $unlinkedSantri)->create([
        'status' => AttendanceRecord::STATUS_ABSENT,
        'notes' => 'Catatan absensi tidak tertaut.',
        'recorded_at' => now(),
    ]);
    AttendanceRecord::factory()->forSessionAndSantri($otherTenantSession, $otherTenantSantri)->create([
        'status' => AttendanceRecord::STATUS_SICK,
        'notes' => 'Catatan absensi tenant lain.',
        'recorded_at' => now(),
    ]);

    $response = $this
        ->actingAs($wali)
        ->get(route('wali-santri.dashboard'));

    $response->assertOk();
    $response->assertSee('Ringkasan Absensi');
    $response->assertSee('Riwayat Absensi Santri');
    $response->assertSee('Umar Anak Wali Absensi');
    $response->assertSee('Halaqah Wali Absensi');
    $response->assertSee('Terlambat');
    $response->assertSee('Izin');
    $response->assertSee('Catatan telat khusus portal wali.');
    $response->assertSee('Catatan izin khusus portal wali.');
    $response->assertDontSee('Santri Absensi Tidak Tertaut');
    $response->assertDontSee('Catatan absensi tidak tertaut.');
    $response->assertDontSee('Santri Absensi Tenant Lain');
    $response->assertDontSee('Catatan absensi tenant lain.');
});

test('wali santri can view linked invoice detail with payment history', function () {
    $wali = tenantUser('Wali Santri');
    $linkedSantri = Santri::factory()->forTenant($wali->tenant)->create([
        'nis' => 'WALI-DETAIL',
        'full_name' => 'Fatimah Anak Wali',
    ]);

    $wali->guardianSantris()->attach($linkedSantri->id, [
        'tenant_id' => $wali->tenant_id,
        'relationship' => 'Ibu',
    ]);

    $invoice = SantriInvoice::factory()->forSantri($linkedSantri)->create([
        'invoice_number' => 'INV-WALI-DETAIL',
        'title' => 'SPP Detail Wali',
        'amount' => 50000000,
        'paid_amount' => 15000000,
        'status' => SantriInvoice::STATUS_PARTIAL,
        'notes' => 'Dibayarkan sebelum akhir bulan.',
    ]);

    SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 10000000,
        'payment_method' => 'transfer bank',
        'reference_number' => 'REF-WALI-1',
        'note' => 'Pembayaran awal wali',
        'paid_at' => now()->subDays(2),
    ]);
    SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 5000000,
        'payment_method' => 'cash',
        'reference_number' => 'REF-WALI-2',
        'paid_at' => now()->subDay(),
    ]);

    $response = $this
        ->actingAs($wali)
        ->get(route('wali-santri.invoices.show', $invoice));

    $response->assertOk();
    $response->assertSee('Detail Tagihan');
    $response->assertSee(route('wali-santri.invoices.receipt', $invoice), false);
    $response->assertSee('SPP Detail Wali');
    $response->assertSee('INV-WALI-DETAIL');
    $response->assertSee('Fatimah Anak Wali');
    $response->assertSee('Rp 500.000');
    $response->assertSee('Rp 150.000');
    $response->assertSee('Rp 350.000');
    $response->assertSee('Transfer Bank');
    $response->assertSee('Cash');
    $response->assertSee('REF-WALI-1');
    $response->assertSee('Pembayaran awal wali');
    $response->assertSee('Dibayarkan sebelum akhir bulan.');
    $response->assertSee('Upload Bukti Bayar');
    $response->assertSee('Bukti Bayar Dikirim');
});

test('wali santri can upload payment proof for linked invoice', function () {
    Storage::fake('public');
    Notification::fake();

    $wali = tenantUser('Wali Santri');
    $admin = User::factory()->forTenant($wali->tenant)->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $admin->assignRole('Admin');

    $linkedSantri = Santri::factory()->forTenant($wali->tenant)->create([
        'full_name' => 'Santri Bukti Bayar',
    ]);
    $wali->guardianSantris()->attach($linkedSantri->id, [
        'tenant_id' => $wali->tenant_id,
        'relationship' => 'Ayah',
    ]);

    $invoice = SantriInvoice::factory()->forSantri($linkedSantri)->create([
        'invoice_number' => 'INV-PROOF-001',
        'title' => 'SPP Bukti Bayar',
        'amount' => 50000000,
        'paid_amount' => 10000000,
        'status' => SantriInvoice::STATUS_PARTIAL,
    ]);
    $proof = UploadedFile::fake()->createWithContent(
        'bukti-bayar.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
    );

    $response = $this
        ->actingAs($wali)
        ->from(route('wali-santri.dashboard'))
        ->post(route('wali-santri.invoices.payment-confirmations.store', $invoice), [
            'confirmation_invoice_id' => $invoice->id,
            'amount' => 400000,
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'payment_method' => 'transfer bank',
            'reference_number' => 'TRF-WALI-001',
            'note' => 'Transfer dari rekening orang tua.',
            'proof' => $proof,
        ]);

    $response->assertRedirect(route('wali-santri.dashboard', absolute: false));
    $response->assertSessionHas('success');

    $confirmation = SantriPaymentConfirmation::query()->first();

    expect($confirmation)->not->toBeNull();
    expect($confirmation->tenant_id)->toBe($wali->tenant_id);
    expect($confirmation->santri_invoice_id)->toBe($invoice->id);
    expect($confirmation->santri_id)->toBe($linkedSantri->id);
    expect($confirmation->submitted_by)->toBe($wali->id);
    expect($confirmation->status)->toBe(SantriPaymentConfirmation::STATUS_PENDING);
    expect($confirmation->amount)->toBe(40000000);
    Storage::disk('public')->assertExists($confirmation->proof_path);
    Notification::assertSentTo($admin, WaliPaymentProofSubmittedNotification::class);

    $this
        ->actingAs($wali)
        ->get(route('wali-santri.dashboard'))
        ->assertOk()
        ->assertSee('Bukti bayar menunggu verifikasi');
});

test('wali santri can not upload payment proof for unlinked invoice', function () {
    Storage::fake('public');

    $wali = tenantUser('Wali Santri');
    $unlinkedSantri = Santri::factory()->forTenant($wali->tenant)->create();
    $invoice = SantriInvoice::factory()->forSantri($unlinkedSantri)->create([
        'amount' => 30000000,
    ]);
    $proof = UploadedFile::fake()->createWithContent(
        'bukti-bayar.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
    );

    $this
        ->actingAs($wali)
        ->post(route('wali-santri.invoices.payment-confirmations.store', $invoice), [
            'confirmation_invoice_id' => $invoice->id,
            'amount' => 300000,
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'payment_method' => 'transfer bank',
            'proof' => $proof,
        ])
        ->assertNotFound();

    expect(SantriPaymentConfirmation::query()->count())->toBe(0);
});

test('wali santri can print linked invoice receipt', function () {
    $wali = tenantUser('Wali Santri');

    $room = Room::factory()->forTenant($wali->tenant)->create(['name' => 'Asrama Print']);
    $linkedSantri = Santri::factory()->forTenant($wali->tenant)->create([
        'nis' => 'WALI-PRINT',
        'full_name' => 'Hasan Anak Wali',
        'room_id' => $room->id,
    ]);

    $wali->guardianSantris()->attach($linkedSantri->id, [
        'tenant_id' => $wali->tenant_id,
        'relationship' => 'Ayah',
    ]);

    $invoice = SantriInvoice::factory()->forSantri($linkedSantri)->create([
        'invoice_number' => 'INV-WALI-PRINT',
        'title' => 'Kwitansi SPP Wali',
        'amount' => 60000000,
        'paid_amount' => 60000000,
        'status' => SantriInvoice::STATUS_PAID,
        'notes' => 'Lunas untuk periode berjalan.',
    ]);

    SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 60000000,
        'payment_method' => 'qris',
        'reference_number' => 'QRIS-WALI-PRINT',
        'note' => 'Pembayaran penuh',
        'paid_at' => now()->subDay(),
    ]);

    $response = $this
        ->actingAs($wali)
        ->get(route('wali-santri.invoices.receipt', $invoice));

    $response->assertOk();
    $response->assertSee('Bukti Pembayaran / Kwitansi');
    $response->assertSee('INV-WALI-PRINT');
    $response->assertSee('Kwitansi SPP Wali');
    $response->assertSee('Hasan Anak Wali');
    $response->assertSee('Asrama Print');
    $response->assertSee('Rp 600.000');
    $response->assertSee('Lunas');
    $response->assertSee('Qris');
    $response->assertSee('QRIS-WALI-PRINT');
    $response->assertSee('Pembayaran penuh');
    $response->assertSee('window.print()', false);
});

test('wali santri can not view invoice detail for unlinked santri', function () {
    $wali = tenantUser('Wali Santri');
    $linkedSantri = Santri::factory()->forTenant($wali->tenant)->create();
    $unlinkedSantri = Santri::factory()->forTenant($wali->tenant)->create();

    $wali->guardianSantris()->attach($linkedSantri->id, [
        'tenant_id' => $wali->tenant_id,
        'relationship' => 'Ayah',
    ]);

    $invoice = SantriInvoice::factory()->forSantri($unlinkedSantri)->create([
        'title' => 'Tagihan Santri Lain',
        'amount' => 25000000,
    ]);

    $this
        ->actingAs($wali)
        ->get(route('wali-santri.invoices.show', $invoice))
        ->assertNotFound();
});

test('wali santri can not print receipt for unlinked santri invoice', function () {
    $wali = tenantUser('Wali Santri');
    $linkedSantri = Santri::factory()->forTenant($wali->tenant)->create();
    $unlinkedSantri = Santri::factory()->forTenant($wali->tenant)->create();

    $wali->guardianSantris()->attach($linkedSantri->id, [
        'tenant_id' => $wali->tenant_id,
        'relationship' => 'Ayah',
    ]);

    $invoice = SantriInvoice::factory()->forSantri($unlinkedSantri)->create([
        'title' => 'Kwitansi Santri Lain',
        'amount' => 25000000,
    ]);

    $this
        ->actingAs($wali)
        ->get(route('wali-santri.invoices.receipt', $invoice))
        ->assertNotFound();
});

test('admin can attach santri to wali account from user detail', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view user details', 'update users']);
    $wali = User::factory()->forTenant($admin->tenant)->create([
        'name' => 'Wali Portal',
    ]);
    $wali->assignRole('Wali Santri');
    $santri = Santri::factory()->forTenant($admin->tenant)->create([
        'full_name' => 'Santri Tertaut',
    ]);

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update-guardian-santri', $wali), [
            'relationship' => 'Ibu',
            'santri_ids' => [$santri->id],
        ]);

    $response->assertRedirect(route('admin.users.show', $wali, absolute: false));
    $this->assertDatabaseHas('santri_guardians', [
        'tenant_id' => $admin->tenant_id,
        'user_id' => $wali->id,
        'santri_id' => $santri->id,
        'relationship' => 'Ibu',
    ]);
});

test('admin can not attach wali account to santri from another tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view user details', 'update users']);
    $wali = User::factory()->forTenant($admin->tenant)->create();
    $wali->assignRole('Wali Santri');
    $otherTenant = Tenant::factory()->activeSubscription()->create();
    $otherSantri = Santri::factory()->forTenant($otherTenant)->create();

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.users.show', $wali))
        ->patch(route('admin.users.update-guardian-santri', $wali), [
            'relationship' => 'Wali',
            'santri_ids' => [$otherSantri->id],
        ]);

    $response->assertRedirect(route('admin.users.show', $wali, absolute: false));
    $response->assertSessionHasErrors('santri_ids', null, 'guardianSantri');
    $this->assertDatabaseMissing('santri_guardians', [
        'user_id' => $wali->id,
        'santri_id' => $otherSantri->id,
    ]);
});
