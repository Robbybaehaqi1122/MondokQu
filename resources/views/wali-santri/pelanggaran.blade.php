<x-app-layout>
    @php
        $poinBadgeClass = $totalPoin > 50 ? 'bg-danger-lt text-danger' : ($totalPoin > 20 ? 'bg-warning-lt text-warning' : 'bg-success-lt text-success');
    @endphp

    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Riwayat Pelanggaran</h2>
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
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-secondary small">Total Poin Pelanggaran</div>
                    <div class="fs-1 fw-bold mt-2 {{ $poinBadgeClass }}">{{ number_format($totalPoin) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-secondary small">Jumlah Pelanggaran</div>
                    <div class="fs-1 fw-bold mt-2">{{ number_format($records->total()) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <div>
                <h3 class="card-title">Daftar Pelanggaran</h3>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Poin</th>
                        <th>Keterangan</th>
                        <th>Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $pelanggaran)
                        <tr>
                            <td>{{ $pelanggaran->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td>{{ $pelanggaran->kategori?->nama ?? '-' }}</td>
                            <td>
                                <span class="badge bg-orange-lt text-orange">{{ $pelanggaran->poin }}</span>
                            </td>
                            <td class="text-secondary">{{ $pelanggaran->keterangan ?: '-' }}</td>
                            <td class="text-secondary">{{ $pelanggaran->pencatat?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-secondary">Tidak ada catatan pelanggaran untuk santri ini.</td>
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
