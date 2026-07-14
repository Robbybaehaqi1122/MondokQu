<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Dashboard KeuanganQu</h2>
                <div class="text-secondary mt-1">Ringkasan keuangan pondok periode {{ $year }} / {{ str_pad((string) $month, 2, '0', STR_PAD_LEFT) }}.</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('keuangan.jurnal.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Jurnal Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Saldo Kas</div>
                    <div class="h1 mb-1 text-truncate" style="font-size: 1.5rem;">Rp {{ number_format($saldoKas, 0, ',', '.') }}</div>
                    <div class="text-secondary">Total saldo kas & bank</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Akun Aktif</div>
                    <div class="h1 mb-1" style="font-size: 1.5rem;">{{ number_format($totalAkun) }}</div>
                    <div class="text-secondary">Kode akun (COA) aktif</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Jurnal Diposting</div>
                    <div class="h1 mb-1" style="font-size: 1.5rem;">{{ number_format($totalPosted) }}</div>
                    <div class="text-secondary">Bulan {{ str_pad((string) $month, 2, '0', STR_PAD_LEFT) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Jurnal Draft</div>
                    <div class="h1 mb-1" style="font-size: 1.5rem;">{{ number_format($totalDraft) }}</div>
                    <div class="text-secondary">Menunggu persetujuan</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mt-3">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tren Pemasukan & Pengeluaran {{ $year }}</h3>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pendapatan {{ str_pad((string) $month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</h3>
                </div>
                <div class="card-body">
                    @if ($pieData->isNotEmpty())
                        <canvas id="pieChart" height="250"></canvas>
                    @else
                        <div class="text-secondary text-center py-4">Belum ada data pendapatan bulan ini.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Jurnal Terbaru</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-mobile-md">
                        <thead>
                            <tr>
                                <th>No. Jurnal</th>
                                <th>Tanggal</th>
                                <th class="d-none d-md-table-cell">Deskripsi</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="d-none d-md-table-cell">Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($jurnalTerbaru as $entry)
                                <tr>
                                    <td>
                                        <a href="{{ route('keuangan.jurnal.show', $entry) }}" class="text-reset fw-semibold">
                                            {{ $entry->journal_number }}
                                        </a>
                                    </td>
                                    <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
                                    <td class="d-none d-md-table-cell text-truncate" style="max-width: 250px;">{{ $entry->description }}</td>
                                    <td>Rp {{ number_format($entry->totalDebit(), 0, ',', '.') }}</td>
                                    <td>
                                        @if ($entry->isPosted())
                                            <span class="badge bg-success">Posted</span>
                                        @else
                                            <span class="badge bg-warning">Draft</span>
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $entry->creator?->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-secondary text-center py-4">Belum ada jurnal.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trendData = @json($trend);
    const pieData = @json($pieData);
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    const trendCtx = document.getElementById('trendChart')?.getContext('2d');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: monthNames,
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: trendData.map(d => d.pemasukan),
                        borderColor: '#0d9488',
                        backgroundColor: 'rgba(13, 148, 136, 0.1)',
                        fill: true,
                        tension: 0.3,
                    },
                    {
                        label: 'Pengeluaran',
                        data: trendData.map(d => d.pengeluaran),
                        borderColor: '#e53e3e',
                        backgroundColor: 'rgba(229, 62, 62, 0.1)',
                        fill: true,
                        tension: 0.3,
                    },
                ],
            },
            options: {
                responsive: true,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { position: 'top' },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => 'Rp ' + (v / 1000).toFixed(0) + 'k',
                        },
                    },
                },
            },
        });
    }

    const pieCtx = document.getElementById('pieChart')?.getContext('2d');
    if (pieCtx && pieData.length) {
        const colors = ['#0d9488', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316', '#6366f1'];
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: pieData.map(d => d.label),
                datasets: [{
                    data: pieData.map(d => d.amount),
                    backgroundColor: colors.slice(0, pieData.length),
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12 } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + ctx.label + ': Rp ' + Number(ctx.raw).toLocaleString('id-ID'),
                        },
                    },
                },
            },
        });
    }
});
</script>
@endpush
