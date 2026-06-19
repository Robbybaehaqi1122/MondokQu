<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Dashboard KitabQu</h2>
            <div class="text-secondary mt-1">Pantau data kitab dan hafalan kitab secara ringkas.</div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Total Kitab</div>
                <div class="fs-2 fw-bold">{{ number_format($totalKitab) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Total Setoran</div>
                <div class="fs-2 fw-bold">{{ number_format($totalSetoran) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Santri Aktif</div>
                <div class="fs-2 fw-bold">{{ number_format($totalSantri) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Menunggu Review</div>
                <div class="fs-2 fw-bold {{ $pendingReview > 0 ? 'text-warning' : '' }}">{{ number_format($pendingReview) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Kitab per Kategori</h3></div>
                @if ($kitabPerKategori->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead><tr><th>Kategori</th><th>Jumlah Kitab</th></tr></thead>
                            <tbody>
                                @foreach ($kitabPerKategori as $item)
                                    <tr>
                                        <td>{{ $item['kategori'] }}</td>
                                        <td>{{ number_format($item['total']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="card-body"><div class="text-secondary">Belum ada data kitab.</div></div>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Setoran Terbaru</h3></div>
                @if ($recentSetorans->isNotEmpty())
                    <div class="list-group list-group-flush">
                        @foreach ($recentSetorans as $setoran)
                            <div class="list-group-item d-flex align-items-center gap-3">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $setoran->santri?->full_name ?? '-' }}</div>
                                    <div class="text-secondary small">{{ $setoran->kitab?->nama }} &middot; {{ $setoran->tanggal_setoran?->translatedFormat('d M Y') }}</div>
                                </div>
                                <span class="badge {{ $setoran->status === 'disetujui' ? 'bg-success' : ($setoran->status === 'ditolak' ? 'bg-danger' : 'bg-warning-lt') }}">
                                    {{ ucfirst($setoran->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="card-body"><div class="text-secondary">Belum ada setoran.</div></div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
