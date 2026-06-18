<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">PerpustakaanQu</div>
                <h2 class="page-title mt-1">{{ $kitab->judul }}</h2>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('perpustakaan.kitab.edit', $kitab) }}" class="btn btn-outline-primary">
                    <i class="ti ti-edit"></i> Edit
                </a>
                <a href="{{ route('perpustakaan.kitab.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Detail Kitab</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Kategori</dt>
                        <dd class="col-sm-8">{{ $kitab->kategori?->nama }}</dd>
                        <dt class="col-sm-4">Pengarang</dt>
                        <dd class="col-sm-8">{{ $kitab->pengarang ?: '-' }}</dd>
                        <dt class="col-sm-4">Penerbit</dt>
                        <dd class="col-sm-8">{{ $kitab->penerbit ?: '-' }}</dd>
                        <dt class="col-sm-4">Tahun Terbit</dt>
                        <dd class="col-sm-8">{{ $kitab->tahun_terbit ?: '-' }}</dd>
                        <dt class="col-sm-4">ISBN</dt>
                        <dd class="col-sm-8">{{ $kitab->isbn ?: '-' }}</dd>
                        <dt class="col-sm-4">Lokasi Rak</dt>
                        <dd class="col-sm-8">{{ $kitab->lokasi_rak ?: '-' }}</dd>
                        <dt class="col-sm-4">Kondisi</dt>
                        <dd class="col-sm-8">
                            <span class="badge {{ $kitab->kondisi === 'baik' ? 'bg-success' : ($kitab->kondisi === 'rusak_berat' || $kitab->kondisi === 'hilang' ? 'bg-danger' : 'bg-warning') }}">
                                {{ $kitab->kondisi }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
            @if ($kitab->deskripsi)
                <div class="card mt-3">
                    <div class="card-header"><h3 class="card-title">Deskripsi</h3></div>
                    <div class="card-body">{{ $kitab->deskripsi }}</div>
                </div>
            @endif
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Stok</h3></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-secondary small text-uppercase fw-bold">Total Eksemplar</div>
                            <div class="fs-2 fw-bold">{{ $kitab->jumlah_eksemplar }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-secondary small text-uppercase fw-bold">Tersedia</div>
                            <div class="fs-2 fw-bold {{ $kitab->tersedia > 0 ? 'text-success' : 'text-danger' }}">{{ $kitab->tersedia }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($kitab->peminjamans->isNotEmpty())
                <div class="card mt-3">
                    <div class="card-header"><h3 class="card-title">Riwayat Peminjaman</h3></div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead><tr><th>Santri</th><th>Pinjam</th><th>Kembali</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach ($kitab->peminjamans as $p)
                                    <tr>
                                        <td>{{ $p->santri?->full_name }}</td>
                                        <td>{{ $p->tanggal_pinjam->translatedFormat('d M Y') }}</td>
                                        <td>{{ $p->tanggal_kembali?->translatedFormat('d M Y') ?: '-' }}</td>
                                        <td>
                                            <span class="badge {{ $p->status === 'dipinjam' ? 'bg-warning' : ($p->status === 'terlambat' ? 'bg-danger' : 'bg-success') }}">
                                                {{ $p->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
