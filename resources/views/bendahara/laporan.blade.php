<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Laporan Keuangan</h2>
                <div class="text-secondary mt-1">Rekap pemasukan dan filter laporan keuangan pondok.</div>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Total Pemasukan</div>
                        <div class="fs-2 fw-bold">Rp {{ number_format($reportSummary['received'], 0, ',', '.') }}</div>
                    </div>
                    <span class="avatar bg-success-lt text-success">
                        <i class="ti ti-cash"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Transaksi</div>
                        <div class="fs-2 fw-bold">{{ number_format($reportSummary['transactions']) }}</div>
                    </div>
                    <span class="avatar bg-primary-lt text-primary">
                        <i class="ti ti-receipt"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Rata-rata</div>
                        <div class="fs-2 fw-bold">Rp {{ number_format($reportSummary['average_payment'], 0, ',', '.') }}</div>
                    </div>
                    <span class="avatar bg-azure-lt text-azure">
                        <i class="ti ti-chart-bar"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Tagihan Tertunggak</div>
                        <div class="fs-2 fw-bold">Rp {{ number_format($summary['overdue_amount'], 0, ',', '.') }}</div>
                    </div>
                    <span class="avatar bg-danger-lt text-danger">
                        <i class="ti ti-alert-octagon"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <div>
                <h3 class="card-title">Filter Laporan</h3>
                <div class="text-secondary small mt-2">Filter rentang tanggal dan metode pembayaran.</div>
            </div>
        </div>
        <form class="card-body" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Metode Pembayaran</label>
                    <select name="payment_method" class="form-select">
                        <option value="">Semua Metode</option>
                        @foreach ($methodOptions as $option)
                            <option value="{{ $option['value'] }}" @selected($filters['payment_method'] === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                    <a href="{{ route('bendahara.laporan') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Pemasukan per Metode</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Metode</th>
                                <th>Transaksi</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($methodTotals as $method)
                                <tr>
                                    <td class="fw-semibold">{{ ucfirst($method->payment_method) }}</td>
                                    <td>{{ number_format((int) $method->count) }}</td>
                                    <td class="fw-semibold">Rp {{ number_format((int) $method->total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-secondary">Belum ada data pembayaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2 w-100">
                        <div>
                            <h3 class="card-title">Riwayat Pembayaran</h3>
                            <div class="text-secondary small mt-2">Daftar pembayaran sesuai filter.</div>
                        </div>
                        <a href="{{ route('santri.payments.reports', request()->only(['date_from', 'date_to', 'payment_method'])) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-download me-1"></i>
                            Laporan Lengkap
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Santri</th>
                                <th>Tanggal</th>
                                <th>Metode</th>
                                <th>Jumlah</th>
                                <th>Pencatat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $payment->santri?->full_name ?? '-' }}</div>
                                        <div class="text-secondary small">{{ $payment->santri?->nis ?? '-' }}</div>
                                    </td>
                                    <td>{{ $payment->paid_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                                    <td>{{ ucfirst($payment->payment_method) }}</td>
                                    <td class="fw-semibold text-success">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                    <td class="text-secondary small">{{ $payment->recorder?->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-secondary">Belum ada pembayaran untuk filter ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($payments->hasPages())
                    <div class="card-footer">
                        {{ $payments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
