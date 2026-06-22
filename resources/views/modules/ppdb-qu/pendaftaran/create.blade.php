<x-guest-layout>
    <div class="container py-4 py-md-5">
        <div class="text-center mb-4">
            @php
                $ponpesName = $tenant?->settings['ponpes_name'] ?? $tenant?->name ?? null;
                $ponpesLogo = isset($tenant?->settings['logo_path']) ? asset('storage/'.$tenant->settings['logo_path']) : null;
                $brandName = $ponpesName ? "PPDB Online - {$ponpesName}" : 'PPDB Online';
            @endphp
            @if ($ponpesLogo)
                <img src="{{ $ponpesLogo }}" alt="{{ $ponpesName }}" class="img-fluid mb-3" style="max-height: 64px;">
            @endif
            <h2 class="mb-1">Pendaftaran Santri Baru</h2>
            <p class="text-secondary">{{ $brandName }}</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-12">
                <form action="{{ route('ppdb.daftar.store') }}" method="POST" enctype="multipart/form-data" class="card">
                    @csrf
                    <div class="card-header">
                        <h3 class="card-title">Form Pendaftaran</h3>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                            </div>
                        @endif

                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label required">Gelombang Pendaftaran</label>
                                <select name="gelombang_id" class="form-select @error('gelombang_id') is-invalid @enderror" required>
                                    <option value="">Pilih Gelombang</option>
                                    @foreach ($gelombangs as $g)
                                        <option value="{{ $g->id }}" @selected(old('gelombang_id', $selectedGelombang?->id) == $g->id)>
                                            {{ $g->nama }} ({{ $g->tanggal_mulai->translatedFormat('d M Y') }} - {{ $g->tanggal_selesai->translatedFormat('d M Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('gelombang_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <h5 class="mb-3">Data Calon Santri</h5>
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label required">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap') }}" required>
                                @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror" value="{{ old('tempat_lahir') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label required">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                                    <option value="">Pilih</option>
                                    <option value="laki-laki" @selected(old('jenis_kelamin') === 'laki-laki')>Laki-laki</option>
                                    <option value="perempuan" @selected(old('jenis_kelamin') === 'perempuan')>Perempuan</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">Asal Sekolah</label>
                                <input type="text" name="asal_sekolah" class="form-control @error('asal_sekolah') is-invalid @enderror" value="{{ old('asal_sekolah') }}">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label required">No. HP</label>
                                <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp') }}" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="2">{{ old('alamat') }}</textarea>
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4">Data Orang Tua</h5>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Nama Ayah</label>
                                <input type="text" name="nama_ayah" class="form-control @error('nama_ayah') is-invalid @enderror" value="{{ old('nama_ayah') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Nama Ibu</label>
                                <input type="text" name="nama_ibu" class="form-control @error('nama_ibu') is-invalid @enderror" value="{{ old('nama_ibu') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">No. HP Orang Tua</label>
                                <input type="text" name="no_hp_orangtua" class="form-control @error('no_hp_orangtua') is-invalid @enderror" value="{{ old('no_hp_orangtua') }}">
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4">Berkas (PDF/JPG/PNG, maks 2MB per file)</h5>
                        <div class="mb-3">
                            <label class="form-label">Upload Berkas</label>
                            <input type="file" name="berkas[]" class="form-control @error('berkas.*') is-invalid @enderror" multiple accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-text">Upload foto, ijazah, atau dokumen pendukung lainnya.</div>
                            @error('berkas.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="card-footer d-flex flex-wrap justify-content-between gap-2">
                        <small class="text-secondary">* Field wajib diisi</small>
                        <button type="submit" class="btn btn-primary">Daftar Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
