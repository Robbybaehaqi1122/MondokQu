<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Pembayaran Santri</h2>
            <div class="text-secondary mt-1">Ringkasan tagihan, pembayaran masuk, dan tunggakan santri.</div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-12">
            <div class="row g-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="text-secondary small text-uppercase fw-bold">Total Tagihan</div>
                                <span class="avatar bg-blue-lt text-blue">
                                    <i class="ti ti-file-invoice"></i>
                                </span>
                            </div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($summary['total_invoices']) }}</div>
                            <div class="text-secondary small mt-1">Rp {{ number_format($summary['total_amount'] / 100, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="text-secondary small text-uppercase fw-bold">Terbayar</div>
                                <span class="avatar bg-green-lt text-green">
                                    <i class="ti ti-check"></i>
                                </span>
                            </div>
                            <div class="fs-2 fw-bold mb-0">Rp {{ number_format($summary['paid_amount'] / 100, 0, ',', '.') }}</div>
                            <div class="text-secondary small mt-1">{{ number_format($summary['paid_invoices']) }} tagihan lunas</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="text-secondary small text-uppercase fw-bold">Sisa Tagihan</div>
                                <span class="avatar bg-yellow-lt text-yellow">
                                    <i class="ti ti-clock"></i>
                                </span>
                            </div>
                            <div class="fs-2 fw-bold mb-0">Rp {{ number_format($summary['outstanding_amount'] / 100, 0, ',', '.') }}</div>
                            <div class="text-secondary small mt-1">{{ number_format($summary['partial_invoices'] + $summary['pending_invoices']) }} belum lunas</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="text-secondary small text-uppercase fw-bold">Masuk Bulan Ini</div>
                                <span class="avatar bg-teal-lt text-teal">
                                    <i class="ti ti-calendar-event"></i>
                                </span>
                            </div>
                            <div class="fs-2 fw-bold mb-0">Rp {{ number_format($paidThisMonth / 100, 0, ',', '.') }}</div>
                            <div class="text-secondary small mt-1">{{ number_format($summary['overdue_invoices']) }} tunggakan aktif</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Tagihan Terbaru</h3>
                        <div class="text-secondary small mt-1">Tagihan terakhir yang dibuat untuk santri aktif.</div>
                    </div>
                    <div class="card-actions d-flex gap-2">
                        @can('manage keuangan')
                            <a href="{{ route('santri.payments.accounts.index') }}" class="btn btn-outline-secondary">Akun Pembayaran</a>
                        @endcan
                        <a href="{{ route('santri.payments.invoices') }}" class="btn btn-primary">Kelola Tagihan</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-mobile-md">
                        <thead>
                            <tr>
                                <th>No. Tagihan</th>
                                <th>Santri</th>
                                <th class="d-none d-md-table-cell">Nominal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentInvoices as $invoice)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $invoice->invoice_number }}</div>
                                        <div class="text-secondary small">{{ optional($invoice->due_date)->translatedFormat('d M Y') }}</div>
                                    </td>
                                    <td>{{ $invoice->santri?->full_name ?? '-' }}</td>
                                    <td class="d-none d-md-table-cell">Rp {{ number_format($invoice->amount / 100, 0, ',', '.') }}</td>
                                    <td>{{ $invoice->statusLabel() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-secondary">Belum ada tagihan santri.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pembayaran Terakhir</h3>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($recentPayments as $payment)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $payment->santri?->full_name ?? '-' }}</div>
                                    <div class="text-secondary small">{{ $payment->invoice?->invoice_number ?? '-' }} - {{ \App\Models\SantriPayment::paymentMethodLabel($payment->payment_method) }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-semibold">Rp {{ number_format($payment->amount / 100, 0, ',', '.') }}</div>
                                    <div class="text-secondary small">{{ optional($payment->paid_at)->translatedFormat('d M Y') }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Belum ada pembayaran tercatat.</div>
                    @endforelse
                </div>
                <div class="card-footer">
                    <a href="{{ route('santri.payments.reports') }}" class="btn btn-outline-primary w-100">Lihat Laporan</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
