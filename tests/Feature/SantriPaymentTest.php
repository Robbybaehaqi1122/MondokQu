<?php

use App\Http\Controllers\SantriPaymentController;
use App\Http\Requests\Santri\UpdateSantriInvoiceRequest;
use App\Models\Santri;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use App\Models\Tenant;
use App\Services\ActivityLogger;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Bendahara', 'web');

    Permission::findOrCreate('view pembayaran', 'web');
    Permission::findOrCreate('create pembayaran', 'web');
    Permission::findOrCreate('update pembayaran', 'web');
    Permission::findOrCreate('edit historical pembayaran', 'web');
    Permission::findOrCreate('view laporan keuangan', 'web');
});

test('admin can access santri payment overview', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('view pembayaran');

    $response = $this->actingAs($admin)->get(route('santri.payments.index'));

    $response->assertOk();
    $response->assertSee('Pembayaran Santri');
});

test('admin can access santri invoice page', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('view pembayaran');

    $response = $this->actingAs($admin)->get(route('santri.payments.invoices'));

    $response->assertOk();
    $response->assertSee('Tagihan Santri');
});

test('admin can export filtered invoice list scoped to current tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('view pembayaran');
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    $santri = Santri::factory()->forTenant($admin->tenant)->create([
        'nis' => 'INVEXP001',
        'full_name' => 'Invoice Export',
    ]);
    SantriInvoice::factory()->forSantri($santri)->create([
        'invoice_number' => 'INV-FILTER-001',
        'title' => 'SPP Export Tunggakan',
        'amount' => 500000,
        'paid_amount' => 125000,
        'status' => SantriInvoice::STATUS_PARTIAL,
        'due_date' => now()->subDays(3),
    ]);
    SantriInvoice::factory()->forSantri($santri)->create([
        'invoice_number' => 'INV-FILTER-PAID',
        'title' => 'SPP Export Lunas',
        'amount' => 300000,
        'paid_amount' => 300000,
        'status' => SantriInvoice::STATUS_PAID,
        'due_date' => now()->subDays(3),
    ]);

    $otherSantri = Santri::factory()->forTenant($otherTenant)->create([
        'full_name' => 'Invoice Tenant Lain',
    ]);
    SantriInvoice::factory()->forSantri($otherSantri)->create([
        'invoice_number' => 'INV-FILTER-OTHER',
        'title' => 'SPP Tenant Lain',
        'status' => SantriInvoice::STATUS_PARTIAL,
        'due_date' => now()->subDays(3),
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('santri.payments.invoices.export', [
            'q' => 'Export',
            'status' => 'overdue',
            'santri' => $santri->id,
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $csv = $response->streamedContent();

    expect($csv)->toContain('INV-FILTER-001');
    expect($csv)->toContain('Invoice Export');
    expect($csv)->toContain('375000.00');
    expect($csv)->not->toContain('INV-FILTER-PAID');
    expect($csv)->not->toContain('INV-FILTER-OTHER');
    expect($csv)->not->toContain('Invoice Tenant Lain');
});

test('admin can access santri payment reports page', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('view laporan keuangan');

    $response = $this->actingAs($admin)->get(route('santri.payments.reports'));

    $response->assertOk();
    $response->assertSee('Laporan Bendahara');
});

test('admin can export payment report scoped to date range and tenant', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo('view laporan keuangan');
    $otherTenant = Tenant::factory()->activeSubscription()->create();

    $santri = Santri::factory()->forTenant($admin->tenant)->create([
        'nis' => 'PAYEXP001',
        'full_name' => 'Pembayaran Export',
    ]);
    $invoice = SantriInvoice::factory()->forSantri($santri)->create([
        'invoice_number' => 'INV-EXPORT-001',
        'title' => 'SPP Export Mei',
    ]);
    SantriPayment::factory()->forInvoice($invoice)->create([
        'paid_at' => '2026-05-05 10:15:00',
        'amount' => 125000,
        'payment_method' => 'qris',
        'recorded_by' => $admin->id,
    ]);

    $oldInvoice = SantriInvoice::factory()->forSantri($santri)->create([
        'invoice_number' => 'INV-EXPORT-OLD',
    ]);
    SantriPayment::factory()->forInvoice($oldInvoice)->create([
        'paid_at' => '2026-04-25 10:15:00',
        'amount' => 50000,
    ]);

    $otherSantri = Santri::factory()->forTenant($otherTenant)->create([
        'full_name' => 'Pembayaran Tenant Lain',
    ]);
    $otherInvoice = SantriInvoice::factory()->forSantri($otherSantri)->create([
        'invoice_number' => 'INV-EXPORT-OTHER',
    ]);
    SantriPayment::factory()->forInvoice($otherInvoice)->create([
        'paid_at' => '2026-05-06 10:15:00',
        'amount' => 999000,
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('santri.payments.reports.export', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $csv = $response->streamedContent();

    expect($csv)->toContain('INV-EXPORT-001');
    expect($csv)->toContain('Pembayaran Export');
    expect($csv)->toContain('125000.00');
    expect($csv)->not->toContain('INV-EXPORT-OLD');
    expect($csv)->not->toContain('INV-EXPORT-OTHER');
    expect($csv)->not->toContain('Pembayaran Tenant Lain');
});

test('bendahara can open payment module without santri management permission', function () {
    $bendahara = tenantUser('Bendahara');
    $bendahara->givePermissionTo('view pembayaran');

    $response = $this->actingAs($bendahara)->get(route('santri.payments.index'));

    $response->assertOk();
    $response->assertSee('Pembayaran Santri');
    expect($bendahara->can('view santri'))->toBeFalse();
});

test('admin can create a santri invoice', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view pembayaran', 'create pembayaran']);
    $santri = Santri::factory()->forTenant($admin->tenant)->create([
        'full_name' => 'Ahmad Pembayaran',
    ]);

    $response = $this->actingAs($admin)->post(route('santri.payments.invoices.store'), [
        'santri_id' => $santri->id,
        'title' => 'SPP Mei',
        'period_month' => 5,
        'period_year' => 2026,
        'due_date' => '2026-05-20',
        'amount' => 350000,
        'notes' => 'Tagihan SPP bulan Mei.',
    ]);

    $response->assertRedirect(route('santri.payments.invoices', absolute: false));

    $invoice = SantriInvoice::query()->where('santri_id', $santri->id)->first();
    expect($invoice)->not->toBeNull();
    expect($invoice?->tenant_id)->toBe($admin->tenant_id);
    expect($invoice?->title)->toBe('SPP Mei');
    expect((float) $invoice?->amount)->toBe(350000.0);
    expect($invoice?->status)->toBe(SantriInvoice::STATUS_PENDING);
});

test('admin can record partial and full payments for an invoice', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view pembayaran', 'create pembayaran']);
    $santri = Santri::factory()->forTenant($admin->tenant)->create();
    $invoice = SantriInvoice::factory()->forSantri($santri)->create([
        'amount' => 100000,
        'paid_amount' => 0,
        'status' => SantriInvoice::STATUS_PENDING,
    ]);

    $this->actingAs($admin)->post(route('santri.payments.payments.store', $invoice), [
        'amount' => 40000,
        'paid_at' => now()->format('Y-m-d H:i:s'),
        'payment_method' => 'cash',
        'reference_number' => 'CASH-001',
        'note' => 'Bayar sebagian.',
    ])->assertRedirect(route('santri.payments.invoices', absolute: false));

    $invoice->refresh();
    expect((float) $invoice->paid_amount)->toBe(40000.0);
    expect($invoice->status)->toBe(SantriInvoice::STATUS_PARTIAL);

    $this->actingAs($admin)->post(route('santri.payments.payments.store', $invoice), [
        'amount' => 60000,
        'paid_at' => now()->format('Y-m-d H:i:s'),
        'payment_method' => 'qris',
    ])->assertRedirect(route('santri.payments.invoices', absolute: false));

    $invoice->refresh();
    expect((float) $invoice->paid_amount)->toBe(100000.0);
    expect($invoice->status)->toBe(SantriInvoice::STATUS_PAID);
    expect(SantriPayment::query()->where('santri_invoice_id', $invoice->id)->count())->toBe(2);
});

test('payment amount can not exceed invoice outstanding amount', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view pembayaran', 'create pembayaran']);
    $santri = Santri::factory()->forTenant($admin->tenant)->create();
    $invoice = SantriInvoice::factory()->forSantri($santri)->create([
        'amount' => 100000,
        'paid_amount' => 25000,
        'status' => SantriInvoice::STATUS_PARTIAL,
    ]);

    $response = $this->actingAs($admin)->post(route('santri.payments.payments.store', $invoice), [
        'amount' => 80000,
        'paid_at' => now()->format('Y-m-d H:i:s'),
        'payment_method' => 'cash',
        'paying_invoice_id' => $invoice->id,
    ]);

    $response->assertSessionHasErrors('amount', null, 'recordPayment');
    expect(SantriPayment::query()->where('santri_invoice_id', $invoice->id)->exists())->toBeFalse();
});

test('payment store rechecks outstanding amount inside a locked transaction', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view pembayaran', 'create pembayaran']);
    $santri = Santri::factory()->forTenant($admin->tenant)->create();
    $invoice = SantriInvoice::factory()->forSantri($santri)->create([
        'amount' => 100000,
        'paid_amount' => 0,
        'status' => SantriInvoice::STATUS_PENDING,
    ]);

    SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 80000,
    ]);

    $response = $this->actingAs($admin)->post(route('santri.payments.payments.store', $invoice), [
        'amount' => 30000,
        'paid_at' => now()->format('Y-m-d H:i:s'),
        'payment_method' => 'cash',
        'paying_invoice_id' => $invoice->id,
    ]);

    $response->assertSessionHasErrors('amount', null, 'recordPayment');
    expect(SantriPayment::query()->where('santri_invoice_id', $invoice->id)->count())->toBe(1);
});

