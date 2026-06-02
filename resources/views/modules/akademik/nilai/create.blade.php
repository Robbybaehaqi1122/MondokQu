<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Input Nilai Santri</h2>
            </div>
            <a href="{{ route('akademik.nilai.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </x-slot>

    <div class="card">
        <form action="{{ route('akademik.nilai.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Santri <span class="text-danger">*</span></label>
                        <select name="santri_id" class="form-select @error('santri_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Santri --</option>
                            @foreach ($santris as $santri)
                                <option value="{{ $santri->id }}" {{ old('santri_id') == $santri->id ? 'selected' : '' }}>
                                    {{ $santri->full_name }} ({{ $santri->nis }})
                                </option>
                            @endforeach
                        </select>
                        @error('santri_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="mata_pelajaran_id" class="form-select @error('mata_pelajaran_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Mapel --</option>
                            @foreach ($mapels as $mapel)
                                <option value="{{ $mapel->id }}" {{ old('mata_pelajaran_id') == $mapel->id ? 'selected' : '' }}>
                                    {{ $mapel->nama }} (KKM: {{ $mapel->kkm }})
                                </option>
                            @endforeach
                        </select>
                        @error('mata_pelajaran_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Semester <span class="text-danger">*</span></label>
                        <select name="semester" class="form-select @error('semester') is-invalid @enderror" required>
                            <option value="">-- Pilih Semester --</option>
                            @foreach ($semesters as $sem)
                                <option value="{{ $sem }}" {{ old('semester') == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                            @endforeach
                        </select>
                        @error('semester') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Nilai Pengetahuan <span class="text-danger">*</span></label>
                        <input type="number" name="nilai_pengetahuan"
                            class="form-control @error('nilai_pengetahuan') is-invalid @enderror"
                            value="{{ old('nilai_pengetahuan') }}" min="0" max="100" required>
                        @error('nilai_pengetahuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nilai Keterampilan <span class="text-danger">*</span></label>
                        <input type="number" name="nilai_keterampilan"
                            class="form-control @error('nilai_keterampilan') is-invalid @enderror"
                            value="{{ old('nilai_keterampilan') }}" min="0" max="100" required>
                        @error('nilai_keterampilan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nilai Akhir</label>
                        <div id="nilai-akhir-preview" class="form-control-plaintext fw-bold fs-3">-</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> Simpan Nilai
                </button>
            </div>
        </form>
    </div>
</x-app-layout>

@push('scripts')
<script>
    const pengetahuan = document.querySelector('[name="nilai_pengetahuan"]');
    const keterampilan = document.querySelector('[name="nilai_keterampilan"]');
    const preview = document.getElementById('nilai-akhir-preview');

    function updatePreview() {
        const p = parseInt(pengetahuan.value) || 0;
        const k = parseInt(keterampilan.value) || 0;
        preview.textContent = Math.round((p + k) / 2);
    }

    pengetahuan.addEventListener('input', updatePreview);
    keterampilan.addEventListener('input', updatePreview);
</script>
@endpush
