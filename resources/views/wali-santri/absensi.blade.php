<x-app-layout>
    @php
        $statusBadgeClasses = [
            'present' => 'bg-success-lt text-success',
            'permission' => 'bg-azure-lt text-azure',
            'sick' => 'bg-warning-lt text-warning',
            'absent' => 'bg-danger-lt text-danger',
            'late' => 'bg-orange-lt text-orange',
        ];
    @endphp

    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Riwayat Absensi</h2>
                <div class="text-secondary mt-1">{{ $santri->full_name }} &middot; NIS {{ $santri->nis }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('wali-santri.profil-santri', $santri) }}" class="btn btn-outline-primary">
                    <i class="ti ti-user me-1"></i>
                    Profil
                </a>
                <a href="{{ route('wali-santri.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter Tanggal</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('wali-santri.absensi', $santri) }}" class="row g-3">
                <div class="col-md-4">
                    <label for="date_from" class="form-label">Dari Tanggal</label>
                    <input
                        id="date_from"
                        name="date_from"
                        type="date"
                        class="form-control"
                        value="{{ $dateFrom }}"
                        required
                    >
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">Sampai Tanggal</label>
                    <input
                        id="date_to"
                        name="date_to"
                        type="date"
                        class="form-control"
                        value="{{ $dateTo }}"
                        required
                    >
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-filter me-1"></i>
                        Filter
                    </button>
                    <a href="{{ route('wali-santri.absensi', $santri) }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <div>
                <h3 class="card-title">Catatan Absensi</h3>
                <div class="text-secondary small mt-1">Total {{ $records->total() }} catatan ditemukan.</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kegiatan</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th>Diinput</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>{{ $record->session?->session_date?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td>{{ $record->session?->activity?->name ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $statusBadgeClasses[$record->status] ?? 'bg-secondary-lt text-secondary' }}">
                                    {{ $record->statusLabel() }}
                                </span>
                            </td>
                            <td class="text-secondary">{{ $record->notes ?: '-' }}</td>
                            <td class="text-secondary small">{{ $record->recorded_at?->translatedFormat('d M Y H:i') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-secondary">Belum ada catatan absensi untuk periode ini.</td>
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
