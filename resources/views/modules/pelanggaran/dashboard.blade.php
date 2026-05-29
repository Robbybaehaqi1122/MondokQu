<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Dashboard Pelanggaran</h2>
                <div class="text-secondary mt-1">Ringkasan pelanggaran santri untuk {{ $today->translatedFormat('d M Y') }}.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('pelanggaran.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Catat Pelanggaran
                </a>
                <a href="{{ route('pelanggaran.laporan.index') }}" class="btn btn-outline-primary">
                    <i class="ti ti-report-analytics me-1"></i>
                    Laporan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Hari Ini</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['hari_ini']) }}</div>
                    </div>
                    <span class="avatar bg-danger-lt text-danger">
                        <i class="ti ti-alert-triangle"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Total Pelanggaran</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['total_pelanggaran']) }}</div>
                    </div>
                    <span class="avatar bg-warning-lt text-warning">
                        <i class="ti ti-list-details"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Total Poin</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['total_poin']) }}</div>
                    </div>
                    <span class="avatar bg-orange-lt text-orange">
                        <i class="ti ti-numbers"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Santri Tercatat</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['santri_tercatat']) }}</div>
                    </div>
                    <span class="avatar bg-azure-lt text-azure">
                        <i class="ti ti-users"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Santri Aktif</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['santri_aktif']) }}</div>
                    </div>
                    <span class="avatar bg-success-lt text-success">
                        <i class="ti ti-user-check"></i>
                    </span>
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
                            <h3 class="card-title">Pelanggaran Terbaru</h3>
                            <div class="text-secondary small mt-2">10 pelanggaran terbaru yang dicatat.</div>
                        </div>
                        <a href="{{ route('pelanggaran.index') }}" class="btn btn-outline-secondary btn-sm">
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
                                <th>Kategori</th>
                                <th>Poin</th>
                                <th>Tanggal</th>
                                <th>Pencatat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentPelanggaran as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->santri?->full_name ?? '-' }}</div>
                                        <div class="text-secondary small">NIS {{ $item->santri?->nis ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger-lt text-danger">{{ $item->kategori?->nama ?? '-' }}</span>
                                    </td>
                                    <td class="fw-semibold">{{ number_format($item->poin) }}</td>
                                    <td>{{ $item->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                                    <td class="text-secondary small">{{ $item->pencatat?->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-secondary">Belum ada pelanggaran yang dicatat.</td>
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
                    <div>
                        <h3 class="card-title">Santri Poin Tertinggi</h3>
                        <div class="text-secondary small mt-2">5 santri dengan akumulasi poin terbanyak.</div>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($santriTertinggi as $item)
                        <div class="list-group-item">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $item->santri?->full_name ?? '-' }}</div>
                                    <div class="text-secondary small">{{ $item->jumlah }} pelanggaran</div>
                                </div>
                                <span class="badge bg-danger-lt text-danger fs-5">{{ number_format((int) $item->total_poin) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Belum ada data pelanggaran.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
