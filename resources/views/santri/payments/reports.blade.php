<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Laporan Bendahara</h2>
            <div class="text-secondary mt-1">Rekap pembayaran masuk, tunggakan, dan metode bayar santri.</div>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('santri.payments.reports') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="date_from" class="form-label">Dari Tanggal</label>
                    <input id="date_from" name="date_from" type="date" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">Sampai Tanggal</label>
                    <input id="date_to" name="date_to" type="date" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                        <a
                            href="{{ route('santri.payments.reports.export', request()->only(['date_from', 'date_to'])) }}"
                            class="btn btn-outline-primary"
                        >
                            Export CSV
                        </a>
                        <a href="{{ route('santri.payments.reports') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Pembayaran Masuk</div>
                <div class="fs-2 fw-bold">Rp {{ number_format((float) $reportSummary['received'], 0, ',', '.') }}</div>
                <div class="text-secondary small">{{ number_format($reportSummary['transactions']) }} transaksi</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Rata-rata Bayar</div>
                <div class="fs-2 fw-bold">Rp {{ number_format((float) $reportSummary['average_payment'], 0, ',', '.') }}</div>
                <div class="text-secondary small">Dalam periode filter</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Sisa Tagihan</div>
                <div class="fs-2 fw-bold">Rp {{ number_format((float) $summary['outstanding_amount'], 0, ',', '.') }}</div>
                <div class="text-secondary small">{{ number_format($summary['pending_invoices'] + $summary['partial_invoices']) }} belum lunas</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Tunggakan</div>
                <div class="fs-2 fw-bold">Rp {{ number_format((float) $summary['overdue_amount'], 0, ',', '.') }}</div>
                <div class="text-secondary small">{{ number_format($summary['overdue_invoices']) }} tagihan lewat tempo</div>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Metode Pembayaran</h3>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($methodTotals as $methodTotal)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">{{ str($methodTotal->payment_method)->headline() }}</div>
                                    <div class="text-secondary small">{{ number_format($methodTotal->count) }} transaksi</div>
                                </div>
                                <div class="fw-semibold text-end">Rp {{ number_format((float) $methodTotal->total, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Belum ada pembayaran pada periode ini.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100">
                        <h3 class="card-title">Transaksi Pembayaran</h3>
                        <a href="{{ route('santri.payments.invoices') }}" class="btn btn-outline-primary">Kelola Tagihan</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Santri</th>
                                <th>Tagihan</th>
                                <th>Metode</th>
                                <th>Nominal</th>
                                <th>Dicatat Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentPayments as $payment)
                                <tr>
                                    <td>{{ optional($payment->paid_at)->translatedFormat('d M Y H:i') }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $payment->santri?->full_name ?? '-' }}</div>
                                        <div class="text-secondary small">NIS: {{ $payment->santri?->nis ?? '-' }}</div>
                                    </td>
                                    <td>{{ $payment->invoice?->invoice_number ?? '-' }}</td>
                                    <td>{{ str($payment->payment_method)->headline() }}</td>
                                    <td>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                                    <td>{{ $payment->recorder?->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-secondary">Belum ada transaksi pembayaran pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($recentPayments->hasPages())
                    <div class="card-footer">
                        {{ $recentPayments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
