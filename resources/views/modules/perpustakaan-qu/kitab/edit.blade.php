<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">PerpustakaanQu</div>
            <h2 class="page-title mt-1">Edit Kitab</h2>
        </div>
    </x-slot>

    <form action="{{ route('perpustakaan.kitab.update', $kitab) }}" method="POST">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-header"><h3 class="card-title">Informasi Kitab</h3></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label required">Kategori</label>
                    <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror" required>
                        <option value="">Pilih Kategori</option>
                        @foreach ($kategoris as $k)
                            <option value="{{ $k->id }}" @selected(old('kategori_id', $kitab->kategori_id) == $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                    @error('kategori_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label required">Judul Kitab</label>
                    <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $kitab->judul) }}" required>
                    @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Pengarang</label>
                        <input type="text" name="pengarang" class="form-control @error('pengarang') is-invalid @enderror" value="{{ old('pengarang', $kitab->pengarang) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Penerbit</label>
                        <input type="text" name="penerbit" class="form-control @error('penerbit') is-invalid @enderror" value="{{ old('penerbit', $kitab->penerbit) }}">
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" class="form-control @error('tahun_terbit') is-invalid @enderror" value="{{ old('tahun_terbit', $kitab->tahun_terbit) }}" min="1000" max="{{ now()->year + 1 }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ISBN</label>
                        <input type="text" name="isbn" class="form-control @error('isbn') is-invalid @enderror" value="{{ old('isbn', $kitab->isbn) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Lokasi Rak</label>
                        <input type="text" name="lokasi_rak" class="form-control @error('lokasi_rak') is-invalid @enderror" value="{{ old('lokasi_rak', $kitab->lokasi_rak) }}">
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label required">Jumlah Eksemplar</label>
                        <input type="number" name="jumlah_eksemplar" class="form-control @error('jumlah_eksemplar') is-invalid @enderror" value="{{ old('jumlah_eksemplar', $kitab->jumlah_eksemplar) }}" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Kondisi</label>
                        <select name="kondisi" class="form-select @error('kondisi') is-invalid @enderror" required>
                            <option value="baik" @selected(old('kondisi', $kitab->kondisi) === 'baik')>Baik</option>
                            <option value="rusak_ringan" @selected(old('kondisi', $kitab->kondisi) === 'rusak_ringan')>Rusak Ringan</option>
                            <option value="rusak_berat" @selected(old('kondisi', $kitab->kondisi) === 'rusak_berat')>Rusak Berat</option>
                            <option value="hilang" @selected(old('kondisi', $kitab->kondisi) === 'hilang')>Hilang</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3">{{ old('deskripsi', $kitab->deskripsi) }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('perpustakaan.kitab.show', $kitab) }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>
    </form>
</x-app-layout>
