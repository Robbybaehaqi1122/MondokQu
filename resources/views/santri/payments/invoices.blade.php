<x-app-layout>
    @php
        $periodYearMin = (int) config('santri.invoice.period_year_min', 2000);
        $periodYearMax = now()->year + max(1, (int) config('santri.invoice.period_year_future_limit', 5));
    @endphp

    @php
        $statusBadgeClasses = [
            'pending' => 'bg-warning-lt text-warning',
            'partial' => 'bg-azure-lt text-azure',
            'paid' => 'bg-success-lt text-success',
            'overdue' => 'bg-danger-lt text-danger',
        ];
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="page-title">Tagihan Santri</h2>
            <div class="text-secondary mt-1">Buat tagihan dan catat pembayaran santri per tenant pondok.</div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Total Tagihan</div>
                <div class="fs-2 fw-bold">{{ number_format($summary['total_invoices']) }}</div>
                <div class="text-secondary small">Rp {{ number_format($summary['total_amount'] / 100, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Lunas</div>
                <div class="fs-2 fw-bold">{{ number_format($summary['paid_invoices']) }}</div>
                <div class="text-secondary small">Rp {{ number_format($summary['paid_amount'] / 100, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Belum Lunas</div>
                <div class="fs-2 fw-bold">{{ number_format($summary['pending_invoices'] + $summary['partial_invoices']) }}</div>
                <div class="text-secondary small">Rp {{ number_format($summary['outstanding_amount'] / 100, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Tunggakan</div>
                <div class="fs-2 fw-bold">{{ number_format($summary['overdue_invoices']) }}</div>
                <div class="text-secondary small">Rp {{ number_format($summary['overdue_amount'] / 100, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    @if (session('bulk_invoice_preview'))
        @php
            $bulkPreview = session('bulk_invoice_preview');
        @endphp
        <div class="alert alert-info">
            Preview generate tagihan bulanan:
            <strong>{{ number_format($bulkPreview['created']) }}</strong> tagihan akan dibuat,
            <strong>{{ number_format($bulkPreview['skipped']) }}</strong> dilewati karena sudah ada,
            dari <strong>{{ number_format($bulkPreview['eligible']) }}</strong> santri aktif.
        </div>
    @endif

    @include('exports.partials.status-list', [
        'title' => 'Export Tagihan Terbaru',
        'dataExports' => $dataExports,
    ])

    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 w-100">
                <div>
                    <h3 class="card-title">Daftar Tagihan</h3>
                    <div class="text-secondary small mt-2">Menampilkan {{ $invoices->total() }} tagihan berdasarkan filter aktif.</div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('santri.payments.index') }}" class="btn btn-outline-secondary">Overview</a>
                    @php $exportQuery = request()->only(['q', 'status', 'santri']); @endphp
                    <div class="btn-group">
                        <a href="{{ route('santri.payments.invoices.export', array_merge($exportQuery, ['format' => 'xlsx'])) }}" class="btn btn-outline-primary">Excel</a>
                        <a href="{{ route('santri.payments.invoices.export', array_merge($exportQuery, ['format' => 'pdf'])) }}" class="btn btn-outline-primary">PDF</a>
                    </div>
                    @if ($canCreateInvoice)
                        <button
                            type="button"
                            class="btn btn-outline-primary"
                            id="open-monthly-invoice-modal"
                            data-bs-toggle="modal"
                            data-bs-target="#monthlyInvoiceModal"
                        >
                            Generate Bulanan
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            id="open-create-invoice-modal"
                            data-bs-toggle="modal"
                            data-bs-target="#createInvoiceModal"
                        >
                            Tambah Tagihan
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('santri.payments.invoices') }}" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label for="q" class="form-label">Cari Tagihan</label>
                    <input
                        id="q"
                        name="q"
                        type="text"
                        class="form-control"
                        value="{{ $filters['q'] }}"
                        placeholder="No. tagihan, nama santri, atau NIS"
                    >
                </div>
                <div class="col-md-4 col-lg-3">
                    <label for="santri_filter" class="form-label">Santri</label>
                    <select id="santri_filter" name="santri" class="form-select form-select-pretty">
                        <option value="">Semua Santri</option>
                        @foreach ($santris as $santri)
                            <option value="{{ $santri->id }}" @selected((string) $filters['santri'] === (string) $santri->id)>
                                {{ $santri->full_name }} - {{ $santri->nis }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label for="status_filter" class="form-label">Status</label>
                    <select id="status_filter" name="status" class="form-select form-select-pretty">
                        <option value="">Semua</option>
                        @foreach ($statusOptions as $statusOption)
                            <option value="{{ $statusOption['value'] }}" @selected($filters['status'] === $statusOption['value'])>
                                {{ $statusOption['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                        <a href="{{ route('santri.payments.invoices') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. Tagihan</th>
                        <th>Santri</th>
                        <th>Periode</th>
                        <th>Nominal</th>
                        <th>Terbayar</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        @php
                            $displayStatus = $invoice->isOverdue() ? 'overdue' : $invoice->status;
                            $outstandingAmount = $invoice->outstandingAmount();
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $invoice->invoice_number }}</div>
                                <div class="text-secondary small">{{ $invoice->title }}</div>
                                <div class="text-secondary small">Jatuh tempo {{ optional($invoice->due_date)->translatedFormat('d M Y') }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $invoice->santri?->full_name ?? '-' }}</div>
                                <div class="text-secondary small">NIS: {{ $invoice->santri?->nis ?? '-' }}</div>
                            </td>
                            <td>
                                @if ($invoice->period_month && $invoice->period_year)
                                    {{ str_pad((string) $invoice->period_month, 2, '0', STR_PAD_LEFT) }}/{{ $invoice->period_year }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>Rp {{ number_format($invoice->amount / 100, 0, ',', '.') }}</td>
                            <td>
                                <div>Rp {{ number_format($invoice->paid_amount / 100, 0, ',', '.') }}</div>
                                <div class="text-secondary small">Sisa Rp {{ number_format($outstandingAmount / 100, 0, ',', '.') }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $statusBadgeClasses[$displayStatus] ?? 'bg-secondary-lt text-secondary' }}">
                                    {{ $invoice->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    @if ($canRecordPayment && $outstandingAmount > 0)
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#recordPaymentModal{{ $invoice->id }}"
                                        >
                                            Catat Bayar
                                        </button>
                                    @endif

                                    @if ($canUpdateInvoice)
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editInvoiceModal{{ $invoice->id }}"
                                        >
                                            Edit
                                        </button>
                                    @endif

                                    @if ($canEditHistoricalPayments && $invoice->payments->isNotEmpty())
                                        <button
                                            type="button"
                                            class="btn btn-outline-warning btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#correctPaymentModal{{ $invoice->id }}"
                                        >
                                            Koreksi
                                        </button>
                                    @endif

                                    @if ($canUpdateInvoice && $invoice->payments->isEmpty())
                                        <form method="POST" action="{{ route('santri.payments.invoices.destroy', $invoice) }}" onsubmit="return confirm('Hapus tagihan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">Hapus</button>
                                        </form>
                                    @endif

                                    @if (! $canRecordPayment && ! $canUpdateInvoice && ! $canEditHistoricalPayments)
                                        <span class="text-secondary small">-</span>
                                    @endif
                                </div>

                                @if ($canRecordPayment && $outstandingAmount > 0)
                                    <div class="modal modal-blur fade" id="recordPaymentModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('santri.payments.payments.store', $invoice) }}">
                                                    @csrf
                                                    <input type="hidden" name="paying_invoice_id" value="{{ $invoice->id }}">

                                                    <div class="modal-header">
                                                        <div>
                                                            <h5 class="modal-title">Catat Pembayaran</h5>
                                                            <div class="text-secondary small mt-1">{{ $invoice->invoice_number }} - {{ $invoice->santri?->full_name }}</div>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label" for="amount_{{ $invoice->id }}">Nominal Bayar</label>
                                                                <input
                                                                    id="amount_{{ $invoice->id }}"
                                                                    name="amount"
                                                                    type="number"
                                                                    min="1"
                                                                    max="{{ $outstandingAmount / 100 }}"
                                                                     step="1"
                                                                     class="form-control @if(old('paying_invoice_id') == $invoice->id && $errors->recordPayment->has('amount')) is-invalid @endif"
                                                                     value="{{ old('paying_invoice_id') == $invoice->id ? old('amount') : $outstandingAmount / 100 }}"
                                                                    required
                                                                >
                                                                @if (old('paying_invoice_id') == $invoice->id && $errors->recordPayment->has('amount'))
                                                                    <div class="invalid-feedback">{{ $errors->recordPayment->first('amount') }}</div>
                                                                @else
                                                                    <div class="form-hint mt-2">Sisa tagihan Rp {{ number_format($outstandingAmount / 100, 0, ',', '.') }}.</div>
                                                                @endif
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label" for="paid_at_{{ $invoice->id }}">Tanggal Bayar</label>
                                                                <input
                                                                    id="paid_at_{{ $invoice->id }}"
                                                                    name="paid_at"
                                                                    type="datetime-local"
                                                                    class="form-control @if(old('paying_invoice_id') == $invoice->id && $errors->recordPayment->has('paid_at')) is-invalid @endif"
                                                                    value="{{ old('paying_invoice_id') == $invoice->id ? old('paid_at') : now()->format('Y-m-d\TH:i') }}"
                                                                    required
                                                                >
                                                                @if (old('paying_invoice_id') == $invoice->id && $errors->recordPayment->has('paid_at'))
                                                                    <div class="invalid-feedback">{{ $errors->recordPayment->first('paid_at') }}</div>
                                                                @endif
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label" for="payment_method_{{ $invoice->id }}">Metode Bayar</label>
                                                                <select
                                                                    id="payment_method_{{ $invoice->id }}"
                                                                    name="payment_method"
                                                                    class="form-select @if(old('paying_invoice_id') == $invoice->id && $errors->recordPayment->has('payment_method')) is-invalid @endif"
                                                                    required
                                                                >
                                                                    <option value="">Pilih metode bayar</option>
                                                                    @foreach ($paymentMethods as $method)
                                                                        <option value="{{ $method }}" @selected((old('paying_invoice_id') == $invoice->id ? old('payment_method') : '') === $method)>
                                                                            {{ str($method)->headline() }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @if (old('paying_invoice_id') == $invoice->id && $errors->recordPayment->has('payment_method'))
                                                                    <div class="invalid-feedback">{{ $errors->recordPayment->first('payment_method') }}</div>
                                                                @endif
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label" for="reference_number_{{ $invoice->id }}">No. Referensi</label>
                                                                <input
                                                                    id="reference_number_{{ $invoice->id }}"
                                                                    name="reference_number"
                                                                    type="text"
                                                                    class="form-control @if(old('paying_invoice_id') == $invoice->id && $errors->recordPayment->has('reference_number')) is-invalid @endif"
                                                                    value="{{ old('paying_invoice_id') == $invoice->id ? old('reference_number') : '' }}"
                                                                    placeholder="Opsional"
                                                                >
                                                                @if (old('paying_invoice_id') == $invoice->id && $errors->recordPayment->has('reference_number'))
                                                                    <div class="invalid-feedback">{{ $errors->recordPayment->first('reference_number') }}</div>
                                                                @endif
                                                            </div>

                                                            <div class="col-12">
                                                                <label class="form-label" for="note_{{ $invoice->id }}">Catatan</label>
                                                                <textarea
                                                                    id="note_{{ $invoice->id }}"
                                                                    name="note"
                                                                    rows="3"
                                                                    class="form-control @if(old('paying_invoice_id') == $invoice->id && $errors->recordPayment->has('note')) is-invalid @endif"
                                                                    placeholder="Opsional"
                                                                >{{ old('paying_invoice_id') == $invoice->id ? old('note') : '' }}</textarea>
                                                                @if (old('paying_invoice_id') == $invoice->id && $errors->recordPayment->has('note'))
                                                                    <div class="invalid-feedback">{{ $errors->recordPayment->first('note') }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if ($canUpdateInvoice)
                                    <div class="modal modal-blur fade" id="editInvoiceModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('santri.payments.invoices.update', $invoice) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="editing_invoice_id" value="{{ $invoice->id }}">

                                                    <div class="modal-header">
                                                        <div>
                                                            <h5 class="modal-title">Edit Tagihan</h5>
                                                            <div class="text-secondary small mt-1">{{ $invoice->invoice_number }} - {{ $invoice->santri?->full_name }}</div>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label for="edit_santri_id_{{ $invoice->id }}" class="form-label">Santri</label>
                                                                <select id="edit_santri_id_{{ $invoice->id }}" name="santri_id" class="form-select @if(old('editing_invoice_id') == $invoice->id && $errors->updateInvoice->has('santri_id')) is-invalid @endif" required>
                                                                    @foreach ($santris as $santri)
                                                                        <option value="{{ $santri->id }}" @selected((string) (old('editing_invoice_id') == $invoice->id ? old('santri_id') : $invoice->santri_id) === (string) $santri->id)>
                                                                            {{ $santri->full_name }} - {{ $santri->nis }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @if (old('editing_invoice_id') == $invoice->id && $errors->updateInvoice->has('santri_id'))
                                                                    <div class="invalid-feedback">{{ $errors->updateInvoice->first('santri_id') }}</div>
                                                                @endif
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label for="edit_title_{{ $invoice->id }}" class="form-label">Nama Tagihan</label>
                                                                <input id="edit_title_{{ $invoice->id }}" name="title" type="text" class="form-control @if(old('editing_invoice_id') == $invoice->id && $errors->updateInvoice->has('title')) is-invalid @endif" value="{{ old('editing_invoice_id') == $invoice->id ? old('title') : $invoice->title }}" required>
                                                                @if (old('editing_invoice_id') == $invoice->id && $errors->updateInvoice->has('title'))
                                                                    <div class="invalid-feedback">{{ $errors->updateInvoice->first('title') }}</div>
                                                                @endif
                                                            </div>

                                                            <div class="col-md-3">
                                                                <label for="edit_period_month_{{ $invoice->id }}" class="form-label">Bulan</label>
                                                                <select id="edit_period_month_{{ $invoice->id }}" name="period_month" class="form-select @if(old('editing_invoice_id') == $invoice->id && $errors->updateInvoice->has('period_month')) is-invalid @endif">
                                                                    <option value="">Tanpa bulan</option>
                                                                    @foreach (range(1, 12) as $month)
                                                                        <option value="{{ $month }}" @selected((string) (old('editing_invoice_id') == $invoice->id ? old('period_month') : $invoice->period_month) === (string) $month)>
                                                                            {{ str_pad((string) $month, 2, '0', STR_PAD_LEFT) }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @if (old('editing_invoice_id') == $invoice->id && $errors->updateInvoice->has('period_month'))
                                                                    <div class="invalid-feedback">{{ $errors->updateInvoice->first('period_month') }}</div>
                                                                @endif
                                                            </div>

                                                            <div class="col-md-3">
                                                                <label for="edit_period_year_{{ $invoice->id }}" class="form-label">Tahun</label>
                                                                <input id="edit_period_year_{{ $invoice->id }}" name="period_year" type="number" min="{{ $periodYearMin }}" max="{{ $periodYearMax }}" class="form-control @if(old('editing_invoice_id') == $invoice->id && $errors->updateInvoice->has('period_year')) is-invalid @endif" value="{{ old('editing_invoice_id') == $invoice->id ? old('period_year') : $invoice->period_year }}">
                                                                @if (old('editing_invoice_id') == $invoice->id && $errors->updateInvoice->has('period_year'))
                                                                    <div class="invalid-feedback">{{ $errors->updateInvoice->first('period_year') }}</div>
                                                                @endif
                                                            </div>

                                                            <div class="col-md-3">
                                                                <label for="edit_due_date_{{ $invoice->id }}" class="form-label">Jatuh Tempo</label>
                                                                <input id="edit_due_date_{{ $invoice->id }}" name="due_date" type="date" class="form-control @if(old('editing_invoice_id') == $invoice->id && $errors->updateInvoice->has('due_date')) is-invalid @endif" value="{{ old('editing_invoice_id') == $invoice->id ? old('due_date') : optional($invoice->due_date)->toDateString() }}" required>
                                                                @if (old('editing_invoice_id') == $invoice->id && $errors->updateInvoice->has('due_date'))
                                                                    <div class="invalid-feedback">{{ $errors->updateInvoice->first('due_date') }}</div>
                                                                @endif
                                                            </div>

                                                            <div class="col-md-3">
                                                                <label for="edit_amount_{{ $invoice->id }}" class="form-label">Nominal</label>
                                                                <input id="edit_amount_{{ $invoice->id }}" name="amount" type="number" min="{{ max(1, $invoice->paid_amount / 100) }}" step="1" class="form-control @if(old('editing_invoice_id') == $invoice->id && $errors->updateInvoice->has('amount')) is-invalid @endif" value="{{ old('editing_invoice_id') == $invoice->id ? old('amount') : $invoice->amount / 100 }}" required>
                                                                @if (old('editing_invoice_id') == $invoice->id && $errors->updateInvoice->has('amount'))
                                                                    <div class="invalid-feedback">{{ $errors->updateInvoice->first('amount') }}</div>
                                                                @else
                                                                    <div class="form-hint mt-2">Minimal Rp {{ number_format(max(1, $invoice->paid_amount / 100), 0, ',', '.') }}.</div>
                                                                @endif
                                                            </div>

                                                            <div class="col-12">
                                                                <label for="edit_notes_{{ $invoice->id }}" class="form-label">Catatan</label>
                                                                <textarea id="edit_notes_{{ $invoice->id }}" name="notes" rows="3" class="form-control @if(old('editing_invoice_id') == $invoice->id && $errors->updateInvoice->has('notes')) is-invalid @endif" placeholder="Opsional">{{ old('editing_invoice_id') == $invoice->id ? old('notes') : $invoice->notes }}</textarea>
                                                                @if (old('editing_invoice_id') == $invoice->id && $errors->updateInvoice->has('notes'))
                                                                    <div class="invalid-feedback">{{ $errors->updateInvoice->first('notes') }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if ($canEditHistoricalPayments && $invoice->payments->isNotEmpty())
                                    <div class="modal modal-blur fade" id="correctPaymentModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-xl modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <div>
                                                        <h5 class="modal-title">Koreksi Pembayaran</h5>
                                                        <div class="text-secondary small mt-1">{{ $invoice->invoice_number }} - {{ $invoice->santri?->full_name }}</div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        @foreach ($invoice->payments as $payment)
                                                            <div class="col-12">
                                                                <div class="card card-body">
                                                                    <form method="POST" action="{{ route('santri.payments.payments.update', $payment) }}">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="editing_payment_id" value="{{ $payment->id }}">
                                                                        <input type="hidden" name="editing_payment_invoice_id" value="{{ $invoice->id }}">

                                                                        <div class="row g-3">
                                                                            <div class="col-md-3">
                                                                                <label for="edit_payment_amount_{{ $payment->id }}" class="form-label">Nominal</label>
                                                                                <input id="edit_payment_amount_{{ $payment->id }}" name="amount" type="number" min="1" step="1" class="form-control @if(old('editing_payment_id') == $payment->id && $errors->updatePayment->has('amount')) is-invalid @endif" value="{{ old('editing_payment_id') == $payment->id ? old('amount') : $payment->amount / 100 }}" required>
                                                                                @if (old('editing_payment_id') == $payment->id && $errors->updatePayment->has('amount'))
                                                                                    <div class="invalid-feedback">{{ $errors->updatePayment->first('amount') }}</div>
                                                                                @endif
                                                                            </div>

                                                                            <div class="col-md-3">
                                                                                <label for="edit_payment_paid_at_{{ $payment->id }}" class="form-label">Tanggal Bayar</label>
                                                                                <input id="edit_payment_paid_at_{{ $payment->id }}" name="paid_at" type="datetime-local" class="form-control @if(old('editing_payment_id') == $payment->id && $errors->updatePayment->has('paid_at')) is-invalid @endif" value="{{ old('editing_payment_id') == $payment->id ? old('paid_at') : optional($payment->paid_at)->format('Y-m-d\TH:i') }}" required>
                                                                                @if (old('editing_payment_id') == $payment->id && $errors->updatePayment->has('paid_at'))
                                                                                    <div class="invalid-feedback">{{ $errors->updatePayment->first('paid_at') }}</div>
                                                                                @endif
                                                                            </div>

                                                                            <div class="col-md-3">
                                                                                <label for="edit_payment_method_{{ $payment->id }}" class="form-label">Metode</label>
                                                                                <select id="edit_payment_method_{{ $payment->id }}" name="payment_method" class="form-select @if(old('editing_payment_id') == $payment->id && $errors->updatePayment->has('payment_method')) is-invalid @endif" required>
                                                                                    @foreach ($paymentMethods as $method)
                                                                                        <option value="{{ $method }}" @selected((old('editing_payment_id') == $payment->id ? old('payment_method') : $payment->payment_method) === $method)>
                                                                                            {{ str($method)->headline() }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                                @if (old('editing_payment_id') == $payment->id && $errors->updatePayment->has('payment_method'))
                                                                                    <div class="invalid-feedback">{{ $errors->updatePayment->first('payment_method') }}</div>
                                                                                @endif
                                                                            </div>

                                                                            <div class="col-md-3">
                                                                                <label for="edit_payment_reference_{{ $payment->id }}" class="form-label">No. Referensi</label>
                                                                                <input id="edit_payment_reference_{{ $payment->id }}" name="reference_number" type="text" class="form-control @if(old('editing_payment_id') == $payment->id && $errors->updatePayment->has('reference_number')) is-invalid @endif" value="{{ old('editing_payment_id') == $payment->id ? old('reference_number') : $payment->reference_number }}">
                                                                                @if (old('editing_payment_id') == $payment->id && $errors->updatePayment->has('reference_number'))
                                                                                    <div class="invalid-feedback">{{ $errors->updatePayment->first('reference_number') }}</div>
                                                                                @endif
                                                                            </div>

                                                                            <div class="col-12">
                                                                                <label for="edit_payment_note_{{ $payment->id }}" class="form-label">Catatan</label>
                                                                                <textarea id="edit_payment_note_{{ $payment->id }}" name="note" rows="2" class="form-control @if(old('editing_payment_id') == $payment->id && $errors->updatePayment->has('note')) is-invalid @endif">{{ old('editing_payment_id') == $payment->id ? old('note') : $payment->note }}</textarea>
                                                                                @if (old('editing_payment_id') == $payment->id && $errors->updatePayment->has('note'))
                                                                                    <div class="invalid-feedback">{{ $errors->updatePayment->first('note') }}</div>
                                                                                @endif
                                                                            </div>
                                                                        </div>

                                                                        <div class="d-flex justify-content-between align-items-center gap-2 mt-3">
                                                                            <div class="text-secondary small">Dicatat oleh {{ $payment->recorder?->name ?? 'System' }}</div>
                                                                            <button type="submit" class="btn btn-primary btn-sm">Simpan Koreksi</button>
                                                                        </div>
                                                                    </form>

                                                                    <form method="POST" action="{{ route('santri.payments.payments.destroy', $payment) }}" class="mt-2" onsubmit="return confirm('Hapus pembayaran ini dan hitung ulang status tagihan?')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-outline-danger btn-sm">Hapus Pembayaran</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Selesai</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-secondary">Belum ada tagihan santri.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invoices->hasPages())
            <div class="card-footer">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    @if ($canCreateInvoice)
        <div class="modal modal-blur fade" id="monthlyInvoiceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('santri.payments.invoices.monthly.generate') }}">
                        @csrf

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Generate Tagihan Bulanan</h5>
                                <div class="text-secondary small mt-1">Membuat tagihan untuk semua santri aktif dan melewati data yang sudah ada.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="monthly_title" class="form-label">Nama Tagihan</label>
                                    <input id="monthly_title" name="title" type="text" class="form-control @if($errors->has('title')) is-invalid @endif" value="{{ old('title', 'SPP Bulanan') }}" required>
                                    @if ($errors->has('title'))
                                        <div class="invalid-feedback">{{ $errors->first('title') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-3">
                                    <label for="monthly_period_month" class="form-label">Bulan</label>
                                    <select id="monthly_period_month" name="period_month" class="form-select @if($errors->has('period_month')) is-invalid @endif" required>
                                        @foreach (range(1, 12) as $month)
                                            <option value="{{ $month }}" @selected((string) old('period_month', now()->month) === (string) $month)>
                                                {{ str_pad((string) $month, 2, '0', STR_PAD_LEFT) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('period_month'))
                                        <div class="invalid-feedback">{{ $errors->first('period_month') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-3">
                                    <label for="monthly_period_year" class="form-label">Tahun</label>
                                    <input id="monthly_period_year" name="period_year" type="number" min="{{ $periodYearMin }}" max="{{ $periodYearMax }}" class="form-control @if($errors->has('period_year')) is-invalid @endif" value="{{ old('period_year', now()->year) }}" required>
                                    @if ($errors->has('period_year'))
                                        <div class="invalid-feedback">{{ $errors->first('period_year') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="monthly_due_date" class="form-label">Jatuh Tempo</label>
                                    <input id="monthly_due_date" name="due_date" type="date" class="form-control @if($errors->has('due_date')) is-invalid @endif" value="{{ old('due_date', now()->endOfMonth()->toDateString()) }}" required>
                                    @if ($errors->has('due_date'))
                                        <div class="invalid-feedback">{{ $errors->first('due_date') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="monthly_amount" class="form-label">Nominal per Santri</label>
                                    <input id="monthly_amount" name="amount" type="number" min="1" step="1" class="form-control @if($errors->has('amount')) is-invalid @endif" value="{{ old('amount') }}" required>
                                    @if ($errors->has('amount'))
                                        <div class="invalid-feedback">{{ $errors->first('amount') }}</div>
                                    @endif
                                </div>

                                <div class="col-12">
                                    <label for="monthly_notes" class="form-label">Catatan</label>
                                    <textarea id="monthly_notes" name="notes" rows="3" class="form-control @if($errors->has('notes')) is-invalid @endif" placeholder="Opsional">{{ old('notes') }}</textarea>
                                    @if ($errors->has('notes'))
                                        <div class="invalid-feedback">{{ $errors->first('notes') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="mode" value="preview" class="btn btn-outline-primary">Preview</button>
                            <button type="submit" name="mode" value="dispatch" class="btn btn-primary">Generate via Queue</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal modal-blur fade" id="createInvoiceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('santri.payments.invoices.store') }}">
                        @csrf

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Tambah Tagihan Santri</h5>
                                <div class="text-secondary small mt-1">Nomor tagihan dibuat otomatis per tenant dan periode.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="santri_id" class="form-label">Santri</label>
                                    <select id="santri_id" name="santri_id" class="form-select @if($errors->createInvoice->has('santri_id')) is-invalid @endif" required>
                                        <option value="">Pilih santri</option>
                                        @foreach ($santris as $santri)
                                            <option value="{{ $santri->id }}" @selected((string) old('santri_id') === (string) $santri->id)>
                                                {{ $santri->full_name }} - {{ $santri->nis }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->createInvoice->has('santri_id'))
                                        <div class="invalid-feedback">{{ $errors->createInvoice->first('santri_id') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="title" class="form-label">Nama Tagihan</label>
                                    <input id="title" name="title" type="text" class="form-control @if($errors->createInvoice->has('title')) is-invalid @endif" value="{{ old('title', 'SPP Bulanan') }}" required>
                                    @if ($errors->createInvoice->has('title'))
                                        <div class="invalid-feedback">{{ $errors->createInvoice->first('title') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-3">
                                    <label for="period_month" class="form-label">Bulan</label>
                                    <select id="period_month" name="period_month" class="form-select @if($errors->createInvoice->has('period_month')) is-invalid @endif">
                                        <option value="">Tanpa bulan</option>
                                        @foreach (range(1, 12) as $month)
                                            <option value="{{ $month }}" @selected((string) old('period_month', now()->month) === (string) $month)>
                                                {{ str_pad((string) $month, 2, '0', STR_PAD_LEFT) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->createInvoice->has('period_month'))
                                        <div class="invalid-feedback">{{ $errors->createInvoice->first('period_month') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-3">
                                    <label for="period_year" class="form-label">Tahun</label>
                                    <input id="period_year" name="period_year" type="number" min="{{ $periodYearMin }}" max="{{ $periodYearMax }}" class="form-control @if($errors->createInvoice->has('period_year')) is-invalid @endif" value="{{ old('period_year', now()->year) }}">
                                    @if ($errors->createInvoice->has('period_year'))
                                        <div class="invalid-feedback">{{ $errors->createInvoice->first('period_year') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-3">
                                    <label for="due_date" class="form-label">Jatuh Tempo</label>
                                    <input id="due_date" name="due_date" type="date" class="form-control @if($errors->createInvoice->has('due_date')) is-invalid @endif" value="{{ old('due_date', now()->endOfMonth()->toDateString()) }}" required>
                                    @if ($errors->createInvoice->has('due_date'))
                                        <div class="invalid-feedback">{{ $errors->createInvoice->first('due_date') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-3">
                                    <label for="amount" class="form-label">Nominal</label>
                                    <input id="amount" name="amount" type="number" min="1" step="1" class="form-control @if($errors->createInvoice->has('amount')) is-invalid @endif" value="{{ old('amount') }}" required>
                                    @if ($errors->createInvoice->has('amount'))
                                        <div class="invalid-feedback">{{ $errors->createInvoice->first('amount') }}</div>
                                    @endif
                                </div>

                                <div class="col-12">
                                    <label for="notes" class="form-label">Catatan</label>
                                    <textarea id="notes" name="notes" rows="3" class="form-control @if($errors->createInvoice->has('notes')) is-invalid @endif" placeholder="Opsional">{{ old('notes') }}</textarea>
                                    @if ($errors->createInvoice->has('notes'))
                                        <div class="invalid-feedback">{{ $errors->createInvoice->first('notes') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Tagihan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if ($errors->any() && ! $errors->createInvoice->any() && ! $errors->recordPayment->any() && ! $errors->updateInvoice->any() && ! $errors->updatePayment->any())
                document.getElementById('open-monthly-invoice-modal')?.click();
            @endif

            @if ($errors->createInvoice->any())
                document.getElementById('open-create-invoice-modal')?.click();
            @endif

            @if ($errors->recordPayment->any() && old('paying_invoice_id'))
                const paymentModalElement = document.getElementById('recordPaymentModal{{ old('paying_invoice_id') }}');

                if (paymentModalElement && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(paymentModalElement).show();
                }
            @endif

            @if ($errors->updateInvoice->any() && old('editing_invoice_id'))
                const invoiceModalElement = document.getElementById('editInvoiceModal{{ old('editing_invoice_id') }}');

                if (invoiceModalElement && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(invoiceModalElement).show();
                }
            @endif

            @if ($errors->updatePayment->any() && old('editing_payment_invoice_id'))
                const correctionModalElement = document.getElementById('correctPaymentModal{{ old('editing_payment_invoice_id') }}');

                if (correctionModalElement && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(correctionModalElement).show();
                }
            @endif
        });
    </script>
</x-app-layout>
