<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">{{ $aset->name }}</h2>
                <div class="text-secondary mt-1">{{ $aset->kode_aset }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('inventaris.aset.edit', $aset) }}" class="btn btn-primary">
                    <i class="ti ti-pencil me-1"></i> Edit
                </a>
                <a href="{{ route('inventaris.aset.qr', $aset) }}" class="btn btn-outline-primary">
                    <i class="ti ti-qrcode me-1"></i> Generate QR
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Detail Aset</h3></div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Kode Aset</dt>
                        <dd class="col-sm-8">{{ $aset->kode_aset }}</dd>
                        <dt class="col-sm-4">Kategori</dt>
                        <dd class="col-sm-8">{{ $aset->kategori->name ?? '-' }}</dd>
                        <dt class="col-sm-4">Lokasi</dt>
                        <dd class="col-sm-8">{{ $aset->lokasi->name ?? '-' }}
                            @if ($aset->lokasi->building) ({{ $aset->lokasi->building }}) @endif</dd>
                        <dt class="col-sm-4">Merk</dt>
                        <dd class="col-sm-8">{{ $aset->merk ?? '-' }}</dd>
                        <dt class="col-sm-4">Tahun Perolehan</dt>
                        <dd class="col-sm-8">{{ $aset->tahun_perolehan ?? '-' }}</dd>
                        <dt class="col-sm-4">Harga Perolehan</dt>
                        <dd class="col-sm-8">Rp {{ number_format($aset->harga_perolehan, 0, ',', '.') }}</dd>
                        <dt class="col-sm-4">Kondisi</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $aset->kondisi === 'baik' ? 'success' : ($aset->kondisi === 'hilang' ? 'warning' : 'danger') }}">
                                {{ \App\Modules\InventarisQu\Models\Aset::KONDISI[$aset->kondisi] ?? $aset->kondisi }}
                            </span>
                        </dd>
                        <dt class="col-sm-4">Deskripsi</dt>
                        <dd class="col-sm-8">{{ $aset->deskripsi ?? '-' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><h3 class="card-title">Riwayat Peminjaman</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Peminjam</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($aset->peminjaman as $p)
                                <tr>
                                    <td>{{ $p->peminjam }}</td>
                                    <td>{{ $p->tanggal_pinjam->format('d M Y') }}</td>
                                    <td>{{ $p->tanggal_kembali?->format('d M Y') ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $p->status === 'dipinjam' ? 'warning' : 'success' }}">
                                            {{ $p->status === 'dipinjam' ? 'Dipinjam' : 'Dikembalikan' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary py-3">Belum ada riwayat peminjaman.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @if ($aset->qr_code)
                <div class="card">
                    <div class="card-header"><h3 class="card-title">QR Code</h3></div>
                    <div class="card-body text-center">
                        <img src="data:image/svg+xml;base64,{{ $aset->qr_code }}" alt="QR Code" class="img-fluid" style="max-width: 200px;">
                        <div class="mt-2">
                            <a href="{{ route('inventaris.aset.qr', $aset) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-refresh me-1"></i> Generate Ulang
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
