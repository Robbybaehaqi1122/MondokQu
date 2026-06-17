<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KegiatanQu</div>
            <h2 class="page-title mt-1">Tambah Kegiatan</h2>
        </div>
    </x-slot>

    <form action="{{ route('kegiatan.kegiatan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Informasi Kegiatan</h3></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Nama Kegiatan</label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="4">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Pembina</label>
                                <select name="pembina_id" class="form-select @error('pembina_id') is-invalid @enderror">
                                    <option value="">Pilih Pembina</option>
                                    @foreach ($pembinas as $p)
                                        <option value="{{ $p->id }}" @selected(old('pembina_id') == $p->id)>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                @error('pembina_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tempat</label>
                                <input type="text" name="tempat" class="form-control @error('tempat') is-invalid @enderror" value="{{ old('tempat') }}">
                                @error('tempat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Kuota Peserta</label>
                                <input type="number" name="kuota" class="form-control @error('kuota') is-invalid @enderror" value="{{ old('kuota') }}" min="0" placeholder="Kosongkan jika tak terbatas">
                                @error('kuota')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>Aktif</option>
                                    <option value="nonaktif" @selected(old('status') === 'nonaktif')>Nonaktif</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cover</label>
                            <input type="file" name="cover" class="form-control @error('cover') is-invalid @enderror" accept="image/*">
                            @error('cover')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Jadwal Kegiatan</h3></div>
                    <div class="card-body">
                        <div id="jadwal-list">
                            <div class="jadwal-item border rounded p-3 mb-2">
                                <div class="mb-2">
                                    <label class="form-label required">Hari</label>
                                    <select name="jadwal[0][hari]" class="form-select">
                                        <option value="">Pilih Hari</option>
                                        <option value="senin">Senin</option>
                                        <option value="selasa">Selasa</option>
                                        <option value="rabu">Rabu</option>
                                        <option value="kamis">Kamis</option>
                                        <option value="jumat">Jumat</option>
                                        <option value="sabtu">Sabtu</option>
                                        <option value="minggu">Minggu</option>
                                    </select>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label required">Jam Mulai</label>
                                        <input type="time" name="jadwal[0][jam_mulai]" class="form-control">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Jam Selesai</label>
                                        <input type="time" name="jadwal[0][jam_selesai]" class="form-control">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-jadwal" style="display:none;">Hapus</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary w-100" id="add-jadwal">
                            <i class="ti ti-plus"></i> Tambah Jadwal
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('kegiatan.kegiatan.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</x-app-layout>

@push('scripts')
<script>
let jadwalIndex = 1;
document.getElementById('add-jadwal').addEventListener('click', function() {
    const template = document.querySelector('.jadwal-item').cloneNode(true);
    template.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/jadwal\[\d+\]/, `jadwal[${jadwalIndex}]`);
        el.value = '';
    });
    template.querySelector('.remove-jadwal').style.display = '';
    template.querySelector('.remove-jadwal').addEventListener('click', function() {
        template.remove();
    });
    document.getElementById('jadwal-list').appendChild(template);
    jadwalIndex++;
});
</script>
@endpush
