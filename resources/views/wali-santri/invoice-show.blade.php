<x-app-layout>
    @php
        $invoiceBadgeClasses = [
            'pending' => 'bg-warning-lt text-warning',
            'partial' => 'bg-blue-lt text-blue',
            'paid' => 'bg-success-lt text-success',
            'overdue' => 'bg-danger-lt text-danger',
        ];

        $displayStatus = $invoice->isOverdue() ? 'overdue' : $invoice->status;
        $outstandingAmount = $invoice->outstandingAmount();
        $invoiceAmount = $invoice->amount;
        $paidAmount = $invoice->paid_amount;
        $paymentProgress = $invoiceAmount > 0 ? min(100, round(($paidAmount / $invoiceAmount) * 100)) : 0;
        $periodLabel = $invoice->period_month && $invoice->period_year
            ? str_pad((string) $invoice->period_month, 2, '0', STR_PAD_LEFT).'/'.$invoice->period_year
            : '-';
        $confirmationBadgeClasses = [
            'pending' => 'bg-warning-lt text-warning',
            'approved' => 'bg-success-lt text-success',
            'rejected' => 'bg-danger-lt text-danger',
        ];
    @endphp

    <x-slot name="header">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-sm-between gap-2">
            <div>
                <h2 class="page-title">Detail Tagihan</h2>
                <div class="text-secondary mt-1">{{ $invoice->invoice_number }} - {{ $invoice->santri?->full_name ?? '-' }}</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if ($outstandingAmount > 0)
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadProofModal{{ $invoice->id }}">
                        <i class="ti ti-upload me-1"></i>
                        Upload Bukti Bayar
                    </button>
                @endif
                <a href="{{ route('wali-santri.invoices.receipt', $invoice) }}" class="btn btn-primary" target="_blank" rel="noopener">
                    <i class="ti ti-printer me-1"></i>
                    Cetak
                </a>
                <a href="{{ route('wali-santri.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex flex-column flex-sm-row align-items-sm-start justify-content-sm-between gap-3">
                        <div>
                            <div class="text-secondary small text-uppercase fw-bold">Tagihan</div>
                            <h3 class="card-title mt-2 mb-1">{{ $invoice->title }}</h3>
                            <div class="text-secondary small">{{ $invoice->invoice_number }}</div>
                        </div>
                        <span class="badge {{ $invoiceBadgeClasses[$displayStatus] ?? 'bg-secondary-lt text-secondary' }}">
                            {{ $invoice->statusLabel() }}
                        </span>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-sm-6 col-lg-4">
                            <div class="wali-mobile-field h-100">
                                <span>Santri</span>
                                <strong>{{ $invoice->santri?->full_name ?? '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="wali-mobile-field h-100">
                                <span>NIS</span>
                                <strong>{{ $invoice->santri?->nis ?? '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="wali-mobile-field h-100">
                                <span>Periode</span>
                                <strong>{{ $periodLabel }}</strong>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="wali-mobile-field h-100">
                                <span>Jatuh Tempo</span>
                                <strong>{{ $invoice->due_date?->translatedFormat('d M Y') ?? '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="wali-mobile-field h-100">
                                <span>Nominal</span>
                                <strong>Rp {{ number_format($invoiceAmount / 100, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="wali-mobile-field h-100">
                                <span>Sisa</span>
                                <strong>Rp {{ number_format($outstandingAmount / 100, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between gap-3 mb-2">
                            <div class="text-secondary small">Terbayar</div>
                            <div class="fw-semibold">Rp {{ number_format($paidAmount / 100, 0, ',', '.') }}</div>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-primary" style="width: {{ $paymentProgress }}%" role="progressbar" aria-valuenow="{{ $paymentProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    @if ($invoice->notes)
                        <div class="alert alert-info mt-4 mb-0">
                            <div class="fw-semibold mb-1">Catatan</div>
                            <div>{{ $invoice->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan</h3>
                </div>
                <div class="card-body user-detail-info-list">
                    <div class="user-detail-info-row">
                        <span>Total Tagihan</span>
                        <strong>Rp {{ number_format($invoiceAmount / 100, 0, ',', '.') }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Sudah Dibayar</span>
                        <strong>Rp {{ number_format($paidAmount / 100, 0, ',', '.') }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Sisa Tagihan</span>
                        <strong>Rp {{ number_format($outstandingAmount / 100, 0, ',', '.') }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Transaksi</span>
                        <strong>{{ number_format($payments->count()) }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Bukti Dikirim</span>
                        <strong>{{ number_format($paymentConfirmations->count()) }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Status</span>
                        <strong>{{ $invoice->statusLabel() }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bukti Bayar Dikirim</h3>
                </div>
                <div class="card-body wali-payment-list">
                    @forelse ($paymentConfirmations as $confirmation)
                        <div class="wali-payment-item d-flex align-items-start gap-3">
                            <span class="avatar avatar-sm {{ $confirmationBadgeClasses[$confirmation->status] ?? 'bg-secondary-lt text-secondary' }}">
                                <i class="ti ti-receipt-2"></i>
                            </span>
                            <div class="flex-fill min-width-0">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-sm-between gap-1">
                                    <div class="fw-semibold wali-payment-amount">Rp {{ number_format($confirmation->amount / 100, 0, ',', '.') }}</div>
                                    <span class="badge {{ $confirmationBadgeClasses[$confirmation->status] ?? 'bg-secondary-lt text-secondary' }}">
                                        {{ $confirmation->statusLabel() }}
                                    </span>
                                </div>
                                <div class="text-secondary small mt-1">
                                    Transfer {{ $confirmation->paid_at?->translatedFormat('d M Y H:i') }}
                                    @if ($confirmation->reference_number)
                                        &middot; {{ $confirmation->reference_number }}
                                    @endif
                                </div>
                                @if ($confirmation->note)
                                    <div class="text-secondary small mt-2">{{ $confirmation->note }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary">Belum ada bukti bayar yang dikirim untuk tagihan ini.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Pembayaran</h3>
                </div>
                <div class="card-body wali-payment-list">
                    @forelse ($payments as $payment)
                        <div class="wali-payment-item d-flex align-items-start gap-3">
                            <span class="avatar avatar-sm bg-success-lt text-success">
                                <i class="ti ti-check"></i>
                            </span>
                            <div class="flex-fill min-width-0">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-sm-between gap-1">
                                    <div class="fw-semibold wali-payment-amount">Rp {{ number_format($payment->amount / 100, 0, ',', '.') }}</div>
                                    <div class="text-secondary small">{{ $payment->paid_at?->translatedFormat('d M Y H:i') }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <span class="badge bg-success-lt text-success">
                                        {{ $payment->payment_method ? str($payment->payment_method)->headline() : 'Pembayaran' }}
                                    </span>
                                    @if ($payment->reference_number)
                                        <span class="badge bg-secondary-lt text-secondary">{{ $payment->reference_number }}</span>
                                    @endif
                                </div>
                                @if ($payment->note)
                                    <div class="text-secondary small mt-2">{{ $payment->note }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary">Belum ada pembayaran untuk tagihan ini.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if ($outstandingAmount > 0)
        <div class="modal modal-blur fade" id="uploadProofModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('wali-santri.invoices.payment-confirmations.store', $invoice) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="confirmation_invoice_id" value="{{ $invoice->id }}">

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Upload Bukti Bayar</h5>
                                <div class="text-secondary small mt-1">{{ $invoice->invoice_number }} &middot; {{ $invoice->santri?->full_name ?? '-' }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            @if ($errors->paymentConfirmation->any() && (string) old('confirmation_invoice_id') === (string) $invoice->id)
                                <div class="alert alert-danger" role="alert">
                                    <div class="fw-semibold mb-2">Bukti bayar belum bisa dikirim.</div>
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->paymentConfirmation->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="proof_amount_{{ $invoice->id }}" class="form-label">Nominal Transfer</label>
                                    <input id="proof_amount_{{ $invoice->id }}" name="amount" type="number" min="1" max="{{ $outstandingAmount / 100 }}" step="1" class="form-control @if($errors->paymentConfirmation->has('amount')) is-invalid @endif" value="{{ old('amount', $outstandingAmount / 100) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="proof_paid_at_{{ $invoice->id }}" class="form-label">Tanggal Transfer</label>
                                    <input id="proof_paid_at_{{ $invoice->id }}" name="paid_at" type="datetime-local" class="form-control @if($errors->paymentConfirmation->has('paid_at')) is-invalid @endif" value="{{ old('paid_at', now()->format('Y-m-d\\TH:i')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="proof_method_{{ $invoice->id }}" class="form-label">Metode Pembayaran</label>
                                    <select id="proof_method_{{ $invoice->id }}" name="payment_method" class="form-select form-select-pretty @if($errors->paymentConfirmation->has('payment_method')) is-invalid @endif" required>
                                        @foreach ($paymentMethods as $method)
                                            <option value="{{ $method }}" @selected(old('payment_method', 'transfer bank') === $method)>{{ str($method)->headline() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="proof_reference_{{ $invoice->id }}" class="form-label">No. Referensi</label>
                                    <input id="proof_reference_{{ $invoice->id }}" name="reference_number" type="text" maxlength="100" class="form-control @if($errors->paymentConfirmation->has('reference_number')) is-invalid @endif" value="{{ old('reference_number') }}" placeholder="Opsional">
                                </div>
                                <div class="col-12">
                                    <label for="proof_file_{{ $invoice->id }}" class="form-label">File Bukti Bayar</label>
                                    <input id="proof_file_{{ $invoice->id }}" name="proof" type="file" class="form-control @if($errors->paymentConfirmation->has('proof')) is-invalid @endif" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                                    <div class="form-hint mt-2">JPG, PNG, atau WEBP. Maksimal 4 MB.</div>
                                </div>
                                <div class="col-12">
                                    <label for="proof_note_{{ $invoice->id }}" class="form-label">Catatan</label>
                                    <textarea id="proof_note_{{ $invoice->id }}" name="note" rows="3" maxlength="1000" class="form-control @if($errors->paymentConfirmation->has('note')) is-invalid @endif" placeholder="Opsional. Contoh: transfer atas nama orang tua.">{{ old('note') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-upload me-1"></i>
                                Kirim Bukti Bayar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                @if ($errors->paymentConfirmation->any())
                    const proofModalElement = document.getElementById('uploadProofModal{{ $invoice->id }}');

                    if (proofModalElement && window.bootstrap?.Modal) {
                        window.bootstrap.Modal.getOrCreateInstance(proofModalElement).show();
                    }
                @endif
            });
        </script>
    @endif
</x-app-layout>
