<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Detail Setoran Hafalan</h2>
                <div class="text-secondary mt-1">
                    Setoran oleh {{ $session->musyrif?->name ?? '-' }} pada {{ $session->session_date?->translatedFormat('d M Y') ?? '-' }}.
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('tahfidz.setoran.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Kembali
                </a>
                <form method="POST" action="{{ route('tahfidz.setoran.destroy', $session) }}" onsubmit="return confirm('Yakin ingin menghapus setoran ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="ti ti-trash me-1"></i>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Santri</h3>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="text-secondary small">Nama</div>
                        <div class="fw-semibold">{{ $session->santri?->full_name ?? '-' }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="text-secondary small">NIS</div>
                        <div>{{ $session->santri?->nis ?? '-' }}</div>
                    </div>
                    @if ($session->santri?->room)
                        <div class="mb-0">
                            <div class="text-secondary small">Kamar</div>
                            <div>{{ $session->santri->room->name ?? '-' }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Setoran</h3>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="text-secondary small">Tanggal</div>
                        <div>{{ $session->session_date?->translatedFormat('d M Y') ?? '-' }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="text-secondary small">Musyrif</div>
                        <div>{{ $session->musyrif?->name ?? '-' }}</div>
                    </div>
                    <div class="mb-0">
                        <div class="text-secondary small">Status</div>
                        <div>
                            <span class="badge {{ $session->status === 'completed' ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">
                                {{ $session->statusLabel() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan</h3>
                </div>
                <div class="card-body">
                    @php
                        $totalAyat = $session->records->sum(fn ($r) => ($r->verse_end - $r->verse_start) + 1);
                        $totalLancar = $session->records->filter(fn ($r) => $r->evaluation === 'lancar')->sum(fn ($r) => ($r->verse_end - $r->verse_start) + 1);
                    @endphp
                    <div class="mb-2">
                        <div class="text-secondary small">Total Ayat</div>
                        <div class="fs-2 fw-bold">{{ number_format($totalAyat) }}</div>
                    </div>
                    <div class="mb-0">
                        <div class="text-secondary small">Lancar</div>
                        <div class="fw-semibold text-success">{{ number_format($totalLancar) }} ayat</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Rincian Ayat</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Surah</th>
                        <th>Ayat</th>
                        <th>Jumlah Ayat</th>
                        <th>Penilaian</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($session->records as $record)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $record->surah?->name ?? '-' }}</td>
                            <td>{{ $record->verseRangeLabel() }}</td>
                            <td>{{ number_format(($record->verse_end - $record->verse_start) + 1) }}</td>
                            <td>
                                @php
                                    $evalBadge = match ($record->evaluation) {
                                        'lancar' => 'bg-success-lt text-success',
                                        'perlu_pengulangan' => 'bg-warning-lt text-warning',
                                        'belum_lancar' => 'bg-danger-lt text-danger',
                                        default => 'bg-secondary-lt text-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $evalBadge }}">{{ $record->evaluationLabel() }}</span>
                            </td>
                            <td>{{ $record->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($session->notes)
            <div class="card-footer">
                <div class="text-secondary small">Catatan Setoran</div>
                <div>{{ $session->notes }}</div>
            </div>
        @endif
    </div>
</x-app-layout>
