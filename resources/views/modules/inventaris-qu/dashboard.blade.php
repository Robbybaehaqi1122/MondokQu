<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Dashboard InventarisQu</h2>
                <div class="text-secondary mt-1">Ringkasan inventaris dan aset pondok.</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('inventaris.aset.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Aset Baru
                </a>
            </div>
        </div>
    </x-slot>

    @php $s = $summary @endphp
    <div class="row row-cards">
        <div class="col-md-6 col-xl-2">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Total Aset</div>
                    <div class="h1 mb-1">{{ number_format($s['totalAset'] ?? 0) }}</div>
                    <div class="text-secondary">Semua aset terdaftar</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Nilai Aset</div>
                    <div class="h1 mb-1">Rp {{ number_format($s['totalNilai'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-secondary">Total nilai perolehan</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Kondisi Baik</div>
                    <div class="h1 mb-1 text-success">{{ number_format($s['asetBaik'] ?? 0) }}</div>
                    <div class="text-secondary">&nbsp;</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Rusak</div>
                    <div class="h1 mb-1 text-danger">{{ number_format($s['asetRusak'] ?? 0) }}</div>
                    <div class="text-secondary">&nbsp;</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Hilang</div>
                    <div class="h1 mb-1 text-warning">{{ number_format($s['asetHilang'] ?? 0) }}</div>
                    <div class="text-secondary">&nbsp;</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Dipinjam</div>
                    <div class="h1 mb-1 text-info">{{ number_format($s['dipinjam'] ?? 0) }}</div>
                    <div class="text-secondary">&nbsp;</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mt-3">
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Aset Terbaru</h3>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($asetTerbaru as $aset)
                        <a href="{{ route('inventaris.aset.show', $aset) }}" class="list-group-item list-group-item-action d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $aset->name }}</div>
                                <div class="text-secondary small">{{ $aset->kode_aset }} &mdash; {{ $aset->kategori->name ?? '-' }}</div>
                            </div>
                            <span class="badge bg-{{ $aset->kondisi === 'baik' ? 'success' : ($aset->kondisi === 'hilang' ? 'warning' : 'danger') }} ms-2">
                                {{ \App\Modules\InventarisQu\Models\Aset::KONDISI[$aset->kondisi] ?? $aset->kondisi }}
                            </span>
                        </a>
                    @empty
                        <div class="list-group-item text-secondary text-center py-4">Belum ada aset terdaftar.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Peminjaman Aktif</h3>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($peminjamanAktif as $p)
                        <div class="list-group-item d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $p->aset->name ?? '-' }}</div>
                                <div class="text-secondary small">{{ $p->peminjam }} &mdash; {{ $p->tanggal_pinjam->format('d M Y') }}</div>
                            </div>
                            <span class="badge bg-warning ms-2">Dipinjam</span>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary text-center py-4">Tidak ada peminjaman aktif.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
