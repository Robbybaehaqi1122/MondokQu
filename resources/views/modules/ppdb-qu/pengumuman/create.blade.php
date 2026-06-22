<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">PpdbQu</div>
            <h2 class="page-title mt-1">Buat Pengumuman</h2>
        </div>
    </x-slot>

    <form action="{{ route('ppdb.pengumuman.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-header"><h3 class="card-title">Informasi Pengumuman</h3></div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label required">Gelombang</label>
                        <select name="gelombang_id" class="form-select @error('gelombang_id') is-invalid @enderror" required>
                            <option value="">Pilih Gelombang</option>
                            @foreach ($gelombangs as $g)
                                <option value="{{ $g->id }}" @selected(old('gelombang_id') == $g->id)>{{ $g->nama }}</option>
                            @endforeach
                        </select>
                        @error('gelombang_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Judul Pengumuman</label>
                        <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul') }}" placeholder="Misal: Pengumuman Hasil Seleksi Gelombang 1" required>
                        @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="4">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Tanggal Pengumuman</label>
                        <input type="date" name="tanggal_pengumuman" class="form-control @error('tanggal_pengumuman') is-invalid @enderror" value="{{ old('tanggal_pengumuman', now()->toDateString()) }}" required>
                        @error('tanggal_pengumuman')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('ppdb.pengumuman.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>
    </form>
</x-app-layout>