test('admin can update an invoice before or after payment within paid amount rules', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view pembayaran', 'update pembayaran']);
    $santri = Santri::factory()->forTenant($admin->tenant)->create();
    $invoice = SantriInvoice::factory()->forSantri($santri)->create([
        'title' => 'SPP Lama',
        'amount' => 100000,
    ]);

    SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 40000,
    ]);
    $invoice->refreshPaymentStatus();

    $response = $this->actingAs($admin)->patch(route('santri.payments.invoices.update', $invoice), [
        'santri_id' => $santri->id,
        'title' => 'SPP Diperbarui',
        'period_month' => 5,
        'period_year' => 2026,
        'due_date' => '2026-05-25',
        'amount' => 120000,
        'notes' => 'Nominal disesuaikan.',
        'editing_invoice_id' => $invoice->id,
    ]);

    $response->assertRedirect(route('santri.payments.invoices', absolute: false));

    $invoice->refresh();
    expect($invoice->title)->toBe('SPP Diperbarui');
    expect((float) $invoice->amount)->toBe(120000.0);
    expect((float) $invoice->paid_amount)->toBe(40000.0);
    expect($invoice->status)->toBe(SantriInvoice::STATUS_PARTIAL);
});

test('invoice amount can not be reduced below recorded payments', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view pembayaran', 'update pembayaran']);
    $santri = Santri::factory()->forTenant($admin->tenant)->create();
    $invoice = SantriInvoice::factory()->forSantri($santri)->create([
        'amount' => 100000,
    ]);
    SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 60000,
    ]);
    $invoice->refreshPaymentStatus();

    $response = $this->actingAs($admin)->patch(route('santri.payments.invoices.update', $invoice), [
        'santri_id' => $santri->id,
        'title' => $invoice->title,
        'period_month' => $invoice->period_month,
        'period_year' => $invoice->period_year,
        'due_date' => $invoice->due_date->toDateString(),
        'amount' => 50000,
        'editing_invoice_id' => $invoice->id,
    ]);

    $response->assertSessionHasErrors('amount', null, 'updateInvoice');
    expect((float) $invoice->fresh()->amount)->toBe(100000.0);
});

