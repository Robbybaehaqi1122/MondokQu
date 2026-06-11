<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KesehatanQu</div>
            <h2 class="page-title mt-1">Rekam Medis: {{ $santri->full_name }}</h2>
        </div>
        <a href="{{ route('kesehatan.rekam-medis.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </x-slot>

    <form method="POST" action="{{ $rekamMedis ? route('kesehatan.rekam-medis.update', $santri) : route('kesehatan.rekam-medis.store') }}">
        @csrf
        @if ($rekamMedis) @method('PATCH') @endif
        <input type="hidden" name="santri_id" value="{{ $santri->id }}">

        <div class="row row-cards mb-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data Medis</h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="golongan_darah" class="form-label">Golongan Darah</label>
                                <select id="golongan_darah" name="golongan_darah" class="form-select @error('golongan_darah') is-invalid @enderror">
                                    <option value="">Pilih</option>
                                    @foreach (['A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $gd)
                                        <option value="{{ $gd }}" @selected(old('golongan_darah', $rekamMedis?->golongan_darah) == $gd)>{{ $gd }}</option>
                                    @endforeach
                                </select>
                                @error('golongan_darah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="tinggi_badan" class="form-label">Tinggi Badan (cm)</label>
                                <input type="number" step="0.1" id="tinggi_badan" name="tinggi_badan" class="form-control @error('tinggi_badan') is-invalid @enderror" value="{{ old('tinggi_badan', $rekamMedis?->tinggi_badan) }}">
                                @error('tinggi_badan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="berat_badan" class="form-label">Berat Badan (kg)</label>
                                <input type="number" step="0.1" id="berat_badan" name="berat_badan" class="form-control @error('berat_badan') is-invalid @enderror" value="{{ old('berat_badan', $rekamMedis?->berat_badan) }}">
                                @error('berat_badan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="riwayat_penyakit" class="form-label">Riwayat Penyakit</label>
                            <textarea id="riwayat_penyakit" name="riwayat_penyakit" class="form-control @error('riwayat_penyakit') is-invalid @enderror" rows="3" placeholder="Riwayat penyakit yang pernah diderita...">{{ old('riwayat_penyakit', $rekamMedis?->riwayat_penyakit) }}</textarea>
                            @error('riwayat_penyakit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="alergi_obat" class="form-label">Alergi Obat</label>
                            <input type="text" id="alergi_obat" name="alergi_obat" class="form-control @error('alergi_obat') is-invalid @enderror" value="{{ old('alergi_obat', $rekamMedis?->alergi_obat) }}" placeholder="Misal: Paracetamol, Penicillin">
                            @error('alergi_obat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="alergi_makanan" class="form-label">Alergi Makanan</label>
                            <input type="text" id="alergi_makanan" name="alergi_makanan" class="form-control @error('alergi_makanan') is-invalid @enderror" value="{{ old('alergi_makanan', $rekamMedis?->alergi_makanan) }}" placeholder="Misal: Kacang, Seafood">
                            @error('alergi_makanan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label for="catatan" class="form-label">Catatan Tambahan</label>
                            <textarea id="catatan" name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="2" placeholder="Informasi medis lain...">{{ old('catatan', $rekamMedis?->catatan) }}</textarea>
                            @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Simpan Rekam Medis</button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Info Santri</h3>
                    </div>
                    <div class="card-body">
                        <dl class="mb-0">
                            <dt>Nama</dt>
                            <dd class="mb-2">{{ $santri->full_name }}</dd>
                            <dt>NIS</dt>
                            <dd class="mb-2">{{ $santri->nis }}</dd>
                            <dt>Jenis Kelamin</dt>
                            <dd class="mb-2">{{ $santri->genderLabel() }}</dd>
                            <dt>Kamar</dt>
                            <dd class="mb-0">{{ $santri->displayRoomName() ?: '-' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
