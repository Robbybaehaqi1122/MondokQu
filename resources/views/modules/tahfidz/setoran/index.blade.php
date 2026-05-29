<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Setoran Hafalan</h2>
                <div class="text-secondary mt-1">Daftar setoran hafalan Al-Quran santri.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('tahfidz.setoran.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Catat Setoran Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('tahfidz.setoran.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari Santri</label>
                    <input type="text" name="q" class="form-control" placeholder="Nama atau NIS..." value="{{ $filters['q'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Santri</label>
                    <select name="santri" class="form-select">
                        <option value="">Semua Santri</option>
                        @foreach ($santriOptions as $santri)
                            <option value="{{ $santri->id }}" @selected($filters['santri'] == $santri->id)>
                                {{ $santri->full_name }} ({{ $santri->nis }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
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
                        <th>Santri</th>
                        <th>Tanggal</th>
                        <th>Musyrif</th>
                        <th>Ayat Disetor</th>
                        <th>Penilaian</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $session->santri?->full_name ?? '-' }}</div>
                                <div class="text-secondary small">NIS {{ $session->santri?->nis ?? '-' }}</div>
                            </td>
                            <td>{{ $session->session_date?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td>{{ $session->musyrif?->name ?? '-' }}</td>
                            <td>
                                @foreach ($session->records as $record)
                                    <div class="small">
                                        {{ $record->surah?->name ?? '-' }}: {{ $record->verseRangeLabel() }}
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                @foreach ($session->records as $record)
                                    <div class="small">
                                        @php
                                            $evalBadge = match ($record->evaluation) {
                                                'lancar' => 'bg-success-lt text-success',
                                                'perlu_pengulangan' => 'bg-warning-lt text-warning',
                                                'belum_lancar' => 'bg-danger-lt text-danger',
                                                default => 'bg-secondary-lt text-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $evalBadge }}">{{ $record->evaluationLabel() }}</span>
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge {{ $session->status === 'completed' ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">
                                    {{ $session->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('tahfidz.setoran.show', $session) }}" class="btn btn-outline-primary btn-sm btn-icon" aria-label="Detail">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('tahfidz.setoran.destroy', $session) }}" onsubmit="return confirm('Yakin ingin menghapus setoran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm btn-icon" aria-label="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-secondary">Belum ada setoran hafalan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($sessions->hasPages())
            <div class="card-footer">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
