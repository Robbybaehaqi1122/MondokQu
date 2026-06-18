<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">PerpustakaanQu</div>
            <h2 class="page-title mt-1">Tambah Peminjaman</h2>
        </div>
    </x-slot>

    <form action="{{ route('perpustakaan.peminjaman.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-header"><h3 class="card-title">Form Peminjaman</h3></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label required">Kitab</label>
                    <select name="kitab_id" class="form-select @error('kitab_id') is-invalid @enderror" required>
                        <option value="">Pilih Kitab</option>
                        @foreach ($kitabs as $k)
                            <option value="{{ $k->id }}" @selected(old('kitab_id') == $k->id) data-stok="{{ $k->tersedia }}">
                                {{ $k->judul }} ({{ $k->kategori?->nama }} - Tersedia: {{ $k->tersedia }})
                            </option>
                        @endforeach
                    </select>
                    @error('kitab_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label required">Santri</label>
                    <select name="santri_id" class="form-select @error('santri_id') is-invalid @enderror" required>
                        <option value="">Pilih Santri</option>
                        @foreach ($santris as $s)
                            <option value="{{ $s->id }}" @selected(old('santri_id') == $s->id)>{{ $s->full_name }} (NIS: {{ $s->nis }})</option>
                        @endforeach
                    </select>
                    @error('santri_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label required">Tanggal Pinjam</label>
                        <input type="date" name="tanggal_pinjam" class="form-control @error('tanggal_pinjam') is-invalid @enderror" value="{{ old('tanggal_pinjam', now()->toDateString()) }}" required>
                        @error('tanggal_pinjam')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Tanggal Jatuh Tempo</label>
                        <input type="date" name="tanggal_jatuh_tempo" class="form-control @error('tanggal_jatuh_tempo') is-invalid @enderror" value="{{ old('tanggal_jatuh_tempo', now()->addDays(7)->toDateString()) }}" required>
                        @error('tanggal_jatuh_tempo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="2">{{ old('catatan') }}</textarea>
                    @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>
    </form>
</x-app-layout>
