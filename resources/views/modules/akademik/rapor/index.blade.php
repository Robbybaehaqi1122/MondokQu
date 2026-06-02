<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Rapor Digital</h2>
            </div>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('akademik.rapor.show') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Santri <span class="text-danger">*</span></label>
                    <select name="santri_id" class="form-select" required>
                        <option value="">-- Pilih Santri --</option>
                        @foreach ($santris as $santri)
                            <option value="{{ $santri->id }}" {{ request('santri_id') == $santri->id ? 'selected' : '' }}>
                                {{ $santri->full_name }} ({{ $santri->nis }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Semester <span class="text-danger">*</span></label>
                    <select name="semester" class="form-select" required>
                        <option value="">-- Pilih Semester --</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-search me-1"></i> Lihat Rapor
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if (request('santri_id') && request('semester'))
        <div class="mt-3">
            <div class="d-flex justify-content-end mb-2">
                <a href="{{ route('akademik.rapor.pdf', ['santri_id' => request('santri_id'), 'semester' => request('semester')]) }}"
                    class="btn btn-outline-danger">
                    <i class="ti ti-file-download me-1"></i> Download PDF
                </a>
            </div>
        </div>
    @endif
</x-app-layout>
