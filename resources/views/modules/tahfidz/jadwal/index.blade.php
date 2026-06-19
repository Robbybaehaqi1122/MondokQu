<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Jadwal Setoran Tahfidz</h2>
                <div class="text-secondary mt-1">Jadwal setoran hafalan per musyrif/ustadz.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('tahfidz.jadwal.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Jadwal
                </a>
            </div>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('tahfidz.jadwal.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari Musyrif</label>
                    <input type="text" name="q" class="form-control" placeholder="Nama musyrif..." value="{{ $filters['q'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Musyrif</label>
                    <select name="musyrif" class="form-select">
                        <option value="">Semua Musyrif</option>
                        @foreach ($musyrifOptions as $musyrif)
                            <option value="{{ $musyrif->id }}" @selected($filters['musyrif'] == $musyrif->id)>
                                {{ $musyrif->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hari</label>
                    <select name="day" class="form-select">
                        <option value="">Semua Hari</option>
                        @foreach ($daysOfWeek as $day)
                            <option value="{{ $day }}" @selected($filters['day'] == $day)>
                                @php
                                    $dayLabels = ['monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu', 'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu', 'sunday' => 'Minggu'];
                                @endphp
                                {{ $dayLabels[$day] ?? ucfirst($day) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-filter"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Musyrif</th>
                        <th>Hari</th>
                        <th>Jam</th>
                        <th>Maks Santri</th>
                        <th>Ruangan</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedules as $schedule)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $schedule->musyrif?->name ?? '-' }}</div>
                            </td>
                            <td>{{ $schedule->dayLabel() }}</td>
                            <td>{{ $schedule->timeRangeLabel() }}</td>
                            <td>{{ $schedule->max_santri }} santri</td>
                            <td>{{ $schedule->room?->name ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $schedule->is_active ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">
                                    {{ $schedule->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" data-bs-boundary="window" aria-expanded="false">
                                        Aksi
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="{{ route('tahfidz.jadwal.edit', $schedule) }}" class="dropdown-item">
                                            <i class="ti ti-edit me-2"></i>
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('tahfidz.jadwal.toggle-active', $schedule) }}" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="dropdown-item">
                                                <i class="ti ti-toggle-{{ $schedule->is_active ? 'left' : 'right' }} me-2"></i>
                                                {{ $schedule->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <div class="dropdown-divider"></div>
                                        <form method="POST" action="{{ route('tahfidz.jadwal.destroy', $schedule) }}" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="ti ti-trash me-2"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-secondary">Belum ada jadwal setoran tahfidz.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($schedules->hasPages())
            <div class="card-footer">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
