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
        <div>
            <h2 class="page-title">Laporan Absensi</h2>
            <div class="text-secondary mt-1">
                @if ($viewMode === 'detail')
                    Rekap kehadiran santri berdasarkan tanggal, kegiatan, kamar, santri, dan status.
                @else
                    Ringkasan absensi per santri berdasarkan filter aktif.
                @endif
            </div>
        </div>
    </x-slot>

    <div class="mb-3">
        <ul class="nav nav-tabs nav-tabs-alt">
            <li class="nav-item">
                <a class="nav-link {{ $viewMode === 'detail' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['view_mode' => 'detail']) }}">
                    <i class="ti ti-list me-1"></i>
                    Detail
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $viewMode === 'rekap' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['view_mode' => 'rekap']) }}">
                    <i class="ti ti-users me-1"></i>
                    Rekap per Santri
                </a>
            </li>
        </ul>
    </div>

    @if ($viewMode === 'rekap')
        <div class="row row-cards mb-3">
            <div class="col-6 col-lg-4">
                <div class="card card-body stat-card">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-width-0">
                            <div class="text-uppercase text-secondary small text-truncate">Total Santri</div>
                            <div class="fs-2 fw-bold">{{ number_format($rekapStats['total_santri']) }}</div>
                        </div>
                        <span class="avatar bg-blue-lt text-blue flex-shrink-0">
                            <i class="ti ti-users"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="card card-body stat-card">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-width-0">
                            <div class="text-uppercase text-secondary small text-truncate">Rata-rata Hadir</div>
                            <div class="fs-2 fw-bold">{{ number_format($rekapStats['avg_percentage'], 1) }}%</div>
                        </div>
                        <span class="avatar bg-green-lt text-green flex-shrink-0">
                            <i class="ti ti-chart-arcs"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card card-body stat-card">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-width-0">
                            <div class="text-uppercase text-secondary small text-truncate">Periode</div>
                            <div class="fs-2 fw-bold small">
                                {{ \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d M Y') }}
                                &mdash;
                                {{ \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d M Y') }}
                            </div>
                        </div>
                        <span class="avatar bg-azure-lt text-azure flex-shrink-0">
                            <i class="ti ti-calendar-range"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row row-cards mb-3">
            <div class="col-6 col-lg-3">
                <div class="card card-body stat-card">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-width-0">
                            <div class="text-uppercase text-secondary small text-truncate">Total Absensi</div>
                            <div class="fs-2 fw-bold">{{ number_format($reportStats['records']) }}</div>
                        </div>
                        <span class="avatar bg-blue-lt text-blue flex-shrink-0">
                            <i class="ti ti-clipboard-list"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card card-body stat-card">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-width-0">
                            <div class="text-uppercase text-secondary small text-truncate">Sesi Tercatat</div>
                            <div class="fs-2 fw-bold">{{ number_format($reportStats['sessions']) }}</div>
                        </div>
                        <span class="avatar bg-azure-lt text-azure flex-shrink-0">
                            <i class="ti ti-calendar-event"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card card-body stat-card">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-width-0">
                            <div class="text-uppercase text-secondary small text-truncate">Santri Tercatat</div>
                            <div class="fs-2 fw-bold">{{ number_format($reportStats['santris']) }}</div>
                        </div>
                        <span class="avatar bg-green-lt text-green flex-shrink-0">
                            <i class="ti ti-user-check"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card card-body stat-card">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-width-0">
                            <div class="text-uppercase text-secondary small text-truncate">Perlu Perhatian</div>
                            <div class="fs-2 fw-bold {{ $reportStats['issues'] > 0 ? 'text-danger' : '' }}">{{ number_format($reportStats['issues']) }}</div>
                        </div>
                        <span class="avatar {{ $reportStats['issues'] > 0 ? 'bg-danger-lt text-danger' : 'bg-secondary-lt text-secondary' }} flex-shrink-0">
                            <i class="ti ti-alert-triangle"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header">
            <div>
                <h3 class="card-title">Filter Laporan</h3>
                <div class="text-secondary small mt-2">Periode {{ \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d M Y') }} sampai {{ \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d M Y') }}.</div>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('attendance.reports.index') }}" class="row g-3 align-items-end">
                <input type="hidden" name="view_mode" value="{{ $viewMode }}">
                <div class="col-md-6 col-lg-2">
                    <label for="date_from" class="form-label">Dari Tanggal</label>
                    <input id="date_from" name="date_from" type="date" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-md-6 col-lg-2">
                    <label for="date_to" class="form-label">Sampai Tanggal</label>
                    <input id="date_to" name="date_to" type="date" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-md-6 col-lg-2">
                    <label for="activity" class="form-label">Kegiatan</label>
                    <select id="activity" name="activity" class="form-select">
                        <option value="">Semua</option>
                        @foreach ($activityOptions as $activityOption)
                            <option value="{{ $activityOption->id }}" @selected($filters['activity'] === (string) $activityOption->id)>
                                {{ $activityOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if ($viewMode === 'detail')
                    <div class="col-md-6 col-lg-2">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">Semua</option>
                            @foreach ($statusOptions as $statusOption)
                                <option value="{{ $statusOption['value'] }}" @selected($filters['status'] === $statusOption['value'])>
                                    {{ $statusOption['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-6 col-lg-2">
                    <label for="room" class="form-label">Kamar</label>
                    <select id="room" name="room" class="form-select">
                        <option value="">Semua</option>
                        @foreach ($roomOptions as $roomOption)
                            <option value="{{ $roomOption->id }}" @selected($filters['room'] === (string) $roomOption->id)>
                                {{ $roomOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if ($viewMode === 'detail')
                    <div class="col-md-6 col-lg-2">
                        <label for="santri" class="form-label">Santri</label>
                        <select id="santri" name="santri" class="form-select">
                            <option value="">Semua</option>
                            @foreach ($santriOptions as $santriOption)
                                <option value="{{ $santriOption->id }}" @selected($filters['santri'] === (string) $santriOption->id)>
                                    {{ $santriOption->full_name }}{{ $santriOption->nis ? ' - '.$santriOption->nis : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-12">
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-filter me-1"></i>
                            Filter
                        </button>
                        <a href="{{ route('attendance.reports.index', ['view_mode' => $viewMode]) }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($viewMode === 'detail')
        <div class="row row-cards mb-3">
            @foreach ($statusSummary as $statusItem)
                <div class="col-6 col-sm-4 col-lg">
                    <div class="card card-body stat-card">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div class="min-width-0">
                                <div class="text-uppercase text-secondary small text-truncate">{{ $statusItem['label'] }}</div>
                                <div class="fs-2 fw-bold">{{ number_format($statusItem['count']) }}</div>
                            </div>
                            <span class="avatar {{ $recordBadgeClasses[$statusItem['value']] ?? 'bg-secondary-lt text-secondary' }} flex-shrink-0">
                                {{ strtoupper(substr($statusItem['label'], 0, 1)) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Santri Perlu Perhatian</h3>
                    <div class="text-secondary small mt-2">Diurutkan dari total Izin, Sakit, Alpa, dan Terlambat terbanyak pada filter aktif.</div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-mobile-md">
                    <thead>
                        <tr>
                            <th>Santri</th>
                            <th>Izin</th>
                            <th>Sakit</th>
                            <th>Alpa</th>
                            <th>Terlambat</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attentionSantris as $attentionSantri)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $attentionSantri->full_name }}</div>
                                    <div class="text-secondary small">NIS {{ $attentionSantri->nis }}</div>
                                </td>
                                <td>{{ number_format((int) $attentionSantri->permission_count) }}</td>
                                <td>{{ number_format((int) $attentionSantri->sick_count) }}</td>
                                <td>{{ number_format((int) $attentionSantri->absent_count) }}</td>
                                <td>{{ number_format((int) $attentionSantri->late_count) }}</td>
                                <td class="fw-semibold">{{ number_format((int) $attentionSantri->issue_total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-secondary">Belum ada catatan yang perlu perhatian pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Detail Absensi</h3>
                    <div class="text-secondary small mt-2">Menampilkan {{ $records->total() }} catatan absensi berdasarkan filter aktif.</div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-mobile-md">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kegiatan</th>
                            <th>Santri</th>
                            <th class="d-none d-md-table-cell">Kamar</th>
                            <th>Status</th>
                            <th class="d-none d-lg-table-cell">Catatan</th>
                            <th class="d-none d-md-table-cell">Diinput</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            <tr>
                                <td>{{ $record->session?->session_date?->translatedFormat('d M Y') ?? '-' }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $record->session?->activity?->name ?? '-' }}</div>
                                    <div class="text-secondary small">{{ $record->session?->activity?->timeRangeLabel() ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $record->santri?->full_name ?? '-' }}</div>
                                    <div class="text-secondary small">NIS {{ $record->santri?->nis ?? '-' }}</div>
                                </td>
                                <td class="d-none d-md-table-cell">{{ $record->santri?->displayRoomName() ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $recordBadgeClasses[$record->status] ?? 'bg-secondary-lt text-secondary' }}">
                                        {{ $record->statusLabel() }}
                                    </span>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <span class="text-secondary small">{{ $record->notes ?: '-' }}</span>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    @if ($record->recorded_at)
                                        <div>{{ $record->recorded_at->translatedFormat('d M Y H:i') }}</div>
                                        <div class="text-secondary small">{{ $record->recorder?->name ?? '-' }}</div>
                                    @else
                                        <span class="text-secondary small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-secondary">Belum ada catatan absensi pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($records->hasPages() || true)
                <div class="card-footer">
                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                        <div>
                            @if ($records->hasPages())
                                {{ $records->links() }}
                            @endif
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-file-download me-1"></i>
                                Export
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('attendance.reports.export-detail', ['xlsx'] + request()->query()) }}">
                                        <i class="ti ti-file-spreadsheet me-2 text-success"></i>
                                        Excel (.xlsx)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('attendance.reports.export-detail', ['csv'] + request()->query()) }}">
                                        <i class="ti ti-file-text me-2 text-secondary"></i>
                                        CSV (.csv)
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        @if ($chartData && count($chartData['labels']) > 0)
            <div class="card mb-3">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Grafik Kehadiran {{ \Carbon\Carbon::parse($filters['date_from'])->format('Y') }}</h3>
                        <div class="text-secondary small mt-2">Perbandingan Hadir vs Issue (Izin+Sakit+Alpa+Terlambat) per bulan.</div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="rekapChart" height="100"></canvas>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Rekap Absensi per Santri</h3>
                    <div class="text-secondary small mt-2">
                        {{ $rekap->count() }} santri aktif.
                        @if ($filters['room'])
                            Kamar: {{ $roomOptions->firstWhere('id', (int) $filters['room'])?->name ?? '-' }}
                        @else
                            Semua kamar.
                        @endif
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-mobile-md">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Santri</th>
                            <th class="d-none d-md-table-cell">NIS</th>
                            <th class="d-none d-md-table-cell">Kamar</th>
                            <th>Hadir</th>
                            <th>Sakit</th>
                            <th>Izin</th>
                            <th>Alpa</th>
                            <th>Telat</th>
                            <th>Total</th>
                            <th>% Hadir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rekap as $item)
                            @php
                                $pctClass = $item['percentage'] >= 90 ? 'text-success fw-bold' : ($item['percentage'] >= 75 ? 'text-warning fw-bold' : 'text-danger fw-bold');
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $item['full_name'] }}</td>
                                <td class="d-none d-md-table-cell">{{ $item['nis'] ?: '-' }}</td>
                                <td class="d-none d-md-table-cell">{{ $item['room_name'] }}</td>
                                <td>{{ number_format($item['present']) }}</td>
                                <td>{{ number_format($item['sick']) }}</td>
                                <td>{{ number_format($item['permission']) }}</td>
                                <td>{{ number_format($item['absent']) }}</td>
                                <td>{{ number_format($item['late']) }}</td>
                                <td class="fw-semibold">{{ number_format($item['total']) }}</td>
                                <td class="{{ $pctClass }}">{{ $item['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-secondary">Belum ada data absensi pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                        <i class="ti ti-printer me-1"></i>
                        Cetak
                    </button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-file-download me-1"></i>
                            Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('attendance.reports.export-rekap', ['xlsx'] + request()->query()) }}">
                                    <i class="ti ti-file-spreadsheet me-2 text-success"></i>
                                    Excel (.xlsx)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('attendance.reports.export-rekap', ['csv'] + request()->query()) }}">
                                    <i class="ti ti-file-text me-2 text-secondary"></i>
                                    CSV (.csv)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('attendance.reports.pdf', request()->query()) }}">
                                    <i class="ti ti-file-type-pdf me-2 text-danger"></i>
                                    PDF
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>

@if ($viewMode === 'rekap' && $chartData && count($chartData['labels']) > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('rekapChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [
                        {
                            label: 'Hadir',
                            data: @json($chartData['present']),
                            backgroundColor: '#2fb344',
                            borderRadius: 4,
                        },
                        {
                            label: 'Issue',
                            data: @json($chartData['issues']),
                            backgroundColor: '#d63939',
                            borderRadius: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                        },
                    },
                },
            });
        });
    </script>
@endif