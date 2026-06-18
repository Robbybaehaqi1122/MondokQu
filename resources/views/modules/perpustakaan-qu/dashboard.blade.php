<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">PerpustakaanQu</div>
            <h2 class="page-title mt-1">Dashboard Perpustakaan</h2>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-bold">Total Kitab</div>
                    <div class="fs-2 fw-bold">{{ number_format($totalKitab ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-bold">Total Eksemplar</div>
                    <div class="fs-2 fw-bold">{{ number_format($totalEksemplar ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-bold">Kategori</div>
                    <div class="fs-2 fw-bold">{{ number_format($totalKategori ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-bold">Sedang Dipinjam</div>
                    <div class="fs-2 fw-bold text-warning">{{ number_format($dipinjam ?? 0) }}</div>
                </div>
            </div>
        </div>

        @if (!empty($statsPerKategori))
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Kitab per Kategori</h3></div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead><tr><th>Kategori</th><th>Jumlah Kitab</th></tr></thead>
                            <tbody>
                                @foreach ($statsPerKategori as $k)
                                    <tr>
                                        <td>{{ $k['nama'] }}</td>
                                        <td>{{ number_format($k['kitabs_count']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if ($recentPeminjamans->isNotEmpty())
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Peminjaman Terbaru</h3>
                        <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-outline-secondary btn-sm">Lihat Semua</a>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach ($recentPeminjamans as $p)
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $p->kitab?->judul }}</div>
                                        <div class="text-secondary small">{{ $p->santri?->full_name }} &middot; {{ $p->tanggal_pinjam->translatedFormat('d M Y') }}</div>
                                    </div>
                                    <span class="badge ms-2 {{ $p->status === 'dipinjam' ? 'bg-warning' : ($p->status === 'terlambat' ? 'bg-danger' : 'bg-success') }}">{{ $p->status }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
