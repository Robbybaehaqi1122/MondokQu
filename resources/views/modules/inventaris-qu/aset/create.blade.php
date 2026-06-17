<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Tambah Aset</h2>
                <div class="text-secondary mt-1">Input data aset / inventaris baru.</div>
            </div>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('inventaris.aset.store') }}">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Aset</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Merk</label>
                        <input type="text" name="merk" class="form-control @error('merk') is-invalid @enderror"
                               value="{{ old('merk') }}">
                        @error('merk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tahun Perolehan</label>
                        <input type="number" name="tahun_perolehan" class="form-control @error('tahun_perolehan') is-invalid @enderror"
                               value="{{ old('tahun_perolehan', date('Y')) }}" min="1900" max="2099">
                        @error('tahun_perolehan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label required">Kategori</label>
                        <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror" required>
                            <option value="">Pilih Kategori</option>
                            @foreach ($kategoris as $k)
                                <option value="{{ $k->id }}" {{ old('kategori_id') == $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
                            @endforeach
                        </select>
                        @error('kategori_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Lokasi</label>
                        <select name="lokasi_id" class="form-select @error('lokasi_id') is-invalid @enderror" required>
                            <option value="">Pilih Lokasi</option>
                            @foreach ($lokasis as $l)
                                <option value="{{ $l->id }}" {{ old('lokasi_id') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                            @endforeach
                        </select>
                        @error('lokasi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Kondisi</label>
                        <select name="kondisi" class="form-select @error('kondisi') is-invalid @enderror" required>
                            @foreach ($kondisiList as $key => $label)
                                <option value="{{ $key }}" {{ old('kondisi', 'baik') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('kondisi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Harga Perolehan (Rp)</label>
                        <input type="number" name="harga_perolehan" class="form-control @error('harga_perolehan') is-invalid @enderror"
                               value="{{ old('harga_perolehan', 0) }}" min="0">
                        @error('harga_perolehan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"
                                  rows="2">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('inventaris.aset.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