test('invoice update rechecks paid amount inside a locked transaction', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view pembayaran', 'update pembayaran']);
    $santri = Santri::factory()->forTenant($admin->tenant)->create();
    $invoice = SantriInvoice::factory()->forSantri($santri)->create([
        'amount' => 100000,
        'paid_amount' => 0,
        'status' => SantriInvoice::STATUS_PENDING,
    ]);

    SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 80000,
    ]);

    $request = Mockery::mock(UpdateSantriInvoiceRequest::class);
    $request->shouldReceive('validated')->once()->andReturn([
        'santri_id' => $santri->id,
        'title' => $invoice->title,
        'period_month' => $invoice->period_month,
        'period_year' => $invoice->period_year,
        'due_date' => $invoice->due_date->toDateString(),
        'amount' => 75000,
        'notes' => $invoice->notes,
    ]);
    $request->shouldReceive('user')->once()->andReturn($admin);

    $this->actingAs($admin);

    try {
        (new SantriPaymentController(new ActivityLogger))->updateInvoice($request, $invoice);

        $this->fail('Expected invoice update validation to fail.');
    } catch (ValidationException $exception) {
        expect($exception->errorBag)->toBe('updateInvoice');
        expect($exception->errors())->toHaveKey('amount');
    }

    expect((float) $invoice->fresh()->amount)->toBe(100000.0);
});

