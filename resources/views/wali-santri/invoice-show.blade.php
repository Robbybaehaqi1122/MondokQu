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
        $invoiceAmount = (float) $invoice->amount;
        $paidAmount = (float) $invoice->paid_amount;
        $paymentProgress = $invoiceAmount > 0 ? min(100, round(($paidAmount / $invoiceAmount) * 100)) : 0;
        $periodLabel = $invoice->period_month && $invoice->period_year
            ? str_pad((string) $invoice->period_month, 2, '0', STR_PAD_LEFT).'/'.$invoice->period_year
            : '-';
    @endphp

    <x-slot name="header">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-sm-between gap-2">
            <div>
                <h2 class="page-title">Detail Tagihan</h2>
                <div class="text-secondary mt-1">{{ $invoice->invoice_number }} - {{ $invoice->santri?->full_name ?? '-' }}</div>
            </div>
            <a href="{{ route('wali-santri.dashboard') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i>
                Kembali
            </a>
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
                                <strong>Rp {{ number_format($invoiceAmount, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="wali-mobile-field h-100">
                                <span>Sisa</span>
                                <strong>Rp {{ number_format($outstandingAmount, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between gap-3 mb-2">
                            <div class="text-secondary small">Terbayar</div>
                            <div class="fw-semibold">Rp {{ number_format($paidAmount, 0, ',', '.') }}</div>
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
                        <strong>Rp {{ number_format($invoiceAmount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Sudah Dibayar</span>
                        <strong>Rp {{ number_format($paidAmount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Sisa Tagihan</span>
                        <strong>Rp {{ number_format($outstandingAmount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Transaksi</span>
                        <strong>{{ number_format($payments->count()) }}</strong>
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
                                    <div class="fw-semibold wali-payment-amount">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</div>
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
</x-app-layout>
