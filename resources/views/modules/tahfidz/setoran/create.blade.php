<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Catat Setoran Hafalan</h2>
            <div class="text-secondary mt-1">Catat setoran hafalan Al-Quran untuk santri.</div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('tahfidz.setoran.store') }}" id="setoranForm">
        @csrf

        <div class="row row-cards mb-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Setoran</h3>
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
                            @error('santri_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="session_date" class="form-label">Tanggal Setoran <span class="text-danger">*</span></label>
                            <input type="date" id="session_date" name="session_date" class="form-control @error('session_date') is-invalid @enderror"
                                value="{{ old('session_date', now()->toDateString()) }}" required>
                            @error('session_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Catatan tambahan tentang setoran ini...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <label class="form-label">Status</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="status_completed" value="completed" @checked(old('status', 'completed') === 'completed')>
                                <label class="form-check-label" for="status_completed">Selesai</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="status_draft" value="draft" @checked(old('status') === 'draft')>
                                <label class="form-check-label" for="status_draft">Draft</label>
                            </div>
                        </div>
                    </div>
                </div>

                @error('records')
                    <div class="alert alert-danger mt-3">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div>
                                <h3 class="card-title">Ayat yang Disetorkan</h3>
                                <div class="text-secondary small mt-1">Tambahkan ayat yang disetorkan dalam sesi ini.</div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addRecordBtn">
                                <i class="ti ti-plus me-1"></i>
                                Tambah Ayat
                            </button>
                        </div>
                    </div>
                    <div class="card-body" id="recordsContainer">
                        @if (old('records'))
                            @foreach (old('records') as $index => $record)
                                <div class="record-item border rounded p-3 mb-3">
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <label class="form-label">Surah</label>
                                            <select name="records[{{ $index }}][surah_id]" class="form-select" required>
                                                <option value="">Pilih Surah</option>
                                                @foreach ($surahOptions as $surah)
                                                    <option value="{{ $surah->id }}" @selected($record['surah_id'] == $surah->id)>
                                                        {{ $surah->number }}. {{ $surah->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("records.{$index}.surah_id")
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Ayat Dari</label>
                                            <input type="number" name="records[{{ $index }}][verse_start]" class="form-control" min="1" value="{{ $record['verse_start'] }}" required>
                                            @error("records.{$index}.verse_start")
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Sampai</label>
                                            <input type="number" name="records[{{ $index }}][verse_end]" class="form-control" min="1" value="{{ $record['verse_end'] }}" required>
                                            @error("records.{$index}.verse_end")
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Penilaian</label>
                                            <select name="records[{{ $index }}][evaluation]" class="form-select" required>
                                                <option value="">Pilih</option>
                                                @foreach ($evaluationOptions as $eval)
                                                    <option value="{{ $eval }}" @selected($record['evaluation'] == $eval)>
                                                        {{ ucfirst(str_replace('_', ' ', $eval)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("records.{$index}.evaluation")
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row g-2 mt-2">
                                        <div class="col-md-10">
                                            <input type="text" name="records[{{ $index }}][notes]" class="form-control" placeholder="Catatan (opsional)" value="{{ $record['notes'] ?? '' }}">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-record-btn">Hapus</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="record-item border rounded p-3 mb-3">
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <label class="form-label">Surah</label>
                                        <select name="records[0][surah_id]" class="form-select" required>
                                            <option value="">Pilih Surah</option>
                                            @foreach ($surahOptions as $surah)
                                                <option value="{{ $surah->id }}">{{ $surah->number }}. {{ $surah->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Ayat Dari</label>
                                        <input type="number" name="records[0][verse_start]" class="form-control" min="1" value="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Sampai</label>
                                        <input type="number" name="records[0][verse_end]" class="form-control" min="1" value="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Penilaian</label>
                                        <select name="records[0][evaluation]" class="form-select" required>
                                            <option value="">Pilih</option>
                                            @foreach ($evaluationOptions as $eval)
                                                <option value="{{ $eval }}">{{ ucfirst(str_replace('_', ' ', $eval)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-md-10">
                                        <input type="text" name="records[0][notes]" class="form-control" placeholder="Catatan (opsional)">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-record-btn">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i>
                Simpan Setoran
            </button>
            <a href="{{ route('tahfidz.setoran.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let recordIndex = {{ old('records') ? count(old('records')) : 1 }};
        const container = document.getElementById('recordsContainer');
        const addBtn = document.getElementById('addRecordBtn');

        const surahOptionsHtml = `@foreach ($surahOptions as $surah)<option value="{{ $surah->id }}">{{ $surah->number }}. {{ $surah->name }}</option>@endforeach`;
        const evalOptionsHtml = `@foreach ($evaluationOptions as $eval)<option value="{{ $eval }}">{{ ucfirst(str_replace('_', ' ', $eval)) }}</option>@endforeach`;

        addBtn.addEventListener('click', () => {
            const div = document.createElement('div');
            div.className = 'record-item border rounded p-3 mb-3';
            div.innerHTML = `
                <div class="row g-2">
                    <div class="col-md-5">
                        <label class="form-label">Surah</label>
                        <select name="records[${recordIndex}][surah_id]" class="form-select" required>
                            <option value="">Pilih Surah</option>
                            ${surahOptionsHtml}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ayat Dari</label>
                        <input type="number" name="records[${recordIndex}][verse_start]" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sampai</label>
                        <input type="number" name="records[${recordIndex}][verse_end]" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Penilaian</label>
                        <select name="records[${recordIndex}][evaluation]" class="form-select" required>
                            <option value="">Pilih</option>
                            ${evalOptionsHtml}
                        </select>
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-md-10">
                        <input type="text" name="records[${recordIndex}][notes]" class="form-control" placeholder="Catatan (opsional)">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-record-btn">Hapus</button>
                    </div>
                </div>
            `;
            container.appendChild(div);
            recordIndex++;
        });

        container.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-record-btn')) {
                const items = container.querySelectorAll('.record-item');
                if (items.length > 1) {
                    e.target.closest('.record-item').remove();
                }
            }
        });
    });
</script>
