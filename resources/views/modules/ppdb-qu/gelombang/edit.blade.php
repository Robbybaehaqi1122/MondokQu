<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">PpdbQu</div>
            <h2 class="page-title mt-1">Edit Gelombang</h2>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-8">
            <form action="{{ route('ppdb.gelombang.update', $gelombang) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card">
            <div class="card-header"><h3 class="card-title">Informasi Gelombang</h3></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label required">Nama Gelombang</label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $gelombang->nama) }}" required>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label required">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai', $gelombang->tanggal_mulai->toDateString()) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai', $gelombang->tanggal_selesai->toDateString()) }}" required>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Kuota</label>
                        <input type="number" name="kuota" class="form-control @error('kuota') is-invalid @enderror" value="{{ old('kuota', $gelombang->kuota) }}" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Biaya Pendaftaran (Rp)</label>
                        <input type="number" name="biaya_pendaftaran" class="form-control @error('biaya_pendaftaran') is-invalid @enderror" value="{{ old('biaya_pendaftaran', $gelombang->biaya_pendaftaran) }}" min="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="aktif" @selected(old('status', $gelombang->status) === 'aktif')>Aktif</option>
                        <option value="selesai" @selected(old('status', $gelombang->status) === 'selesai')>Selesai</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('ppdb.gelombang.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>
            </form>
        </div>
    </div>
</x-app-layout>
