<x-app-layout>
    @php
        $roomStatusBadgeClasses = [
            'active' => 'bg-success-lt text-success',
            'inactive' => 'bg-secondary-lt text-secondary',
        ];

        $leaveStatusBadgeClasses = [
            'pending' => 'bg-warning-lt text-warning',
            'approved' => 'bg-success-lt text-success',
            'rejected' => 'bg-danger-lt text-danger',
            'completed' => 'bg-info-lt text-info',
        ];
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="page-title">Laporan Kamar & Izin</h2>
            <div class="text-secondary mt-1">Okupansi kamar/asrama dan rekap izin santri.</div>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('pengurus.reports.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="month" class="form-label">Bulan Izin</label>
                    <input id="month" name="month" type="month" class="form-control" value="{{ $filters['month'] }}">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status Izin</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach ($statusOptions as $statusOption)
                            <option value="{{ $statusOption['value'] }}" @selected($filters['status'] === $statusOption['value'])>
                                {{ $statusOption['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="santri" class="form-label">Santri</label>
                    <select id="santri" name="santri" class="form-select">
                        <option value="">Semua Santri</option>
                        @foreach ($santris as $santri)
                            <option value="{{ $santri->id }}" @selected((string) $filters['santri'] === (string) $santri->id)>
                                {{ $santri->full_name }} ({{ $santri->nis }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                        <a href="{{ route('pengurus.reports.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Total Kamar</div>
                <div class="fs-2 fw-bold">{{ number_format($roomSummary['total']) }}</div>
                <div class="text-secondary small">{{ number_format($roomSummary['unlimited_rooms']) }} tanpa batas kapasitas</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Total Kapasitas</div>
                <div class="fs-2 fw-bold">{{ number_format((float) $roomSummary['capacity']) }}</div>
                <div class="text-secondary small">Kamar dengan kapasitas angka</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Terisi Aktif</div>
                <div class="fs-2 fw-bold">{{ number_format($roomSummary['occupied']) }}</div>
                <div class="text-secondary small">
                    {{ $roomSummary['occupancy_percentage'] === null ? 'Belum ada kapasitas' : $roomSummary['occupancy_percentage'].'% okupansi' }}
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Sisa Kapasitas</div>
                <div class="fs-2 fw-bold">{{ number_format($roomSummary['available']) }}</div>
                <div class="text-secondary small">{{ number_format($roomSummary['over_capacity']) }} kamar lebih kapasitas</div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Okupansi Per Kamar/Asrama</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kamar/Asrama</th>
                        <th>Status</th>
                        <th>Kapasitas</th>
                        <th>Santri Aktif</th>
                        <th>Sisa</th>
                        <th>Okupansi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roomReports as $room)
                        <tr>
                            <td class="fw-semibold">{{ $room['name'] }}</td>
                            <td>
                                <span class="badge {{ $roomStatusBadgeClasses[$room['status']] ?? 'bg-secondary-lt text-secondary' }}">
                                    {{ $room['status_label'] }}
                                </span>
                            </td>
                            <td>{{ $room['capacity'] === null ? 'Tidak dibatasi' : number_format($room['capacity']) }}</td>
                            <td>{{ number_format($room['active_santris_count']) }}</td>
                            <td>
                                @if ($room['is_over_capacity'])
                                    <span class="badge bg-danger-lt text-danger">Lebih kapasitas</span>
                                @else
                                    {{ $room['remaining_capacity'] === null ? 'Terbuka' : number_format($room['remaining_capacity']) }}
                                @endif
                            </td>
                            <td>
                                @if ($room['occupancy_percentage'] === null)
                                    <span class="text-secondary">Terbuka</span>
                                @else
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 0.5rem;">
                                            <div
                                                class="progress-bar {{ $room['is_over_capacity'] ? 'bg-danger' : 'bg-primary' }}"
                                                style="width: {{ $room['occupancy_percentage'] }}%;"
                                            ></div>
                                        </div>
                                        <span class="text-secondary small">{{ $room['occupancy_percentage'] }}%</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-secondary">Belum ada data kamar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Total Izin</div>
                <div class="fs-2 fw-bold">{{ number_format($leaveSummary['total']) }}</div>
                <div class="text-secondary small">{{ $leaveSummary['month_label'] }}</div>
            </div>
        </div>
        @foreach ($leaveStatusCounts->take(3) as $statusCount)
            <div class="col-sm-6 col-lg-3">
                <div class="card card-body">
                    <div class="text-uppercase text-secondary small">{{ $statusCount['label'] }}</div>
                    <div class="fs-2 fw-bold">{{ number_format($statusCount['count']) }}</div>
                    <div class="text-secondary small">Dalam bulan terpilih</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row row-cards">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Rekap Status Izin</h3>
                </div>
                <div class="list-group list-group-flush">
                    @foreach ($leaveStatusCounts as $statusCount)
                        <div class="list-group-item">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <span class="badge {{ $leaveStatusBadgeClasses[$statusCount['status']] ?? 'bg-secondary-lt text-secondary' }}">
                                    {{ $statusCount['label'] }}
                                </span>
                                <strong>{{ number_format($statusCount['count']) }}</strong>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Rekap Izin Per Bulan</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Total</th>
                                @foreach ($statusOptions as $statusOption)
                                    <th>{{ $statusOption['label'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($monthlyLeaveRecaps as $monthRecap)
                                <tr>
                                    <td class="fw-semibold">{{ $monthRecap['month_label'] }}</td>
                                    <td>{{ number_format($monthRecap['total']) }}</td>
                                    @foreach ($statusOptions as $statusOption)
                                        <td>{{ number_format($monthRecap['statuses'][$statusOption['value']] ?? 0) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Rekap Izin Per Santri</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Santri</th>
                        <th>Total</th>
                        <th>Menunggu</th>
                        <th>Disetujui</th>
                        <th>Ditolak</th>
                        <th>Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($santriLeaveRecaps as $santriRecap)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $santriRecap['santri_name'] }}</div>
                                <div class="text-secondary small">NIS: {{ $santriRecap['nis'] }}</div>
                            </td>
                            <td>{{ number_format($santriRecap['total']) }}</td>
                            <td>{{ number_format($santriRecap['pending']) }}</td>
                            <td>{{ number_format($santriRecap['approved']) }}</td>
                            <td>{{ number_format($santriRecap['rejected']) }}</td>
                            <td>{{ number_format($santriRecap['completed']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-secondary">Belum ada izin pada filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
