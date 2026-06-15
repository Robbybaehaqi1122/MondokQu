<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Nilai Sikap (Spiritual & Sosial)</h2>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('akademik.attitude.create') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Santri <span class="text-danger">*</span></label>
                        <select name="santri_id" class="form-select" required>
                            <option value="">-- Pilih Santri --</option>
                            @foreach ($santris as $s)
                                <option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->nis }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Semester <span class="text-danger">*</span></label>
                        <select name="semester" class="form-select" required>
                            <option value="">-- Pilih Semester --</option>
                            @foreach ($semesters as $sem)
                                <option value="{{ $sem }}">{{ $sem }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-edit me-1"></i> Input / Edit Nilai
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Aspek Penilaian</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-primary">Spiritual (Akhlak kepada Allah)</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Kejujuran</li>
                        <li class="list-group-item">Kedisiplinan Ibadah</li>
                        <li class="list-group-item">Akhlak</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5 class="text-success">Sosial (Akhlak kepada Sesama)</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Tanggung Jawab</li>
                        <li class="list-group-item">Kerjasama</li>
                        <li class="list-group-item">Sopan Santun</li>
                    </ul>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-secondary small mb-0">Predikat: <span class="badge bg-success-lt text-success">SB (Sangat Baik)</span> <span class="badge bg-primary-lt text-primary">B (Baik)</span> <span class="badge bg-warning-lt text-warning">C (Cukup)</span> <span class="badge bg-danger-lt text-danger">K (Kurang)</span></p>
            </div>
        </div>
    </div>
</x-app-layout>