test('admin can delete unpaid invoice but not invoice with payment', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view pembayaran', 'update pembayaran']);
    $santri = Santri::factory()->forTenant($admin->tenant)->create();
    $unpaidInvoice = SantriInvoice::factory()->forSantri($santri)->create();
    $paidInvoice = SantriInvoice::factory()->forSantri($santri)->create();
    SantriPayment::factory()->forInvoice($paidInvoice)->create([
        'amount' => 1000,
    ]);

    $this->actingAs($admin)
        ->delete(route('santri.payments.invoices.destroy', $unpaidInvoice))
        ->assertRedirect(route('santri.payments.invoices', absolute: false));

    expect(SantriInvoice::query()->whereKey($unpaidInvoice->id)->exists())->toBeFalse();

    $this->actingAs($admin)
        ->delete(route('santri.payments.invoices.destroy', $paidInvoice))
        ->assertRedirect(route('santri.payments.invoices', absolute: false));

    expect(SantriInvoice::query()->whereKey($paidInvoice->id)->exists())->toBeTrue();
});

test('historical payment can be corrected and deleted with dedicated permission', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view pembayaran', 'edit historical pembayaran']);
    $santri = Santri::factory()->forTenant($admin->tenant)->create();
    $invoice = SantriInvoice::factory()->forSantri($santri)->create([
        'amount' => 100000,
        'paid_amount' => 0,
        'status' => SantriInvoice::STATUS_PENDING,
    ]);
    $payment = SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 100000,
        'payment_method' => 'cash',
    ]);
    $invoice->refreshPaymentStatus();

    $this->actingAs($admin)->patch(route('santri.payments.payments.update', $payment), [
        'amount' => 45000,
        'paid_at' => now()->format('Y-m-d H:i:s'),
        'payment_method' => 'qris',
        'reference_number' => 'QR-001',
        'note' => 'Koreksi nominal.',
        'editing_payment_id' => $payment->id,
        'editing_payment_invoice_id' => $invoice->id,
    ])->assertRedirect(route('santri.payments.invoices', absolute: false));

    $invoice->refresh();
    $payment->refresh();
    expect((float) $payment->amount)->toBe(45000.0);
    expect($payment->payment_method)->toBe('qris');
    expect((float) $invoice->paid_amount)->toBe(45000.0);
    expect($invoice->status)->toBe(SantriInvoice::STATUS_PARTIAL);

    $this->actingAs($admin)
        ->delete(route('santri.payments.payments.destroy', $payment))
        ->assertRedirect(route('santri.payments.invoices', absolute: false));

    $invoice->refresh();
    expect(SantriPayment::query()->whereKey($payment->id)->exists())->toBeFalse();
    expect((float) $invoice->paid_amount)->toBe(0.0);
    expect($invoice->status)->toBe(SantriInvoice::STATUS_PENDING);
});

