<x-app-layout>
    @php
        $recordBadgeClasses = [
            'present' => 'bg-success-lt text-success',
            'permission' => 'bg-azure-lt text-azure',
            'sick' => 'bg-warning-lt text-warning',
            'absent' => 'bg-danger-lt text-danger',
            'late' => 'bg-orange-lt text-orange',
        ];
    @endphp

    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Dashboard Musyrif</h2>
                <div class="text-secondary mt-1">Ringkasan kegiatan untuk {{ $today->translatedFormat('d M Y') }}.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('tahfidz.setoran.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Catat Setoran
                </a>
                <a href="{{ route('pelanggaran.index') }}" class="btn btn-outline-primary">
                    <i class="ti ti-alert-triangle me-1"></i>
                    Pelanggaran
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Santri Aktif</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['active_santri']) }}</div>
                    </div>
                    <span class="avatar bg-success-lt text-success">
                        <i class="ti ti-users"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Izin Hari Ini</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['on_leave_today']) }}</div>
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
                        <div class="text-uppercase text-secondary small">Sesi Absensi</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['sessions_today']) }}</div>
                    </div>
                    <span class="avatar bg-primary-lt text-primary">
                        <i class="ti ti-calendar-check"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Setoran Hari Ini</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['tahfidz_today']) }}</div>
                    </div>
                    <span class="avatar bg-orange-lt text-orange">
                        <i class="ti ti-book-2"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Pelanggaran (Bulan Ini)</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['total_pelanggaran_bulan_ini']) }}</div>
                    </div>
                    <span class="avatar bg-danger-lt text-danger">
                        <i class="ti ti-alert-triangle"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Total Poin (Bulan Ini)</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['total_poin_bulan_ini']) }}</div>
                    </div>
                    <span class="avatar bg-pink-lt text-pink">
                        <i class="ti ti-gavel"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Santri Tercatat</div>
                        <div class="fs-2 fw-bold">{{ number_format($stats['santri_tercatat_bulan_ini']) }}</div>
                    </div>
                    <span class="avatar bg-yellow-lt text-yellow">
                        <i class="ti ti-users"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Absensi Hari Ini</h3>
                        <div class="text-secondary small mt-2">Rekap status kehadiran santri hari ini.</div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($todaySessions->isNotEmpty())
                        <div class="row g-3">
                            @foreach ($statusSummary as $item)
                                <div class="col-sm-6 col-lg">
                                    <div class="card card-sm">
                                        <div class="card-body text-center py-3">
                                            <div class="fs-1 fw-bold">{{ number_format($item['count']) }}</div>
                                            <div class="text-secondary small mt-1">
                                                <span class="badge {{ $recordBadgeClasses[$item['value']] ?? 'bg-secondary-lt text-secondary' }}">{{ $item['label'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="table-responsive mt-3">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Kegiatan</th>
                                        <th>Status</th>
                                        <th class="w-1">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($todaySessions as $session)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $session->activity?->name ?? '-' }}</div>
                                                <div class="text-secondary small">{{ $session->activity?->timeRangeLabel() ?? '-' }}</div>
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = match($session->status) {
                                                        'draft' => 'bg-secondary-lt text-secondary',
                                                        'open' => 'bg-primary-lt text-primary',
                                                        'completed' => 'bg-success-lt text-success',
                                                        default => 'bg-secondary-lt text-secondary',
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $session->statusLabel() }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('attendance.sessions.records.edit', $session) }}" class="btn btn-outline-primary btn-sm btn-icon" aria-label="Input absensi">
                                                    <i class="ti ti-clipboard-check"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-secondary py-4 text-center">Belum ada sesi absensi untuk hari ini.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Kegiatan Hari Ini</h3>
                        <div class="text-secondary small mt-2">Jadwal kegiatan rutin untuk hari {{ $today->translatedFormat('l') }}.</div>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($todayActivities as $activity)
                        <div class="list-group-item">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $activity->name }}</div>
                                    <div class="text-secondary small">
                                        <i class="ti ti-clock me-1"></i>{{ $activity->timeRangeLabel() }}
                                        @if ($activity->responsibleUser)
                                            <i class="ti ti-user ms-2 me-1"></i>{{ $activity->responsibleUser->name }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Tidak ada kegiatan rutin untuk hari ini.</div>
                    @endforelse
                </div>
                <div class="card-footer">
                    <a href="{{ route('attendance.activities.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="ti ti-calendar-time me-1"></i>
                        Kelola Kegiatan
                    </a>
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
                            <h3 class="card-title">Setoran Tahfidz Terbaru</h3>
                            <div class="text-secondary small mt-2">5 setoran hafalan terbaru yang Anda catat.</div>
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
                                <th>Ayat</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentTahfidz as $session)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $session->santri?->full_name ?? '-' }}</div>
                                        <div class="text-secondary small">NIS {{ $session->santri?->nis ?? '-' }}</div>
                                    </td>
                                    <td>{{ $session->session_date?->translatedFormat('d M Y') ?? '-' }}</td>
                                    <td>
                                        @php
                                            $totalAyat = $session->records->sum(fn ($r) => ($r->verse_end - $r->verse_start) + 1);
                                        @endphp
                                        {{ number_format($totalAyat) }} ayat
                                    </td>
                                    <td>
                                        <span class="badge {{ $session->status === 'completed' ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">
                                            {{ $session->statusLabel() }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-secondary">Belum ada setoran hafalan yang Anda catat.</td>
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
                            <h3 class="card-title">Pelanggaran Bulan Ini</h3>
                            <div class="text-secondary small mt-2">Pelanggaran santri binaan periode {{ $today->translatedFormat('F Y') }}.</div>
                        </div>
                        <a href="{{ route('pelanggaran.create') }}" class="btn btn-outline-danger btn-sm">
                            <i class="ti ti-plus me-1"></i>
                            Catat Pelanggaran
                        </a>
                    </div>
                </div>
                @if ($grafikKategori->isNotEmpty())
                    <div class="card-body">
                        <div class="chart-container" style="height: 200px;">
                            <canvas id="kategori-chart"></canvas>
                        </div>
                    </div>
                @else
                    <div class="card-body">
                        <div class="text-secondary text-center py-3">Belum ada data pelanggaran bulan ini.</div>
                    </div>
                @endif
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Santri Perlu Perhatian</h3>
                        <div class="text-secondary small mt-2">Poin pelanggaran tertinggi santri binaan.</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Santri</th>
                                <th>Total Poin</th>
                                <th>Pelanggaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($santriPelanggaranTertinggi as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->santri?->full_name ?? '-' }}</div>
                                        <div class="text-secondary small">NIS {{ $item->santri?->nis ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->total_poin >= 50 ? 'bg-danger-lt text-danger' : ($item->total_poin >= 20 ? 'bg-warning-lt text-warning' : 'bg-secondary-lt text-secondary') }}">
                                            {{ number_format($item->total_poin) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($item->total_kali) }}x</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-secondary">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2 w-100">
                        <div>
                            <h3 class="card-title">Santri Binaan &mdash; Akumulasi Poin Pelanggaran</h3>
                            <div class="text-secondary small mt-2">
                                {{ number_format($stats['santri_binaan']) }} santri binaan
                                @if ($santriBinaanWithPoin->isNotEmpty())
                                    &middot; Total poin: {{ number_format($santriBinaanWithPoin->sum('total_poin')) }}
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('pelanggaran.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-list me-1"></i> Lihat Semua Pelanggaran
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Santri</th>
                                <th>NIS</th>
                                <th>Kamar</th>
                                <th>Total Setoran</th>
                                <th>Total Pelanggaran</th>
                                <th>Total Poin</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($santriBinaanWithPoin as $santri)
                                @php
                                    $poin = $santri->total_poin;
                                    $statusBadge = $poin >= 50 ? 'bg-danger-lt text-danger' : ($poin >= 20 ? 'bg-warning-lt text-warning' : 'bg-success-lt text-success');
                                    $statusLabel = $poin >= 50 ? 'Kritis' : ($poin >= 20 ? 'Perhatian' : 'Baik');
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $santri->full_name }}</td>
                                    <td class="text-secondary">{{ $santri->nis }}</td>
                                    <td>{{ $santri->displayRoomName() }}</td>
                                    <td>{{ number_format($santri->total_setoran) }}x</td>
                                    <td>{{ number_format($santri->total_pelanggaran) }}x</td>
                                    <td>
                                        <span class="badge {{ $statusBadge }}">{{ number_format($poin) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-secondary">Belum ada santri binaan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
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
                            <div class="text-secondary small mt-2">10 pelanggaran terbaru santri binaan.</div>
                        </div>
                        <a href="{{ route('pelanggaran.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-list me-1"></i>
                            Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($recentPelanggaran as $item)
                        <div class="list-group-item">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $item->santri?->full_name ?? '-' }}</div>
                                    <div class="text-secondary small">
                                        <span class="badge bg-danger-lt text-danger">{{ $item->kategori?->nama ?? '-' }}</span>
                                        <span class="ms-2">{{ $item->tanggal?->translatedFormat('d M Y') ?? '-' }}</span>
                                        @if ($item->keterangan)
                                            <span class="ms-2" title="{{ $item->keterangan }}">&mdash; {{ Str::limit($item->keterangan, 40) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="badge bg-danger-lt text-danger fs-5">{{ number_format($item->poin) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Belum ada pelanggaran yang dicatat untuk santri binaan.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Jadwal Setoran Hari Ini</h3>
                        <div class="text-secondary small mt-2">Jadwal setoran tahfidz untuk hari {{ $today->translatedFormat('l') }}.</div>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($todaySchedules as $schedule)
                        <div class="list-group-item">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $schedule->timeRangeLabel() }}</div>
                                    <div class="text-secondary small">
                                        <i class="ti ti-users me-1"></i>Maks {{ $schedule->max_santri }} santri
                                        @if ($schedule->room)
                                            <i class="ti ti-building ms-2 me-1"></i>{{ $schedule->room->name }}
                                        @endif
                                    </div>
                                </div>
                                <span class="badge bg-primary-lt text-primary">{{ $schedule->max_santri }} slot</span>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Tidak ada jadwal setoran untuk hari ini.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2 w-100">
                        <div>
                            <h3 class="card-title">Jadwal Setoran Saya</h3>
                            <div class="text-secondary small mt-2">Semua jadwal setoran tahfidz Anda.</div>
                        </div>
                        <a href="{{ route('tahfidz.jadwal.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-list me-1"></i>
                            Kelola Jadwal
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Maks Santri</th>
                                <th>Ruangan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($allSchedules as $schedule)
                                <tr>
                                    <td class="fw-semibold">{{ $schedule->dayLabel() }}</td>
                                    <td>{{ $schedule->timeRangeLabel() }}</td>
                                    <td>{{ $schedule->max_santri }} santri</td>
                                    <td class="text-secondary">{{ $schedule->room?->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $schedule->is_active ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">
                                            {{ $schedule->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-secondary">Belum ada jadwal setoran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Semua Kegiatan Rutin</h3>
                <div class="text-secondary small mt-2">Jadwal kegiatan absensi yang aktif.</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kegiatan</th>
                        <th>Waktu</th>
                        <th>Hari Aktif</th>
                        <th>Penanggung Jawab</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activeActivities as $activity)
                        <tr>
                            <td class="fw-semibold">{{ $activity->name }}</td>
                            <td>{{ $activity->timeRangeLabel() }}</td>
                            <td>{{ $activity->activeDayLabels() }}</td>
                            <td class="text-secondary">{{ $activity->responsibleUser?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-secondary">Belum ada kegiatan rutin yang aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <a href="{{ route('attendance.activities.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                <i class="ti ti-settings me-1"></i>
                Kelola Kegiatan Absensi
            </a>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var canvas = document.getElementById('kategori-chart');
        if (!canvas) return;

        var labels = @json($grafikKategori->pluck('kategori.nama'));
        var data = @json($grafikKategori->pluck('total'));
        var colors = ['#d63939', '#f59f00', '#17a2b8', '#6f42c1', '#e83e8c', '#20c997', '#fd7e14', '#6610f2', '#dc3545', '#198754'];

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, labels.length),
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            padding: 12,
                            font: { size: 10 }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
