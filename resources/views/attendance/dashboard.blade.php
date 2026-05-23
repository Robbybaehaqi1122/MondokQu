<x-app-layout>
    @php
        $sessionBadgeClasses = [
            'draft' => 'bg-secondary-lt text-secondary',
            'open' => 'bg-primary-lt text-primary',
            'completed' => 'bg-success-lt text-success',
        ];
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
                <h2 class="page-title">Dashboard Absensi</h2>
                <div class="text-secondary mt-1">Ringkasan operasional AbsenQu untuk {{ $today->translatedFormat('d M Y') }}.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('attendance.sessions.index') }}" class="btn btn-outline-primary">
                    <i class="ti ti-calendar-time me-1"></i>
                    Sesi Harian
                </a>
                <a href="{{ route('attendance.reports.index') }}" class="btn btn-primary">
                    <i class="ti ti-report-analytics me-1"></i>
                    Laporan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Sesi Hari Ini</div>
                        <div class="fs-2 fw-bold">{{ number_format($dashboardStats['sessions_today']) }}</div>
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
                        <div class="text-uppercase text-secondary small">Belum Lengkap</div>
                        <div class="fs-2 fw-bold">{{ number_format($dashboardStats['needs_input']) }}</div>
                    </div>
                    <span class="avatar bg-warning-lt text-warning">
                        <i class="ti ti-clipboard-list"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Sesi Dibuka</div>
                        <div class="fs-2 fw-bold">{{ number_format($dashboardStats['open_sessions']) }}</div>
                    </div>
                    <span class="avatar bg-blue-lt text-blue">
                        <i class="ti ti-lock-open"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Santri Aktif</div>
                        <div class="fs-2 fw-bold">{{ number_format($activeSantriCount) }}</div>
                    </div>
                    <span class="avatar bg-success-lt text-success">
                        <i class="ti ti-users"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-3">
        @foreach ($statusSummary as $statusItem)
            <div class="col-sm-6 col-lg">
                <div class="card card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="text-uppercase text-secondary small">{{ $statusItem['label'] }}</div>
                            <div class="fs-2 fw-bold">{{ number_format($statusItem['count']) }}</div>
                        </div>
                        <span class="badge {{ $recordBadgeClasses[$statusItem['value']] ?? 'bg-secondary-lt text-secondary' }}">
                            {{ $statusItem['label'] }}
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row row-cards mb-3">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2 w-100">
                        <div>
                            <h3 class="card-title">Sesi Hari Ini</h3>
                            <div class="text-secondary small mt-2">Pantau progres input absensi untuk semua sesi hari ini.</div>
                        </div>
                        <a href="{{ route('attendance.sessions.index', ['date_from' => $today->toDateString(), 'date_to' => $today->toDateString()]) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-list me-1"></i>
                            Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Kegiatan</th>
                                <th>Status</th>
                                <th>Terisi</th>
                                <th>Perhatian</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($todaySessions as $session)
                                @php
                                    $recordsCount = (int) $session->records_count;
                                    $inputProgress = $activeSantriCount > 0 ? min(100, round(($recordsCount / $activeSantriCount) * 100)) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $session->activity?->name ?? '-' }}</div>
                                        <div class="text-secondary small">{{ $session->activity?->timeRangeLabel() ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $sessionBadgeClasses[$session->status] ?? 'bg-secondary-lt text-secondary' }}">
                                            {{ $session->statusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-fill" style="height: 0.45rem;">
                                                <div class="progress-bar" style="width: {{ $inputProgress }}%"></div>
                                            </div>
                                            <span class="small text-secondary text-nowrap">{{ $recordsCount }}/{{ $activeSantriCount }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if ((int) $session->issue_records_count > 0)
                                            <span class="badge bg-warning-lt text-warning">{{ number_format((int) $session->issue_records_count) }} catatan</span>
                                        @else
                                            <span class="text-secondary small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('attendance.sessions.records.edit', $session) }}" class="btn btn-outline-primary btn-sm btn-icon" aria-label="Input absensi">
                                            <i class="ti ti-clipboard-check"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-secondary">Belum ada sesi absensi untuk hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Belum Lengkap</h3>
                        <div class="text-secondary small mt-2">Sesi hari ini yang masih perlu dilengkapi.</div>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($sessionsNeedingInput as $session)
                        <a href="{{ route('attendance.sessions.records.edit', $session) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div class="min-width-0">
                                    <div class="fw-semibold text-truncate">{{ $session->activity?->name ?? '-' }}</div>
                                    <div class="text-secondary small">{{ number_format((int) $session->records_count) }} dari {{ number_format($activeSantriCount) }} santri terisi</div>
                                </div>
                                <i class="ti ti-chevron-right text-secondary"></i>
                            </div>
                        </a>
                    @empty
                        <div class="list-group-item text-secondary">Semua sesi hari ini sudah lengkap atau belum ada sesi.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2 w-100">
                <div>
                    <h3 class="card-title">Santri Perlu Perhatian</h3>
                    <div class="text-secondary small mt-2">Akumulasi Izin, Sakit, Alpa, dan Terlambat dalam 7 hari terakhir.</div>
                </div>
                <a href="{{ route('attendance.reports.index', ['date_from' => $today->copy()->subDays(6)->toDateString(), 'date_to' => $today->toDateString()]) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ti ti-report-analytics me-1"></i>
                    Buka Laporan
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
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
                            <td colspan="6" class="text-secondary">Belum ada santri yang perlu perhatian dalam 7 hari terakhir.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
