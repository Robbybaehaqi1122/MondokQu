<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title mt-1">Tambah Jadwal</h2>
            <div class="text-secondary small">Tambahkan jadwal ngaji atau kegiatan baru.</div>
        </div>
    </x-slot>

    <form action="{{ route('kepengurusan.jadwal.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-header"><h3 class="card-title">Informasi Jadwal</h3></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Kegiatan</label>
                        <input type="text" name="kegiatan" class="form-control @error('kegiatan') is-invalid @enderror" value="{{ old('kegiatan') }}" placeholder="Contoh: Ngaji Kitab Safinah" required>
                        @error('kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pengajar</label>
                        <select name="pengajar_id" class="form-select @error('pengajar_id') is-invalid @enderror">
                            <option value="">Pilih Pengajar</option>
                            @foreach ($pengajars as $p)
                                <option value="{{ $p->id }}" @selected(old('pengajar_id') == $p->id)>{{ $p->nama }}</option>
                            @endforeach
                        </select>
                        @error('pengajar_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Hari</label>
                        <select name="hari" class="form-select @error('hari') is-invalid @enderror" required>
                            <option value="">Pilih Hari</option>
                            @foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $h)
                                <option value="{{ $h }}" @selected(old('hari') === $h)>{{ $h }}</option>
                            @endforeach
                        </select>
                        @error('hari')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Jam Mulai</label>
                        <input type="time" name="jam_mulai" class="form-control @error('jam_mulai') is-invalid @enderror" value="{{ old('jam_mulai') }}" required>
                        @error('jam_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jam Selesai</label>
                        <input type="time" name="jam_selesai" class="form-control @error('jam_selesai') is-invalid @enderror" value="{{ old('jam_selesai') }}">
                        @error('jam_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tempat</label>
                        <input type="text" name="tempat" class="form-control @error('tempat') is-invalid @enderror" value="{{ old('tempat') }}" placeholder="Contoh: Masjid, Aula, Kelas A">
                        @error('tempat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3">{{ old('keterangan') }}</textarea>
                        @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="{{ route('kepengurusan.jadwal.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
            </div>
        </div>
    </form>
</x-app-layout>
