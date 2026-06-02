<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Edit Nilai Santri</h2>
            </div>
            <a href="{{ route('akademik.nilai.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </x-slot>

    <div class="card">
        <form action="{{ route('akademik.nilai.update', $nilai) }}" method="POST">
            @csrf @method('PUT')
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Santri</label>
                        <div class="form-control-plaintext fw-semibold">{{ $nilai->santri?->full_name }} ({{ $nilai->santri?->nis }})</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mata Pelajaran</label>
                        <div class="form-control-plaintext fw-semibold">{{ $nilai->mataPelajaran?->nama }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Semester</label>
                        <div class="form-control-plaintext fw-semibold">{{ $nilai->semester }}</div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Nilai Pengetahuan <span class="text-danger">*</span></label>
                        <input type="number" name="nilai_pengetahuan"
                            class="form-control @error('nilai_pengetahuan') is-invalid @enderror"
                            value="{{ old('nilai_pengetahuan', $nilai->nilai_pengetahuan) }}" min="0" max="100" required>
                        @error('nilai_pengetahuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nilai Keterampilan <span class="text-danger">*</span></label>
                        <input type="number" name="nilai_keterampilan"
                            class="form-control @error('nilai_keterampilan') is-invalid @enderror"
                            value="{{ old('nilai_keterampilan', $nilai->nilai_keterampilan) }}" min="0" max="100" required>
                        @error('nilai_keterampilan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nilai Akhir</label>
                        <div id="nilai-akhir-preview" class="form-control-plaintext fw-bold fs-3">{{ $nilai->nilai_akhir }}</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes', $nilai->notes) }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> Perbarui Nilai
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
