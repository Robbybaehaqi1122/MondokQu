<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KesehatanQu</div>
            <h2 class="page-title mt-1">Catat Pemeriksaan UKS</h2>
        </div>
        <a href="{{ route('kesehatan.pemeriksaan.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </x-slot>

    <form method="POST" action="{{ route('kesehatan.pemeriksaan.store') }}">
        @csrf

        <div class="row row-cards mb-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data Pemeriksaan</h3>
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

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="tanggal_pemeriksaan" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" id="tanggal_pemeriksaan" name="tanggal_pemeriksaan" class="form-control @error('tanggal_pemeriksaan') is-invalid @enderror" value="{{ old('tanggal_pemeriksaan', now()->toDateString()) }}" required>
                                @error('tanggal_pemeriksaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="keluhan" class="form-label">Keluhan <span class="text-danger">*</span></label>
                            <input type="text" id="keluhan" name="keluhan" class="form-control @error('keluhan') is-invalid @enderror" value="{{ old('keluhan') }}" placeholder="Misal: Demam, batuk, pusing" required>
                            @error('keluhan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="diagnosis" class="form-label">Diagnosis</label>
                            <input type="text" id="diagnosis" name="diagnosis" class="form-control @error('diagnosis') is-invalid @enderror" value="{{ old('diagnosis') }}" placeholder="Diagnosis sementara dari petugas UKS">
                            @error('diagnosis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tindakan" class="form-label">Tindakan</label>
                            <textarea id="tindakan" name="tindakan" class="form-control @error('tindakan') is-invalid @enderror" rows="2" placeholder="Tindakan yang dilakukan...">{{ old('tindakan') }}</textarea>
                            @error('tindakan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea id="catatan" name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="2" placeholder="Catatan tambahan...">{{ old('catatan') }}</textarea>
                            @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Obat yang Diberikan</h3>
                    </div>
                    <div class="card-body" id="obat-container">
                        <div class="text-secondary small mb-3">Pilih obat yang diberikan pada pemeriksaan ini (opsional).</div>
                        <div class="obat-row row g-2 mb-2">
                            <div class="col-md-5">
                                <select name="obat_ids[]" class="form-select">
                                    <option value="">Pilih Obat</option>
                                    @foreach ($obatOptions as $obat)
                                        <option value="{{ $obat->id }}">{{ $obat->nama_obat }} (stok: {{ $obat->stok }} {{ $obat->satuan }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="obat_jumlahs[]" class="form-control" placeholder="Jumlah" min="1" value="1">
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="obat_catatans[]" class="form-control" placeholder="Catatan (opsional)">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger btn-icon" onclick="this.closest('.obat-row').remove()" title="Hapus">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="tambah-obat">Tambah Obat</button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Rujukan (Opsional)</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" id="rujuk" name="rujuk" class="form-check-input" value="1" @checked(old('rujuk')) onchange="document.getElementById('rujukan-fields').classList.toggle('d-none', !this.checked)">
                                <label for="rujuk" class="form-check-label">Dirujuk ke RS / Klinik</label>
                            </div>
                        </div>

                        <div id="rujukan-fields" class="{{ old('rujuk') ? '' : 'd-none' }}">
                            <div class="mb-3">
                                <label for="tempat_rujukan" class="form-label">Tempat Rujukan</label>
                                <input type="text" id="tempat_rujukan" name="tempat_rujukan" class="form-control @error('tempat_rujukan') is-invalid @enderror" value="{{ old('tempat_rujukan') }}" placeholder="Nama RS/Klinik">
                                @error('tempat_rujukan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="diagnosis_dokter" class="form-label">Diagnosis Dokter</label>
                                <input type="text" id="diagnosis_dokter" name="diagnosis_dokter" class="form-control @error('diagnosis_dokter') is-invalid @enderror" value="{{ old('diagnosis_dokter') }}">
                                @error('diagnosis_dokter') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label for="tanggal_rujuk" class="form-label">Tgl. Rujuk</label>
                                    <input type="date" id="tanggal_rujuk" name="tanggal_rujuk" class="form-control @error('tanggal_rujuk') is-invalid @enderror" value="{{ old('tanggal_rujuk') }}">
                                    @error('tanggal_rujuk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-6">
                                    <label for="tanggal_kembali" class="form-label">Tgl. Kembali</label>
                                    <input type="date" id="tanggal_kembali" name="tanggal_kembali" class="form-control @error('tanggal_kembali') is-invalid @enderror" value="{{ old('tanggal_kembali') }}">
                                    @error('tanggal_kembali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div>
                                <label for="catatan_rujukan" class="form-label">Catatan</label>
                                <textarea id="catatan_rujukan" name="catatan_rujukan" class="form-control @error('catatan_rujukan') is-invalid @enderror" rows="2" placeholder="Catatan rujukan...">{{ old('catatan_rujukan') }}</textarea>
                                @error('catatan_rujukan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Simpan Pemeriksaan</button>
        </div>
    </form>
</x-app-layout>

@push('scripts')
<script>
    document.getElementById('tambah-obat')?.addEventListener('click', function () {
        const container = document.getElementById('obat-container');
        const firstRow = container.querySelector('.obat-row');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelectorAll('input, select').forEach(el => el.value = '');
        container.insertBefore(newRow, this);
    });
</script>
@endpush
