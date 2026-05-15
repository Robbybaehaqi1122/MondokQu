<?php

namespace App\Http\Controllers;

use App\Http\Requests\Santri\StoreSantriInvoiceRequest;
use App\Http\Requests\Santri\StoreSantriPaymentRequest;
use App\Http\Requests\Santri\UpdateSantriInvoiceRequest;
use App\Http\Requests\Santri\UpdateSantriPaymentRequest;
use App\Models\Santri;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SantriPaymentController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Display the santri payment module overview.
     */
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $invoiceBaseQuery = SantriInvoice::query()->visibleTo($currentUser);
        $paymentBaseQuery = SantriPayment::query()->visibleTo($currentUser);

        return view('santri.payments.index', [
            'summary' => $this->buildInvoiceSummary(clone $invoiceBaseQuery),
            'paidThisMonth' => (clone $paymentBaseQuery)
                ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'),
            'recentInvoices' => (clone $invoiceBaseQuery)
                ->with('santri')
                ->latest()
                ->limit(5)
                ->get(),
            'recentPayments' => (clone $paymentBaseQuery)
                ->with(['invoice', 'santri', 'recorder'])
                ->latest('paid_at')
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * Display and manage the santri invoice list.
     */
    public function invoices(Request $request): View
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));
        $selectedStatus = trim((string) $request->string('status'));
        $selectedSantriId = trim((string) $request->string('santri'));

        $baseQuery = SantriInvoice::query()->visibleTo($currentUser);

        $invoices = (clone $baseQuery)
            ->with(['santri', 'payments.recorder'])
            ->tap(fn ($builder) => $this->applyInvoiceFilters($builder, $search, $selectedStatus, $selectedSantriId))
            ->orderByRaw('CASE WHEN status = ? THEN 1 ELSE 0 END', [SantriInvoice::STATUS_PAID])
            ->orderBy('due_date')
            ->paginate(10)
            ->withQueryString();

        return view('santri.payments.invoices', [
            'filters' => [
                'q' => $search,
                'status' => $selectedStatus,
                'santri' => $selectedSantriId,
            ],
            'summary' => $this->buildInvoiceSummary(clone $baseQuery),
            'invoices' => $invoices,
            'paymentMethods' => SantriPayment::paymentMethods(),
            'santris' => Santri::query()
                ->visibleTo($currentUser)
                ->orderBy('full_name')
                ->limit(250)
                ->get(),
            'statusOptions' => $this->invoiceStatusOptions(),
            'canCreateInvoice' => $currentUser?->can('create pembayaran') ?? false,
            'canRecordPayment' => $currentUser?->can('create pembayaran') ?? false,
            'canUpdateInvoice' => $currentUser?->can('update pembayaran') ?? false,
            'canEditHistoricalPayments' => $currentUser?->can('edit historical pembayaran') ?? false,
        ]);
    }

    /**
     * Export filtered invoice list as CSV.
     */
    public function exportInvoices(Request $request): StreamedResponse
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));
        $selectedStatus = trim((string) $request->string('status'));
        $selectedSantriId = trim((string) $request->string('santri'));
        $filename = 'tagihan-santri-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($currentUser, $search, $selectedStatus, $selectedSantriId): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Nomor Invoice',
                'Judul Tagihan',
                'Nama Santri',
                'NIS',
                'Periode Bulan',
                'Periode Tahun',
                'Jatuh Tempo',
                'Nominal',
                'Terbayar',
                'Sisa Tagihan',
                'Status',
                'Catatan',
            ]);

            SantriInvoice::query()
                ->visibleTo($currentUser)
                ->with('santri')
                ->tap(fn ($builder) => $this->applyInvoiceFilters($builder, $search, $selectedStatus, $selectedSantriId))
                ->orderByRaw('CASE WHEN status = ? THEN 1 ELSE 0 END', [SantriInvoice::STATUS_PAID])
                ->orderBy('due_date')
                ->chunk(500, function ($invoices) use ($handle): void {
                    foreach ($invoices as $invoice) {
                        fputcsv($handle, [
                            $invoice->invoice_number,
                            $invoice->title,
                            $invoice->santri?->full_name,
                            $invoice->santri?->nis,
                            $invoice->period_month,
                            $invoice->period_year,
                            $invoice->due_date?->toDateString(),
                            number_format((float) $invoice->amount, 2, '.', ''),
                            number_format((float) $invoice->paid_amount, 2, '.', ''),
                            number_format($invoice->outstandingAmount(), 2, '.', ''),
                            $invoice->statusLabel(),
                            $invoice->notes,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Store a newly created santri invoice.
     */
    public function storeInvoice(StoreSantriInvoiceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $currentUser = $request->user();
        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->findOrFail($validated['santri_id']);

        $invoice = SantriInvoice::query()->create([
            'tenant_id' => $santri->tenant_id,
            'santri_id' => $santri->id,
            'invoice_number' => $this->generateInvoiceNumber($santri, $validated['period_month'] ?? null, $validated['period_year'] ?? null),
            'title' => $validated['title'],
            'period_month' => $validated['period_month'] ?? null,
            'period_year' => $validated['period_year'] ?? null,
            'due_date' => $validated['due_date'],
            'amount' => $validated['amount'],
            'paid_amount' => 0,
            'status' => SantriInvoice::STATUS_PENDING,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $currentUser?->id,
        ]);

        $this->activityLogger->log(
            action: 'santri_invoice_created',
            actor: $currentUser,
            target: $invoice,
            description: 'Tagihan santri baru dibuat.',
            properties: [
                'target_name' => $invoice->invoice_number.' - '.$santri->full_name,
                'santri_id' => $santri->id,
                'amount' => $invoice->amount,
                'due_date' => $invoice->due_date?->toDateString(),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('santri.payments.invoices')
            ->with('success', 'Tagihan santri berhasil dibuat.');
    }

    /**
     * Update the selected santri invoice.
     */
    public function updateInvoice(UpdateSantriInvoiceRequest $request, SantriInvoice $invoice): RedirectResponse
    {
        $validated = $request->validated();
        $currentUser = $request->user();

        [$invoice, $santri, $previousValues] = DB::transaction(function () use ($currentUser, $invoice, $validated): array {
            $lockedInvoice = SantriInvoice::query()
                ->visibleTo($currentUser)
                ->lockForUpdate()
                ->findOrFail($invoice->id);

            $santri = Santri::query()
                ->visibleTo($currentUser)
                ->where('tenant_id', $lockedInvoice->tenant_id)
                ->findOrFail($validated['santri_id']);

            $paidAmount = (float) $lockedInvoice->payments()->sum('amount');

            if ((float) $validated['amount'] < $paidAmount) {
                throw $this->invoiceValidationException(
                    'amount',
                    'Nominal tagihan tidak boleh lebih kecil dari total pembayaran yang sudah dicatat.'
                );
            }

            if ((int) $santri->id !== (int) $lockedInvoice->santri_id && $paidAmount > 0) {
                throw $this->invoiceValidationException(
                    'santri_id',
                    'Santri pada tagihan yang sudah memiliki pembayaran tidak dapat diganti.'
                );
            }

            $previousValues = $lockedInvoice->only([
                'santri_id',
                'title',
                'period_month',
                'period_year',
                'due_date',
                'amount',
                'notes',
                'status',
                'paid_amount',
            ]);

            $lockedInvoice->forceFill([
                'santri_id' => $santri->id,
                'title' => $validated['title'],
                'period_month' => $validated['period_month'] ?? null,
                'period_year' => $validated['period_year'] ?? null,
                'due_date' => $validated['due_date'],
                'amount' => $validated['amount'],
                'notes' => $validated['notes'] ?? null,
            ])->save();

            $lockedInvoice->refreshPaymentStatus();

            return [$lockedInvoice->fresh(['santri']), $santri, $previousValues];
        });

        $this->activityLogger->log(
            action: 'santri_invoice_updated',
            actor: $currentUser,
            target: $invoice,
            description: 'Tagihan santri diperbarui.',
            properties: [
                'target_name' => $invoice->invoice_number.' - '.$santri->full_name,
                'before' => $previousValues,
                'after' => $invoice->fresh()?->only([
                    'santri_id',
                    'title',
                    'period_month',
                    'period_year',
                    'due_date',
                    'amount',
                    'notes',
                    'status',
                    'paid_amount',
                ]),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('santri.payments.invoices')
            ->with('success', 'Tagihan santri berhasil diperbarui.');
    }

    /**
     * Delete an unpaid santri invoice.
     */
    public function destroyInvoice(Request $request, SantriInvoice $invoice): RedirectResponse
    {
        $currentUser = $request->user();
        $invoice = SantriInvoice::query()
            ->visibleTo($currentUser)
            ->with(['santri'])
            ->withCount('payments')
            ->findOrFail($invoice->id);

        if ($invoice->payments_count > 0) {
            return redirect()
                ->route('santri.payments.invoices')
                ->with('error', 'Tagihan yang sudah memiliki pembayaran tidak dapat dihapus. Gunakan koreksi pembayaran jika perlu.');
        }

        $this->activityLogger->log(
            action: 'santri_invoice_deleted',
            actor: $currentUser,
            target: $invoice,
            description: 'Tagihan santri tanpa pembayaran dihapus.',
            properties: [
                'target_name' => $invoice->invoice_number.' - '.$invoice->santri?->full_name,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $invoice->amount,
                'due_date' => $invoice->due_date?->toDateString(),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $invoice->delete();

        return redirect()
            ->route('santri.payments.invoices')
            ->with('success', 'Tagihan santri berhasil dihapus.');
    }

    /**
     * Store a payment for the selected santri invoice.
     */
    public function storePayment(StoreSantriPaymentRequest $request, SantriInvoice $invoice): RedirectResponse
    {
        $validated = $request->validated();
        $currentUser = $request->user();

        [$invoice, $payment] = DB::transaction(function () use ($currentUser, $invoice, $validated): array {
            $lockedInvoice = SantriInvoice::query()
                ->visibleTo($currentUser)
                ->with('santri')
                ->lockForUpdate()
                ->findOrFail($invoice->id);

            $recordedTotal = (float) $lockedInvoice->payments()->sum('amount');
            $maxAllowedAmount = max(0, (float) $lockedInvoice->amount - $recordedTotal);

            if ((float) $validated['amount'] > $maxAllowedAmount) {
                throw $this->paymentValidationException(
                    'Nominal pembayaran melebihi sisa tagihan.',
                    'recordPayment'
                );
            }

            $payment = SantriPayment::query()->create([
                'tenant_id' => $lockedInvoice->tenant_id,
                'santri_invoice_id' => $lockedInvoice->id,
                'santri_id' => $lockedInvoice->santri_id,
                'paid_at' => Carbon::parse($validated['paid_at']),
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'note' => $validated['note'] ?? null,
                'recorded_by' => $currentUser?->id,
            ]);

            $lockedInvoice->refreshPaymentStatus();

            return [$lockedInvoice->fresh(['santri']), $payment];
        });

        $this->activityLogger->log(
            action: 'santri_payment_recorded',
            actor: $currentUser,
            target: $payment,
            description: 'Pembayaran tagihan santri dicatat.',
            properties: [
                'target_name' => $invoice->invoice_number.' - '.$invoice->santri?->full_name,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'invoice_status' => $invoice->fresh()?->status,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('santri.payments.invoices')
            ->with('success', 'Pembayaran santri berhasil dicatat.');
    }

    /**
     * Correct a recorded payment.
     */
    public function updatePayment(UpdateSantriPaymentRequest $request, SantriPayment $payment): RedirectResponse
    {
        $validated = $request->validated();
        $currentUser = $request->user();

        [$payment, $invoice, $previousValues] = DB::transaction(function () use ($currentUser, $payment, $validated): array {
            $lockedPayment = SantriPayment::query()
                ->visibleTo($currentUser)
                ->lockForUpdate()
                ->findOrFail($payment->id);

            $lockedInvoice = SantriInvoice::query()
                ->visibleTo($currentUser)
                ->with('santri')
                ->lockForUpdate()
                ->findOrFail($lockedPayment->santri_invoice_id);

            $previousValues = $lockedPayment->only([
                'paid_at',
                'amount',
                'payment_method',
                'reference_number',
                'note',
            ]);
            $otherPaymentTotal = (float) $lockedInvoice->payments()
                ->whereKeyNot($lockedPayment->id)
                ->sum('amount');
            $maxAllowedAmount = max(0, (float) $lockedInvoice->amount - $otherPaymentTotal);

            if ((float) $validated['amount'] > $maxAllowedAmount) {
                throw $this->paymentValidationException(
                    'Nominal koreksi melebihi sisa tagihan setelah pembayaran lain.',
                    'updatePayment'
                );
            }

            $lockedPayment->forceFill([
                'paid_at' => Carbon::parse($validated['paid_at']),
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'note' => $validated['note'] ?? null,
            ])->save();

            $lockedInvoice->refreshPaymentStatus();

            return [$lockedPayment->fresh(['invoice.santri']), $lockedInvoice->fresh(), $previousValues];
        });

        $this->activityLogger->log(
            action: 'santri_payment_corrected',
            actor: $currentUser,
            target: $payment,
            description: 'Pembayaran historis santri dikoreksi.',
            properties: [
                'target_name' => $payment->invoice?->invoice_number.' - '.$payment->invoice?->santri?->full_name,
                'invoice_id' => $payment->santri_invoice_id,
                'before' => $previousValues,
                'after' => $payment->fresh()?->only([
                    'paid_at',
                    'amount',
                    'payment_method',
                    'reference_number',
                    'note',
                ]),
                'invoice_status' => $invoice?->status,
                'invoice_paid_amount' => $invoice?->paid_amount,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('santri.payments.invoices')
            ->with('success', 'Pembayaran santri berhasil dikoreksi.');
    }

    /**
     * Delete a recorded payment and refresh invoice balance.
     */
    public function destroyPayment(Request $request, SantriPayment $payment): RedirectResponse
    {
        $currentUser = $request->user();
        $payment = SantriPayment::query()
            ->visibleTo($currentUser)
            ->with(['invoice.santri'])
            ->findOrFail($payment->id);
        $invoice = $payment->invoice;

        $this->activityLogger->log(
            action: 'santri_payment_deleted',
            actor: $currentUser,
            target: $payment,
            description: 'Pembayaran historis santri dihapus dari tagihan.',
            properties: [
                'target_name' => $invoice?->invoice_number.' - '.$invoice?->santri?->full_name,
                'invoice_id' => $payment->santri_invoice_id,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'paid_at' => $payment->paid_at?->toDateTimeString(),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        DB::transaction(function () use ($currentUser, $invoice, $payment): void {
            $lockedInvoice = $invoice
                ? SantriInvoice::query()
                    ->visibleTo($currentUser)
                    ->lockForUpdate()
                    ->findOrFail($invoice->id)
                : null;
            $lockedPayment = SantriPayment::query()
                ->visibleTo($currentUser)
                ->lockForUpdate()
                ->findOrFail($payment->id);

            $lockedPayment->delete();
            $lockedInvoice?->refreshPaymentStatus();
        });

        return redirect()
            ->route('santri.payments.invoices')
            ->with('success', 'Pembayaran santri berhasil dihapus.');
    }

    /**
     * Display payment reports.
     */
    public function reports(Request $request): View
    {
        [$dateFrom, $dateTo] = $this->reportDateRange($request);

        $currentUser = $request->user();
        $paymentBaseQuery = SantriPayment::query()
            ->visibleTo($currentUser)
            ->whereBetween('paid_at', [$dateFrom, $dateTo]);
        $invoiceBaseQuery = SantriInvoice::query()->visibleTo($currentUser);

        return view('santri.payments.reports', [
            'filters' => [
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
            ],
            'summary' => $this->buildInvoiceSummary(clone $invoiceBaseQuery),
            'reportSummary' => [
                'received' => (clone $paymentBaseQuery)->sum('amount'),
                'transactions' => (clone $paymentBaseQuery)->count(),
                'average_payment' => (clone $paymentBaseQuery)->avg('amount') ?? 0,
            ],
            'methodTotals' => (clone $paymentBaseQuery)
                ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('payment_method')
                ->orderByDesc('total')
                ->get(),
            'recentPayments' => (clone $paymentBaseQuery)
                ->with(['invoice', 'santri', 'recorder'])
                ->latest('paid_at')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    /**
     * Export filtered payment report as CSV.
     */
    public function exportReports(Request $request): StreamedResponse
    {
        [$dateFrom, $dateTo] = $this->reportDateRange($request);

        $currentUser = $request->user();
        $filename = 'laporan-pembayaran-'
            .$dateFrom->toDateString()
            .'-sd-'
            .$dateTo->toDateString()
            .'.csv';

        return response()->streamDownload(function () use ($currentUser, $dateFrom, $dateTo): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Tanggal Bayar',
                'Nomor Invoice',
                'Judul Tagihan',
                'Nama Santri',
                'NIS',
                'Metode Pembayaran',
                'Nomor Referensi',
                'Nominal',
                'Dicatat Oleh',
                'Catatan',
            ]);

            SantriPayment::query()
                ->visibleTo($currentUser)
                ->with(['invoice', 'santri', 'recorder'])
                ->whereBetween('paid_at', [$dateFrom, $dateTo])
                ->latest('paid_at')
                ->chunk(500, function ($payments) use ($handle): void {
                    foreach ($payments as $payment) {
                        fputcsv($handle, [
                            $payment->paid_at?->format('Y-m-d H:i:s'),
                            $payment->invoice?->invoice_number,
                            $payment->invoice?->title,
                            $payment->santri?->full_name,
                            $payment->santri?->nis,
                            Str::headline($payment->payment_method),
                            $payment->reference_number,
                            number_format((float) $payment->amount, 2, '.', ''),
                            $payment->recorder?->name ?? 'System',
                            $payment->note,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Build invoice summary stats.
     */
    protected function buildInvoiceSummary($query): array
    {
        $totalAmount = (clone $query)->sum('amount');
        $paidAmount = (clone $query)->sum('paid_amount');

        return [
            'total_invoices' => (clone $query)->count(),
            'paid_invoices' => (clone $query)->where('status', SantriInvoice::STATUS_PAID)->count(),
            'pending_invoices' => (clone $query)->where('status', SantriInvoice::STATUS_PENDING)->count(),
            'partial_invoices' => (clone $query)->where('status', SantriInvoice::STATUS_PARTIAL)->count(),
            'overdue_invoices' => (clone $query)
                ->where('status', '!=', SantriInvoice::STATUS_PAID)
                ->whereDate('due_date', '<', now()->toDateString())
                ->count(),
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'outstanding_amount' => max(0, (float) $totalAmount - (float) $paidAmount),
            'overdue_amount' => (clone $query)
                ->where('status', '!=', SantriInvoice::STATUS_PAID)
                ->whereDate('due_date', '<', now()->toDateString())
                ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')
                ->value('total') ?? 0,
        ];
    }

    /**
     * Generate a tenant-scoped invoice number.
     */
    protected function generateInvoiceNumber(Santri $santri, ?int $periodMonth, ?int $periodYear): string
    {
        $periodKey = $periodYear && $periodMonth
            ? sprintf('%04d%02d', $periodYear, $periodMonth)
            : now()->format('Ym');
        $prefix = 'INV-'.$periodKey.'-'.Str::padLeft((string) $santri->tenant_id, 3, '0');
        $nextNumber = SantriInvoice::query()
            ->where('tenant_id', $santri->tenant_id)
            ->where('invoice_number', 'like', $prefix.'-%')
            ->count() + 1;

        return $prefix.'-'.Str::padLeft((string) $nextNumber, 4, '0');
    }

    /**
     * Build filter options for invoice status.
     *
     * @return array<int, array{value: string, label: string}>
     */
    protected function invoiceStatusOptions(): array
    {
        return [
            ['value' => SantriInvoice::STATUS_PENDING, 'label' => 'Menunggu Bayar'],
            ['value' => SantriInvoice::STATUS_PARTIAL, 'label' => 'Sebagian'],
            ['value' => SantriInvoice::STATUS_PAID, 'label' => 'Lunas'],
            ['value' => 'overdue', 'label' => 'Tunggakan'],
        ];
    }

    protected function applyInvoiceFilters($builder, string $search, string $selectedStatus, string $selectedSantriId): void
    {
        $builder
            ->when($search !== '', function ($builder) use ($search) {
                $builder->where(function ($invoiceQuery) use ($search) {
                    $invoiceQuery
                        ->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhereHas('santri', function ($santriQuery) use ($search) {
                            $santriQuery
                                ->where('nis', 'like', "%{$search}%")
                                ->orWhere('full_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($selectedStatus !== '', function ($builder) use ($selectedStatus) {
                if ($selectedStatus === 'overdue') {
                    $builder
                        ->where('status', '!=', SantriInvoice::STATUS_PAID)
                        ->whereDate('due_date', '<', now()->toDateString());

                    return;
                }

                if (in_array($selectedStatus, SantriInvoice::availableStatuses(), true)) {
                    $builder->where('status', $selectedStatus);
                }
            })
            ->when($selectedSantriId !== '', fn ($builder) => $builder->where('santri_id', $selectedSantriId));
    }

    /**
     * Resolve and validate report date range.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function reportDateRange(Request $request): array
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = isset($validated['date_from'])
            ? Carbon::parse($validated['date_from'])->startOfDay()
            : now()->startOfMonth();
        $dateTo = isset($validated['date_to'])
            ? Carbon::parse($validated['date_to'])->endOfDay()
            : now()->endOfMonth();

        return [$dateFrom, $dateTo];
    }

    protected function paymentValidationException(string $message, string $errorBag): ValidationException
    {
        $exception = ValidationException::withMessages([
            'amount' => $message,
        ]);
        $exception->errorBag = $errorBag;

        return $exception;
    }

    protected function invoiceValidationException(string $field, string $message): ValidationException
    {
        $exception = ValidationException::withMessages([
            $field => $message,
        ]);
        $exception->errorBag = 'updateInvoice';

        return $exception;
    }
}
