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
                <div class="text-secondary mt-1">
                    Pantauan absensi real-time untuk {{ $today->translatedFormat('d M Y') }}.
                    <span class="badge bg-green-lt text-green ms-1" id="liveIndicator">
                        <i class="ti ti-refresh me-1"></i>
                        Live
                    </span>
                </div>
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

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('attendance.dashboard') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="room" class="form-label">Filter Kamar</label>
                    <select id="room" name="room" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kamar</option>
                        @foreach ($roomOptions as $roomOption)
                            <option value="{{ $roomOption->id }}" @selected($selectedRoomId === $roomOption->id)>
                                {{ $roomOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if ($selectedRoomId)
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('attendance.dashboard') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-width-0">
                        <div class="text-uppercase text-secondary small text-truncate">Total Santri</div>
                        <div class="fs-2 fw-bold" id="stat-total-santri">{{ number_format($activeSantriCount) }}</div>
                    </div>
                    <span class="avatar bg-primary-lt text-primary flex-shrink-0">
                        <i class="ti ti-users"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-width-0">
                        <div class="text-uppercase text-secondary small text-truncate">Sudah Absen</div>
                        <div class="fs-2 fw-bold text-success" id="stat-sudah-absen">{{ number_format($attendedCount) }}</div>
                    </div>
                    <span class="avatar bg-success-lt text-success flex-shrink-0">
                        <i class="ti ti-check"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-width-0">
                        <div class="text-uppercase text-secondary small text-truncate">Belum Absen</div>
                        <div class="fs-2 fw-bold {{ $notAttendedCount > 0 ? 'text-danger' : 'text-success' }}" id="stat-belum-absen">{{ number_format($notAttendedCount) }}</div>
                    </div>
                    <span class="avatar bg-warning-lt text-warning flex-shrink-0">
                        <i class="ti ti-clock"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-width-0">
                        <div class="text-uppercase text-secondary small text-truncate">% Kehadiran</div>
                        <div class="fs-2 fw-bold {{ $attendancePercentage >= 90 ? 'text-success' : ($attendancePercentage >= 75 ? 'text-warning' : 'text-danger') }}" id="stat-kehadiran">
                            {{ number_format($attendancePercentage, 1) }}%
                        </div>
                    </div>
                    <span class="avatar bg-azure-lt text-azure flex-shrink-0">
                        <i class="ti ti-chart-arcs"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-3">
        @foreach ($statusSummary as $statusItem)
            <div class="col-6 col-sm-4 col-lg">
                <div class="card card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-width-0">
                            <div class="text-uppercase text-secondary small text-truncate">{{ $statusItem['label'] }}</div>
                            <div class="fs-2 fw-bold" id="status-count-{{ $statusItem['value'] }}">{{ number_format($statusItem['count']) }}</div>
                        </div>
                        <span class="badge {{ $recordBadgeClasses[$statusItem['value']] ?? 'bg-secondary-lt text-secondary' }} flex-shrink-0">
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
                            <div class="text-secondary small mt-2">
                                Pantau progres input absensi untuk semua sesi hari ini.
                                <span id="dashboard-stats-info">
                                @if ($dashboardStats['open_sessions'] > 0 || $dashboardStats['needs_input'] > 0)
                                    <span class="badge bg-blue-lt text-blue ms-1">{{ $dashboardStats['open_sessions'] }} dibuka, {{ $dashboardStats['needs_input'] }} perlu input</span>
                                @endif
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('attendance.sessions.index', ['date_from' => $today->toDateString(), 'date_to' => $today->toDateString()]) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-list me-1"></i>
                            Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-mobile-md">
                        <thead>
                            <tr>
                                <th>Kegiatan</th>
                                <th>Status</th>
                                <th>Terisi</th>
                                <th class="d-none d-md-table-cell">Perhatian</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="today-sessions-tbody">
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
                                    <td class="d-none d-md-table-cell">
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
                        <h3 class="card-title">Santri Belum Absen</h3>
                        <div class="text-secondary small mt-2" id="not-attended-desc">
                            @if ($notAttendedCount > 0)
                                {{ number_format($notAttendedCount) }} santri belum tercatat hari ini.
                            @else
                                Semua santri sudah absen hari ini.
                            @endif
                        </div>
                    </div>
                </div>
                <div class="list-group list-group-flush" style="max-height: 320px; overflow-y: auto;" id="not-attended-list">
                    @forelse ($notAttendedSantris as $santri)
                        <div class="list-group-item">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div class="min-width-0">
                                    <div class="fw-semibold text-truncate">{{ $santri->full_name }}</div>
                                    <div class="text-secondary small">
                                        NIS {{ $santri->nis ?: '-' }}
                                        @if ($santri->room)
                                            &middot; {{ $santri->room->name }}
                                        @endif
                                    </div>
                                </div>
                                <span class="badge bg-warning-lt text-warning flex-shrink-0">Belum</span>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ti ti-check text-success"></i>
                                Semua santri sudah absen hari ini.
                            </div>
                        </div>
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
                <tbody id="attention-santris-tbody">
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const indicator = document.getElementById('liveIndicator');
        if (!indicator) return;

        let countdown = 30;

        setInterval(function () {
            countdown--;
            if (countdown <= 0) {
                countdown = 30;
                refreshDashboard();
            } else {
                indicator.textContent = 'Live \u2022 ' + countdown + 's';
            }
        }, 1000);

        const sessionBadgeClasses = {
            draft: 'bg-secondary-lt text-secondary',
            open: 'bg-primary-lt text-primary',
            completed: 'bg-success-lt text-success',
        };

        const recordBadgeClasses = {
            present: 'bg-success-lt text-success',
            permission: 'bg-azure-lt text-azure',
            sick: 'bg-warning-lt text-warning',
            absent: 'bg-danger-lt text-danger',
            late: 'bg-orange-lt text-orange',
        };

        function nf(n) {
            return n.toLocaleString('en-US');
        }

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        async function refreshDashboard() {
            indicator.textContent = 'Memperbarui\u2026';
            indicator.className = 'badge bg-yellow-lt text-yellow ms-1';

            try {
                var params = new URLSearchParams(location.search);
                var room = params.get('room');
                var url = '/absen/api/dashboard' + (room ? '?room=' + encodeURIComponent(room) : '');
                var res = await fetch(url);
                var data = await res.json();

                updateDashboard(data);

                indicator.textContent = 'Live \u2022 30s';
                indicator.className = 'badge bg-green-lt text-green ms-1';
            } catch (e) {
                indicator.textContent = 'Gagal memperbarui';
                indicator.className = 'badge bg-red-lt text-red ms-1';
            }
        }

        function updateDashboard(data) {
            document.getElementById('stat-total-santri').textContent = nf(data.activeSantriCount);
            document.getElementById('stat-sudah-absen').textContent = nf(data.attendedCount);

            var belumEl = document.getElementById('stat-belum-absen');
            belumEl.textContent = nf(data.notAttendedCount);
            belumEl.className = 'fs-2 fw-bold ' + (data.notAttendedCount > 0 ? 'text-danger' : 'text-success');

            var pct = data.attendancePercentage;
            var persenEl = document.getElementById('stat-kehadiran');
            persenEl.textContent = pct.toFixed(1) + '%';
            persenEl.className = 'fs-2 fw-bold ' + (pct >= 90 ? 'text-success' : (pct >= 75 ? 'text-warning' : 'text-danger'));

            data.statusSummary.forEach(function (item) {
                var el = document.getElementById('status-count-' + item.value);
                if (el) el.textContent = nf(item.count);
            });

            var ds = data.dashboardStats;
            var infoEl = document.getElementById('dashboard-stats-info');
            if (infoEl) {
                infoEl.innerHTML = (ds.open_sessions > 0 || ds.needs_input > 0)
                    ? '<span class="badge bg-blue-lt text-blue ms-1">' + ds.open_sessions + ' dibuka, ' + ds.needs_input + ' perlu input</span>'
                    : '';
            }

            var tbody = document.getElementById('today-sessions-tbody');
            if (tbody) {
                if (data.todaySessions.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-secondary">Belum ada sesi absensi untuk hari ini.</td></tr>';
                } else {
                    tbody.innerHTML = data.todaySessions.map(function (s) {
                        var progress = Math.min(100, Math.round((s.records_count / data.activeSantriCount) * 100));
                        var badgeClass = sessionBadgeClasses[s.status] || 'bg-secondary-lt text-secondary';
                        var issueBadge = s.issue_records_count > 0
                            ? '<span class="badge bg-warning-lt text-warning">' + nf(s.issue_records_count) + ' catatan</span>'
                            : '<span class="text-secondary small">-</span>';
                        return '<tr>' +
                            '<td><div class="fw-semibold">' + escapeHtml(s.activity_name || '-') + '</div><div class="text-secondary small">' + escapeHtml(s.activity_time_range || '-') + '</div></td>' +
                            '<td><span class="badge ' + badgeClass + '">' + escapeHtml(s.status_label) + '</span></td>' +
                            '<td><div class="d-flex align-items-center gap-2"><div class="progress flex-fill" style="height: 0.45rem;"><div class="progress-bar" style="width: ' + progress + '%"></div></div><span class="small text-secondary text-nowrap">' + s.records_count + '/' + data.activeSantriCount + '</span></div></td>' +
                            '<td>' + issueBadge + '</td>' +
                            '<td><a href="' + s.edit_url + '" class="btn btn-outline-primary btn-sm btn-icon" aria-label="Input absensi"><i class="ti ti-clipboard-check"></i></a></td>' +
                            '</tr>';
                    }).join('');
                }
            }

            var naList = document.getElementById('not-attended-list');
            if (naList) {
                if (data.notAttendedSantris.length === 0) {
                    naList.innerHTML = '<div class="list-group-item text-secondary"><div class="d-flex align-items-center gap-2"><i class="ti ti-check text-success"></i> Semua santri sudah absen hari ini.</div></div>';
                } else {
                    naList.innerHTML = data.notAttendedSantris.map(function (s) {
                        return '<div class="list-group-item"><div class="d-flex align-items-start justify-content-between gap-3"><div class="min-width-0"><div class="fw-semibold text-truncate">' + escapeHtml(s.full_name) + '</div><div class="text-secondary small">NIS ' + (s.nis || '-') + (s.room_name ? ' &middot; ' + escapeHtml(s.room_name) : '') + '</div></div><span class="badge bg-warning-lt text-warning">Belum</span></div></div>';
                    }).join('');
                }
            }

            var naDesc = document.getElementById('not-attended-desc');
            if (naDesc) {
                naDesc.textContent = data.notAttendedCount > 0
                    ? nf(data.notAttendedCount) + ' santri belum tercatat hari ini.'
                    : 'Semua santri sudah absen hari ini.';
            }

            var atTbody = document.getElementById('attention-santris-tbody');
            if (atTbody) {
                if (data.attentionSantris.length === 0) {
                    atTbody.innerHTML = '<tr><td colspan="6" class="text-secondary">Belum ada santri yang perlu perhatian dalam 7 hari terakhir.</td></tr>';
                } else {
                    atTbody.innerHTML = data.attentionSantris.map(function (s) {
                        return '<tr>' +
                            '<td><div class="fw-semibold">' + escapeHtml(s.full_name) + '</div><div class="text-secondary small">NIS ' + (s.nis || '-') + '</div></td>' +
                            '<td>' + nf(s.permission_count) + '</td>' +
                            '<td>' + nf(s.sick_count) + '</td>' +
                            '<td>' + nf(s.absent_count) + '</td>' +
                            '<td>' + nf(s.late_count) + '</td>' +
                            '<td class="fw-semibold">' + nf(s.issue_total) + '</td>' +
                            '</tr>';
                    }).join('');
                }
            }
        }
    });
</script>