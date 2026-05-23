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
            <div class="text-secondary mt-1">Rekap kehadiran santri berdasarkan tanggal, kegiatan, kamar, santri, dan status.</div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Total Absensi</div>
                <div class="fs-2 fw-bold">{{ number_format($reportStats['records']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Sesi Tercatat</div>
                <div class="fs-2 fw-bold">{{ number_format($reportStats['sessions']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Santri Tercatat</div>
                <div class="fs-2 fw-bold">{{ number_format($reportStats['santris']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Perlu Perhatian</div>
                <div class="fs-2 fw-bold">{{ number_format($reportStats['issues']) }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <div>
                <h3 class="card-title">Filter Laporan</h3>
                <div class="text-secondary small mt-2">Periode {{ \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d M Y') }} sampai {{ \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d M Y') }}.</div>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('attendance.reports.index') }}" class="row g-3 align-items-end">
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
                <div class="col-12">
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-filter me-1"></i>
                            Filter
                        </button>
                        <a href="{{ route('attendance.reports.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
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

    <div class="card mb-3">
        <div class="card-header">
            <div>
                <h3 class="card-title">Santri Perlu Perhatian</h3>
                <div class="text-secondary small mt-2">Diurutkan dari total Izin, Sakit, Alpa, dan Terlambat terbanyak pada filter aktif.</div>
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
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kegiatan</th>
                        <th>Santri</th>
                        <th>Kamar</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th>Diinput</th>
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
                            <td>{{ $record->santri?->displayRoomName() ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $recordBadgeClasses[$record->status] ?? 'bg-secondary-lt text-secondary' }}">
                                    {{ $record->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                <span class="text-secondary small">{{ $record->notes ?: '-' }}</span>
                            </td>
                            <td>
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

        @if ($records->hasPages())
            <div class="card-footer">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
