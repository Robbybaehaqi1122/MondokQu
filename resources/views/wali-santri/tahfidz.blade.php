<x-app-layout>
    @php
        $evaluationBadgeClasses = [
            'lancar' => 'bg-success-lt text-success',
            'perlu_pengulangan' => 'bg-warning-lt text-warning',
            'belum_lancar' => 'bg-danger-lt text-danger',
        ];
    @endphp

    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Riwayat Tahfidz</h2>
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

    <div class="row row-cards">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-secondary small">Total Sesi Setoran</div>
                    <div class="fs-1 fw-bold mt-2">{{ number_format($totalSesi) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-secondary small">Total Ayat</div>
                    <div class="fs-1 fw-bold mt-2">{{ number_format($totalAyat) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <div>
                <h3 class="card-title">Riwayat Setoran Tahfidz</h3>
            </div>
        </div>
        <div class="card-body">
            @forelse ($sessions as $session)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="fw-semibold">{{ $session->session_date?->translatedFormat('d M Y') ?? '-' }}</div>
                            <div class="text-secondary small mt-1">
                                Musyrif: {{ $session->musyrif?->name ?? '-' }}
                            </div>
                        </div>
                        <span class="badge bg-success-lt text-success">Selesai</span>
                    </div>

                    @if ($session->records->isNotEmpty())
                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Surah</th>
                                        <th>Ayat</th>
                                        <th>Evaluasi</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($session->records as $record)
                                        <tr>
                                            <td>{{ $record->surah?->name ?? 'Surah #'.$record->surah_id }}</td>
                                            <td>{{ $record->verseRangeLabel() }}</td>
                                            <td>
                                                <span class="badge {{ $evaluationBadgeClasses[$record->evaluation] ?? 'bg-secondary-lt text-secondary' }}">
                                                    {{ $record->evaluationLabel() }}
                                                </span>
                                            </td>
                                            <td class="text-secondary">{{ $record->notes ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-secondary mt-2">Tidak ada catatan setoran.</div>
                    @endif

                    @if ($session->notes)
                        <div class="text-secondary small mt-2">
                            <span class="fw-semibold">Catatan sesi:</span> {{ $session->notes }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-secondary">Belum ada riwayat setoran tahfidz untuk santri ini.</div>
            @endforelse
        </div>

        @if ($sessions->hasPages())
            <div class="card-footer">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
