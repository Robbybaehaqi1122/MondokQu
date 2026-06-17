<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KegiatanQu</div>
            <h2 class="page-title mt-1">Tambah Pertemuan</h2>
        </div>
    </x-slot>

    <form action="{{ route('kegiatan.pertemuan.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-header"><h3 class="card-title">Informasi Pertemuan</h3></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label required">Kegiatan</label>
                    <select name="kegiatan_id" class="form-select @error('kegiatan_id') is-invalid @enderror" required>
                        <option value="">Pilih Kegiatan</option>
                        @foreach ($kegiatans as $k)
                            <option value="{{ $k->id }}" @selected(old('kegiatan_id') == $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                    @error('kegiatan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label required">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', now()->toDateString()) }}" required>
                    @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Jam Mulai</label>
                        <input type="time" name="jam_mulai" class="form-control @error('jam_mulai') is-invalid @enderror" value="{{ old('jam_mulai') }}">
                        @error('jam_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jam Selesai</label>
                        <input type="time" name="jam_selesai" class="form-control @error('jam_selesai') is-invalid @enderror" value="{{ old('jam_selesai') }}">
                        @error('jam_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Materi</label>
                    <input type="text" name="materi" class="form-control @error('materi') is-invalid @enderror" value="{{ old('materi') }}">
                    @error('materi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3">{{ old('catatan') }}</textarea>
                    @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('kegiatan.pertemuan.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>
    </form>
</x-app-layout>
