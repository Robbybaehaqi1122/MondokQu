<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KesehatanQu</div>
            <h2 class="page-title mt-1">Dashboard Kesehatan</h2>
        </div>
    </x-slot>

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
                            <div class="fs-2 fw-bold mb-0">{{ number_format($obatStokHabis) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Imunisasi Tertunda</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($imunisasiTertunda) }}</div>
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
    </div>
</x-app-layout>