test('payment update rechecks invoice total inside a locked transaction', function () {
    $admin = tenantUser('Admin');
    $admin->givePermissionTo(['view pembayaran', 'edit historical pembayaran']);
    $santri = Santri::factory()->forTenant($admin->tenant)->create();
    $invoice = SantriInvoice::factory()->forSantri($santri)->create([
        'amount' => 100000,
        'paid_amount' => 0,
        'status' => SantriInvoice::STATUS_PENDING,
    ]);
    $payment = SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 20000,
    ]);
    SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 90000,
    ]);

    $response = $this->actingAs($admin)->patch(route('santri.payments.payments.update', $payment), [
        'amount' => 30000,
        'paid_at' => now()->format('Y-m-d H:i:s'),
        'payment_method' => 'qris',
        'editing_payment_id' => $payment->id,
        'editing_payment_invoice_id' => $invoice->id,
    ]);

    $response->assertSessionHasErrors('amount', null, 'updatePayment');
    expect((float) $payment->fresh()->amount)->toBe(20000.0);
});

test('database rejects invalid invoice balances', function () {
    $admin = tenantUser('Admin');
    $santri = Santri::factory()->forTenant($admin->tenant)->create();

    expect(fn () => SantriInvoice::factory()->forSantri($santri)->create([
        'amount' => 100000,
        'paid_amount' => 125000,
    ]))->toThrow(QueryException::class);

    expect(fn () => SantriInvoice::factory()->forSantri($santri)->create([
        'amount' => 0,
        'paid_amount' => 0,
    ]))->toThrow(QueryException::class);

    expect(fn () => SantriInvoice::factory()->forSantri($santri)->create([
        'amount' => 100000,
        'paid_amount' => -1000,
    ]))->toThrow(QueryException::class);

    $invoice = SantriInvoice::factory()->forSantri($santri)->create([
        'amount' => 100000,
        'paid_amount' => 50000,
        'status' => SantriInvoice::STATUS_PARTIAL,
    ]);

    expect(fn () => $invoice->forceFill(['amount' => 40000])->save())
        ->toThrow(QueryException::class);
});

test('database rejects non positive payment amounts', function () {
    $admin = tenantUser('Admin');
    $santri = Santri::factory()->forTenant($admin->tenant)->create();
    $invoice = SantriInvoice::factory()->forSantri($santri)->create();

    expect(fn () => SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 0,
    ]))->toThrow(QueryException::class);

    expect(fn () => SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => -1000,
    ]))->toThrow(QueryException::class);
});

test('invoice payment refresh refuses overpaid ledgers', function () {
    $admin = tenantUser('Admin');
    $santri = Santri::factory()->forTenant($admin->tenant)->create();
    $invoice = SantriInvoice::factory()->forSantri($santri)->create([
        'amount' => 100000,
        'paid_amount' => 0,
        'status' => SantriInvoice::STATUS_PENDING,
    ]);

    SantriPayment::factory()->forInvoice($invoice)->create([
        'amount' => 125000,
    ]);

    $this->actingAs($admin);

    expect(fn () => $invoice->refreshPaymentStatus())
        ->toThrow(DomainException::class, 'Total pembayaran tidak boleh melebihi nominal tagihan.');
});
