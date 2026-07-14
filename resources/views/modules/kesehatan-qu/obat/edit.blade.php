<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KesehatanQu</div>
            <h2 class="page-title mt-1">Edit Obat</h2>
        </div>
        <a href="{{ route('kesehatan.obat.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </x-slot>

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('kesehatan.obat.update', $obat) }}">
                @csrf
                @method('PATCH')

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data Obat</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="nama_obat" class="form-label">Nama Obat <span class="text-danger">*</span></label>
                            <input type="text" id="nama_obat" name="nama_obat" class="form-control @error('nama_obat') is-invalid @enderror" value="{{ old('nama_obat', $obat->nama_obat) }}" required>
                            @error('nama_obat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="jenis" class="form-label">Jenis</label>
                                <input type="text" id="jenis" name="jenis" class="form-control @error('jenis') is-invalid @enderror" value="{{ old('jenis', $obat->jenis) }}" placeholder="Misal: Tablet, Sirup">
                                @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="satuan" class="form-label">Satuan</label>
                                <input type="text" id="satuan" name="satuan" class="form-control @error('satuan') is-invalid @enderror" value="{{ old('satuan', $obat->satuan) }}">
                                @error('satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="stok" class="form-label">Stok</label>
                                <input type="number" id="stok" name="stok" class="form-control @error('stok') is-invalid @enderror" value="{{ old('stok', $obat->stok) }}" min="0">
                                @error('stok') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="expired_date" class="form-label">Expired Date</label>
                                <input type="date" id="expired_date" name="expired_date" class="form-control @error('expired_date') is-invalid @enderror" value="{{ old('expired_date', $obat->expired_date?->toDateString()) }}">
                                @error('expired_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea id="keterangan" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3">{{ old('keterangan', $obat->keterangan) }}</textarea>
                            @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Update Obat</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
