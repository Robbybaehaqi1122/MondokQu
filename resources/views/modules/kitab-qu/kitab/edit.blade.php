<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title mt-1">Edit Kitab</h2>
            <div class="text-secondary small">Perbarui informasi kitab.</div>
        </div>
    </x-slot>

    <form action="{{ route('kitab.kitab.update', $kitab) }}" method="POST">
        @csrf @method('PATCH')
        <div class="card">
            <div class="card-header"><h3 class="card-title">Informasi Kitab</h3></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Kitab</label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $kitab->nama) }}" required>
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pengarang</label>
                        <input type="text" name="pengarang" class="form-control @error('pengarang') is-invalid @enderror" value="{{ old('pengarang', $kitab->pengarang) }}">
                        @error('pengarang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kategori</label>
                        <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror">
                            <option value="">Pilih Kategori</option>
                            @foreach ($kategoris as $k)
                                <option value="{{ $k->id }}" @selected(old('kategori_id', $kitab->kategori_id) == $k->id)>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                        @error('kategori_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="4">{{ old('keterangan', $kitab->keterangan) }}</textarea>
                        @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="{{ route('kitab.kitab.show', $kitab) }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </div>
    </form>
</x-app-layout>
