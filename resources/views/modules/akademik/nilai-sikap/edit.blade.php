<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Edit Nilai Sikap</h2>
                <div class="text-secondary mt-1">{{ $nilaiSikap->santri->full_name }} &middot; Semester {{ $nilaiSikap->semester }}</div>
            </div>
            <a href="{{ route('akademik.nilai-sikap.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </x-slot>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('akademik.nilai-sikap.update', $nilaiSikap) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Data Santri & Semester</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Santri <span class="text-danger">*</span></label>
                                <select name="santri_id" class="form-select" required>
                                    <option value="">-- Pilih Santri --</option>
                                    @foreach ($santris as $s)
                                        <option value="{{ $s->id }}" {{ old('santri_id', $nilaiSikap->santri_id) == $s->id ? 'selected' : '' }}>
                                            {{ $s->full_name }} ({{ $s->nis }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Semester <span class="text-danger">*</span></label>
                                <select name="semester" class="form-select" required>
                                    <option value="">-- Pilih Semester --</option>
                                    @foreach ($semesters as $sem)
                                        <option value="{{ $sem }}" {{ old('semester', $nilaiSikap->semester) == $sem ? 'selected' : '' }}>
                                            {{ $sem }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="text-primary">Sikap Spiritual (Akhlak kepada Allah)</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Predikat</label>
                            <select name="sikap_spiritual" class="form-select">
                                <option value="">-- Pilih Predikat --</option>
                                <option value="SB" {{ old('sikap_spiritual', $nilaiSikap->sikap_spiritual) === 'SB' ? 'selected' : '' }}>SB - Sangat Baik</option>
                                <option value="B" {{ old('sikap_spiritual', $nilaiSikap->sikap_spiritual) === 'B' ? 'selected' : '' }}>B - Baik</option>
                                <option value="C" {{ old('sikap_spiritual', $nilaiSikap->sikap_spiritual) === 'C' ? 'selected' : '' }}>C - Cukup</option>
                                <option value="K" {{ old('sikap_spiritual', $nilaiSikap->sikap_spiritual) === 'K' ? 'selected' : '' }}>K - Kurang</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Deskripsi / Uraian</label>
                            <textarea name="deskripsi_spiritual" class="form-control" rows="3"
                                placeholder="Uraian penilaian sikap spiritual...">{{ old('deskripsi_spiritual', $nilaiSikap->deskripsi_spiritual) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="text-success">Sikap Sosial (Akhlak kepada Sesama)</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Predikat</label>
                            <select name="sikap_sosial" class="form-select">
                                <option value="">-- Pilih Predikat --</option>
                                <option value="SB" {{ old('sikap_sosial', $nilaiSikap->sikap_sosial) === 'SB' ? 'selected' : '' }}>SB - Sangat Baik</option>
                                <option value="B" {{ old('sikap_sosial', $nilaiSikap->sikap_sosial) === 'B' ? 'selected' : '' }}>B - Baik</option>
                                <option value="C" {{ old('sikap_sosial', $nilaiSikap->sikap_sosial) === 'C' ? 'selected' : '' }}>C - Cukup</option>
                                <option value="K" {{ old('sikap_sosial', $nilaiSikap->sikap_sosial) === 'K' ? 'selected' : '' }}>K - Kurang</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Deskripsi / Uraian</label>
                            <textarea name="deskripsi_sosial" class="form-control" rows="3"
                                placeholder="Uraian penilaian sikap sosial...">{{ old('deskripsi_sosial', $nilaiSikap->deskripsi_sosial) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Catatan Wali</h3>
                    </div>
                    <div class="card-body">
                        <textarea name="catatan_wali" class="form-control" rows="3"
                            placeholder="Catatan tambahan dari wali kelas (opsional)...">{{ old('catatan_wali', $nilaiSikap->catatan_wali) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Referensi Predikat</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <span class="badge bg-success-lt text-success me-1">SB</span> Sangat Baik
                            </li>
                            <li class="list-group-item">
                                <span class="badge bg-primary-lt text-primary me-1">B</span> Baik
                            </li>
                            <li class="list-group-item">
                                <span class="badge bg-warning-lt text-warning me-1">C</span> Cukup
                            </li>
                            <li class="list-group-item">
                                <span class="badge bg-danger-lt text-danger me-1">K</span> Kurang
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
