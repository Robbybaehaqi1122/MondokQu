<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Dashboard Bendahara</h2>
                <div class="text-secondary mt-1">Ringkasan keuangan untuk {{ $today->translatedFormat('d M Y') }}.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('santri.payments.invoices') }}" class="btn btn-primary">
                    <i class="ti ti-receipt me-1"></i>
                    Kelola Tagihan
                </a>
                <a href="{{ route('santri.payments.reports') }}" class="btn btn-outline-primary">
                    <i class="ti ti-report-money me-1"></i>
                    Laporan Keuangan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Total Tagihan</div>
                        <div class="fs-2 fw-bold">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</div>
                    </div>
                    <span class="avatar bg-primary-lt text-primary">
                        <i class="ti ti-receipt"></i>
                    </span>
                </div>
                <div class="mt-2 text-secondary small">
                    {{ number_format($summary['total_invoices']) }} tagihan
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Telah Dibayar</div>
                        <div class="fs-2 fw-bold">Rp {{ number_format($summary['paid_amount'], 0, ',', '.') }}</div>
                    </div>
                    <span class="avatar bg-success-lt text-success">
                        <i class="ti ti-circle-check"></i>
                    </span>
                </div>
                <div class="mt-2 text-secondary small">
                    {{ number_format($summary['paid_invoices']) }} tagihan lunas
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Tunggakan</div>
                        <div class="fs-2 fw-bold">Rp {{ number_format($summary['overdue_amount'], 0, ',', '.') }}</div>
                    </div>
                    <span class="avatar bg-danger-lt text-danger">
                        <i class="ti ti-alert-octagon"></i>
                    </span>
                </div>
                <div class="mt-2 text-secondary small">
                    {{ number_format($summary['overdue_invoices']) }} tagihan
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Pemasukan Bulan Ini</div>
                        <div class="fs-2 fw-bold">Rp {{ number_format($paidThisMonth, 0, ',', '.') }}</div>
                    </div>
                    <span class="avatar bg-azure-lt text-azure">
                        <i class="ti ti-trending-up"></i>
                    </span>
                </div>
                <div class="mt-2 text-secondary small">
                    {{ number_format($summary['outstanding_amount'] > 0 ? round(($summary['paid_amount'] / max($summary['total_amount'], 1)) * 100) : 0) }}% terkumpul
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Tren Pembayaran</h3>
                        <div class="text-secondary small mt-2">Total pemasukan 6 bulan terakhir.</div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($trendLabels->isNotEmpty())
                        <canvas id="paymentTrendChart" height="200"></canvas>
                    @else
                        <div class="text-secondary py-4 text-center">Belum ada data pembayaran.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Tagihan Jatuh Tempo</h3>
                        <div class="text-secondary small mt-2">Tagihan yang akan jatuh tempo 7 hari ke depan.</div>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($upcomingInvoices as $invoice)
                        <div class="list-group-item">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $invoice->santri?->full_name ?? '-' }}</div>
                                    <div class="text-secondary small">
                                        Rp {{ number_format($invoice->outstandingAmount(), 0, ',', '.') }}
                                        <span class="ms-2">Jatuh tempo {{ $invoice->due_date?->translatedFormat('d M') ?? '-' }}</span>
                                    </div>
                                </div>
                                <span class="badge bg-warning-lt text-warning">{{ $invoice->statusLabel() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Tidak ada tagihan yang akan jatuh tempo.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2 w-100">
                        <div>
                            <h3 class="card-title">Tagihan Tertunggak</h3>
                            <div class="text-secondary small mt-2">Tagihan yang melewati batas jatuh tempo.</div>
                        </div>
                        <a href="{{ route('santri.payments.invoices', ['status' => 'overdue']) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-list me-1"></i>
                            Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Santri</th>
                                <th>Tagihan</th>
                                <th>Jatuh Tempo</th>
                                <th>Tertunggak</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($overdueInvoices as $invoice)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $invoice->santri?->full_name ?? '-' }}</div>
                                        <div class="text-secondary small">NIS {{ $invoice->santri?->nis ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $invoice->title }}</div>
                                        <div class="text-secondary small">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="text-danger fw-semibold">{{ $invoice->due_date?->translatedFormat('d M Y') ?? '-' }}</td>
                                    <td class="fw-semibold text-danger">Rp {{ number_format($invoice->outstandingAmount(), 0, ',', '.') }}</td>
                                    <td>
                                        <a href="{{ route('santri.payments.invoices') }}?santri_id={{ $invoice->santri_id }}" class="btn btn-outline-primary btn-sm btn-icon" aria-label="Bayar">
                                            <i class="ti ti-cash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-secondary">Tidak ada tagihan tertunggak.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2 w-100">
                        <div>
                            <h3 class="card-title">Pembayaran Terbaru</h3>
                            <div class="text-secondary small mt-2">10 pembayaran terakhir yang dicatat.</div>
                        </div>
                        <a href="{{ route('santri.payments.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-list me-1"></i>
                            Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($recentPayments as $payment)
                        <div class="list-group-item">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $payment->santri?->full_name ?? '-' }}</div>
                                    <div class="text-secondary small">
                                        {{ $payment->payment_method }}
                                        <span class="ms-2">{{ $payment->paid_at?->translatedFormat('d M Y H:i') ?? '-' }}</span>
                                    </div>
                                </div>
                                <span class="fw-semibold text-success">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Belum ada pembayaran.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @if ($trendLabels->isNotEmpty())
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    new Chart(document.getElementById('paymentTrendChart'), {
                        type: 'bar',
                        data: {
                            labels: @json($trendLabels),
                            datasets: [{
                                label: 'Pemasukan (Rp)',
                                data: @json($trendData->map(fn ($v) => (int) $v)),
                                backgroundColor: 'rgba(32, 107, 196, 0.15)',
                                borderColor: '#206bc4',
                                borderWidth: 2,
                                borderRadius: 4,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function (value) {
                                            return 'Rp ' + value.toLocaleString('id-ID');
                                        },
                                    },
                                },
                            },
                        },
                    });
                });
            </script>
        @endif
    @endpush
</x-app-layout>
