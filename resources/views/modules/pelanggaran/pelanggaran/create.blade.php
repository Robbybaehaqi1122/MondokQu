<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Catat Pelanggaran</h2>
            <div class="text-secondary mt-1">Catat pelanggaran santri.</div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('pelanggaran.store') }}">
        @csrf

        <div class="row row-cards mb-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data Pelanggaran</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="santri_id" class="form-label">Santri <span class="text-danger">*</span></label>
                            <select id="santri_id" name="santri_id" class="form-select @error('santri_id') is-invalid @enderror" required>
                                <option value="">Pilih Santri</option>
                                @foreach ($santriOptions as $santri)
                                    <option value="{{ $santri->id }}" @selected(old('santri_id') == $santri->id)>
                                        {{ $santri->full_name }} (NIS {{ $santri->nis }})
                                    </option>
                                @endforeach
                            </select>
                            @error('santri_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="kategori_id" class="form-label">Kategori Pelanggaran <span class="text-danger">*</span></label>
                            <select id="kategori_id" name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror" required>
                                <option value="">Pilih Kategori</option>
                                @foreach ($kategoriOptions as $kategori)
                                    <option value="{{ $kategori->id }}" @selected(old('kategori_id') == $kategori->id)>
                                        {{ $kategori->nama }} ({{ $kategori->poin }} poin)
                                    </option>
                                @endforeach
                            </select>
                            @error('kategori_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-hint">Poin akan otomatis terisi sesuai kategori yang dipilih.</div>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal" class="form-label">Tanggal Pelanggaran <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                                value="{{ old('tanggal', now()->toDateString()) }}" required>
                            @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea id="keterangan" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="4" placeholder="Deskripsi pelanggaran...">{{ old('keterangan') }}</textarea>
                            @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info d-flex align-items-center gap-2" role="alert">
                            <i class="ti ti-info-circle"></i>
                            <span>Poin pelanggaran otomatis mengikuti poin dari kategori yang dipilih. Setiap kategori memiliki bobot poin berbeda.</span>
                        </div>
                        <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
                            <i class="ti ti-alert-triangle"></i>
                            <span>Pastikan data santri dan kategori sudah benar sebelum menyimpan. Pelanggaran yang sudah dicatat dapat dihapus jika terjadi kesalahan.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i>
                Simpan Pelanggaran
            </button>
            <a href="{{ route('pelanggaran.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</x-app-layout>
