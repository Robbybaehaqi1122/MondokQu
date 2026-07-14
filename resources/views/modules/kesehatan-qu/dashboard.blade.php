<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KesehatanQu</div>
            <h2 class="page-title mt-1">Dashboard Kesehatan</h2>
        </div>
    </x-slot>

    @if ($obatStokHabis > 0 || $obatExpired > 0 || $imunisasiTertunda > 0)
        <div class="row row-cards mb-3">
            @if ($obatStokHabis > 0)
                <div class="col-12">
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-0" role="alert">
                        <i class="ti ti-pill-off fs-3"></i>
                        <div>
                            <strong>{{ $obatStokHabis }} obat stok habis.</strong>
                            @if ($obatStokHabisList->isNotEmpty())
                                {{ $obatStokHabisList->pluck('nama_obat')->implode(', ') }}{{ $obatStokHabis > 5 ? ', dll.' : '' }}.
                            @endif
                            <a href="{{ route('kesehatan.obat.index') }}" class="alert-link ms-1">Lihat Stok Obat &rarr;</a>
                        </div>
                    </div>
                </div>
            @endif

            @if ($obatExpired > 0)
                <div class="col-12">
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-0" role="alert">
                        <i class="ti ti-alert-triangle fs-3"></i>
                        <div>
                            <strong>{{ $obatExpired }} obat sudah kedaluwarsa.</strong>
                            @if ($obatExpiredList->isNotEmpty())
                                {{ $obatExpiredList->pluck('nama_obat')->implode(', ') }}{{ $obatExpired > 5 ? ', dll.' : '' }}.
                            @endif
                            <a href="{{ route('kesehatan.obat.index') }}" class="alert-link ms-1">Lihat Stok Obat &rarr;</a>
                        </div>
                    </div>
                </div>
            @endif

            @if ($imunisasiTertunda > 0)
                <div class="col-12">
                    <div class="alert alert-info d-flex align-items-center gap-2 mb-0" role="alert">
                        <i class="ti ti-syringe fs-3"></i>
                        <div>
                            <strong>{{ $imunisasiTertunda }} imunisasi masih tertunda.</strong>
                            @if ($imunisasiTertundaList->isNotEmpty())
                                {{ $imunisasiTertundaList->pluck('jenis_imunisasi')->unique()->implode(', ') }}{{ $imunisasiTertunda > 5 ? ', dll.' : '' }}.
                            @endif
                            <a href="{{ route('kesehatan.imunisasi.index') }}" class="alert-link ms-1">Lihat Imunisasi &rarr;</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="row row-cards">
        <div class="col-12">
            <div class="row g-3">
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Total Santri</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($totalSantri) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Rekam Medis Terisi</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($rekamMedisTerisi) }}</div>
                            <div class="text-secondary small mt-1">{{ $totalSantri > 0 ? round(($rekamMedisTerisi / $totalSantri) * 100) : 0 }}% dari total santri</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Pemeriksaan Hari Ini</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($pemeriksaanHariIni) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Pemeriksaan Bulan Ini</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($pemeriksaanBulanIni) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Obat Stok Habis</div>
                            <div class="fs-2 fw-bold mb-0 {{ $obatStokHabis > 0 ? 'text-danger' : '' }}">{{ number_format($obatStokHabis) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Obat Expired</div>
                            <div class="fs-2 fw-bold mb-0 {{ $obatExpired > 0 ? 'text-warning' : '' }}">{{ number_format($obatExpired) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Imunisasi Tertunda</div>
                            <div class="fs-2 fw-bold mb-0 {{ $imunisasiTertunda > 0 ? 'text-info' : '' }}">{{ number_format($imunisasiTertunda) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($pemeriksaanTerbaru->isNotEmpty())
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title mb-1">Kunjungan UKS Terbaru</h3>
                            <div class="text-secondary small">10 pemeriksaan terakhir.</div>
                        </div>
                        <a href="{{ route('kesehatan.pemeriksaan.index') }}" class="btn btn-outline-secondary btn-sm">Lihat Semua</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Santri</th>
                                    <th>Keluhan</th>
                                    <th>Diagnosis</th>
                                    <th>Dicatat Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pemeriksaanTerbaru as $p)
                                    <tr>
                                        <td>{{ $p->tanggal_pemeriksaan->translatedFormat('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('kesehatan.pemeriksaan.show', $p) }}" class="text-reset text-decoration-none fw-semibold">
                                                {{ $p->santri?->full_name ?? 'Unknown' }}
                                            </a>
                                        </td>
                                        <td>{{ $p->keluhan }}</td>
                                        <td>{{ $p->diagnosis ?: '-' }}</td>
                                        <td>{{ $p->pencatat?->name ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if ($trenPemeriksaan->isNotEmpty() || $topKeluhan->isNotEmpty() || $topObat->isNotEmpty())
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Tren Pemeriksaan (6 Bulan)</h3>
                    </div>
                    <div class="card-body">
                        @if ($trenPemeriksaan->isNotEmpty())
                            <div style="height: 16rem;">
                                <canvas id="chartTrenPemeriksaan"></canvas>
                            </div>
                        @else
                            <div class="text-secondary text-center py-4">Belum ada data pemeriksaan.</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Top Keluhan</h3>
                    </div>
                    <div class="card-body">
                        @if ($topKeluhan->isNotEmpty())
                            <div style="height: 16rem;">
                                <canvas id="chartKeluhan"></canvas>
                            </div>
                        @else
                            <div class="text-secondary text-center py-4">Belum ada data keluhan.</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Obat Paling Sering Digunakan</h3>
                    </div>
                    <div class="card-body">
                        @if ($topObat->isNotEmpty())
                            <div style="height: 14rem;">
                                <canvas id="chartObat"></canvas>
                            </div>
                        @else
                            <div class="text-secondary text-center py-4">Belum ada data penggunaan obat.</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ringkasan</h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 14rem;">
                            <canvas id="chartRingkasan"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const palette = ['#2563eb', '#0d9488', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

    const trenLabels = @json($trenPemeriksaan->pluck('label'));
    const trenData = @json($trenPemeriksaan->pluck('count'));

    if (document.getElementById('chartTrenPemeriksaan') && trenLabels.length) {
        new Chart(document.getElementById('chartTrenPemeriksaan'), {
            type: 'line',
            data: {
                labels: trenLabels,
                datasets: [{
                    label: 'Pemeriksaan',
                    data: trenData,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: '#2563eb',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            },
        });
    }

    const keluhanLabels = @json($topKeluhan->pluck('keluhan'));
    const keluhanData = @json($topKeluhan->pluck('total'));

    if (document.getElementById('chartKeluhan') && keluhanLabels.length) {
        new Chart(document.getElementById('chartKeluhan'), {
            type: 'bar',
            data: {
                labels: keluhanLabels,
                datasets: [{
                    label: 'Jumlah',
                    data: keluhanData,
                    backgroundColor: palette.slice(0, keluhanLabels.length),
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } },
            },
        });
    }

    const obatLabels = @json($topObat->pluck('label'));
    const obatData = @json($topObat->pluck('count'));

    if (document.getElementById('chartObat') && obatLabels.length) {
        new Chart(document.getElementById('chartObat'), {
            type: 'bar',
            data: {
                labels: obatLabels,
                datasets: [{
                    label: 'Kali Digunakan',
                    data: obatData,
                    backgroundColor: palette.slice(0, obatLabels.length),
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } },
            },
        });
    }

    const ringkasanLabels = ['Total Santri', 'Rekam Medis', 'Pemeriksaan', 'Stok Habis', 'Expired', 'Imunisasi Tertunda'];
    const ringkasanData = [
        {{ $totalSantri }},
        {{ $rekamMedisTerisi }},
        {{ $pemeriksaanBulanIni }},
        {{ $obatStokHabis }},
        {{ $obatExpired }},
        {{ $imunisasiTertunda }},
    ];

    if (document.getElementById('chartRingkasan')) {
        new Chart(document.getElementById('chartRingkasan'), {
            type: 'doughnut',
            data: {
                labels: ringkasanLabels,
                datasets: [{
                    data: ringkasanData,
                    backgroundColor: ['#2563eb', '#0d9488', '#f59e0b', '#ef4444', '#f97316', '#8b5cf6'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } },
                },
            },
        });
    }
});
</script>
@endpush
