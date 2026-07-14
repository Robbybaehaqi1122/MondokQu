<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KesehatanQu</div>
            <h2 class="page-title mt-1">Stok Obat UKS</h2>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#obatModal">Tambah Obat</button>
    </x-slot>

    <div class="row mb-3">
        <div class="col-lg-4">
            <form method="GET" action="{{ route('kesehatan.obat.index') }}">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Cari obat..." value="{{ $filters['q'] }}">
                    <button type="submit" class="btn btn-primary">Cari</button>
                    @if ($filters['q'])
                        <a href="{{ route('kesehatan.obat.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-mobile-md">
                <thead>
                    <tr>
                        <th>Nama Obat</th>
                        <th class="d-none d-md-table-cell">Jenis</th>
                        <th>Stok</th>
                        <th class="d-none d-md-table-cell">Satuan</th>
                        <th class="d-none d-md-table-cell">Expired</th>
                        <th class="d-none d-md-table-cell">Keterangan</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($obats as $obat)
                        <tr class="{{ $obat->stok <= 0 ? 'table-danger' : ($obat->expired_date?->isPast() ? 'table-warning' : '') }}">
                            <td class="fw-semibold">{{ $obat->nama_obat }}</td>
                            <td class="d-none d-md-table-cell">{{ $obat->jenis ?: '-' }}</td>
                            <td>
                                <span class="badge {{ $obat->stok <= 0 ? 'bg-danger' : ($obat->stok <= 10 ? 'bg-warning' : 'bg-success') }}">
                                    {{ $obat->stok }}
                                </span>
                            </td>
                            <td class="d-none d-md-table-cell">{{ $obat->satuan }}</td>
                            <td class="d-none d-md-table-cell">
                                @if ($obat->expired_date)
                                    <span class="{{ $obat->expired_date->isPast() ? 'text-danger fw-semibold' : '' }}">
                                        {{ $obat->expired_date->translatedFormat('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell">{{ $obat->keterangan ?: '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('kesehatan.obat.edit', $obat) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('kesehatan.obat.destroy', $obat) }}" class="d-inline" onsubmit="return confirm('Hapus obat {{ $obat->nama_obat }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-secondary">Belum ada obat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($obats->hasPages())
            <div class="card-footer">
                {{ $obats->links() }}
            </div>
        @endif
    </div>

    <div class="modal fade" id="obatModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('kesehatan.obat.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Obat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_obat" class="form-label">Nama Obat <span class="text-danger">*</span></label>
                        <input type="text" id="nama_obat" name="nama_obat" class="form-control @error('nama_obat') is-invalid @enderror" value="{{ old('nama_obat') }}" required>
                        @error('nama_obat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="jenis" class="form-label">Jenis</label>
                            <input type="text" id="jenis" name="jenis" class="form-control" value="{{ old('jenis') }}" placeholder="Misal: Tablet, Sirup">
                        </div>
                        <div class="col-6">
                            <label for="satuan" class="form-label">Satuan</label>
                            <input type="text" id="satuan" name="satuan" class="form-control" value="{{ old('satuan', 'pcs') }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="stok" class="form-label">Stok</label>
                            <input type="number" id="stok" name="stok" class="form-control" value="{{ old('stok', 0) }}" min="0">
                        </div>
                        <div class="col-6">
                            <label for="expired_date" class="form-label">Expired Date</label>
                            <input type="date" id="expired_date" name="expired_date" class="form-control" value="{{ old('expired_date') }}">
                        </div>
                    </div>
                    <div>
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea id="keterangan" name="keterangan" class="form-control" rows="2">{{ old('keterangan') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
