<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Dashboard Tahfidz</h2>
                <div class="text-secondary mt-1">Ringkasan hafalan santri untuk {{ $today->translatedFormat('d M Y') }}.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('tahfidz.setoran.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Catat Setoran
                </a>
                <a href="{{ route('tahfidz.rapor.index') }}" class="btn btn-outline-primary">
                    <i class="ti ti-report-analytics me-1"></i>
                    Rapor Hafalan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Setoran Hari Ini</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['sessions_today']) }}</div>
                    </div>
                    <span class="avatar bg-primary-lt text-primary">
                        <i class="ti ti-book-2"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Total Setoran</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['total_sessions']) }}</div>
                    </div>
                    <span class="avatar bg-azure-lt text-azure">
                        <i class="ti ti-clipboard-list"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Santri Setoran</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['total_santri_with_setoran']) }}</div>
                    </div>
                    <span class="avatar bg-green-lt text-green">
                        <i class="ti ti-users"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Santri Aktif</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['total_santri_active']) }}</div>
                    </div>
                    <span class="avatar bg-success-lt text-success">
                        <i class="ti ti-user-check"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2 w-100">
                <div>
                    <h3 class="card-title">Setoran Terbaru</h3>
                    <div class="text-secondary small mt-2">10 setoran hafalan terbaru yang dicatat.</div>
                </div>
                <a href="{{ route('tahfidz.setoran.index') }}" class="btn btn-outline-secondary btn-sm">
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
                        <th>Tanggal</th>
                        <th>Musyrif</th>
                        <th>Jumlah Ayat</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentSessions as $session)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $session->santri?->full_name ?? '-' }}</div>
                                <div class="text-secondary small">NIS {{ $session->santri?->nis ?? '-' }}</div>
                            </td>
                            <td>{{ $session->session_date?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td>{{ $session->musyrif?->name ?? '-' }}</td>
                            <td>{{ number_format($session->records->sum(fn ($r) => ($r->verse_end - $r->verse_start) + 1)) }}</td>
                            <td>
                                <span class="badge {{ $session->status === 'completed' ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">
                                    {{ $session->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('tahfidz.setoran.show', $session) }}" class="btn btn-outline-primary btn-sm btn-icon" aria-label="Detail">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-secondary">Belum ada setoran hafalan yang dicatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
