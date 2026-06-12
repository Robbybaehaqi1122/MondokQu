<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Target Hafalan Baru</h2>
            <div class="text-secondary mt-1">Tetapkan target hafalan Al-Quran untuk santri.</div>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('tahfidz.targets.store') }}" class="row g-3">
                @csrf

                <div class="col-md-6">
                    <label class="form-label">Santri <span class="text-danger">*</span></label>
                    <select name="santri_id" class="form-select @error('santri_id') is-invalid @enderror" required>
                        <option value="">Pilih Santri</option>
                        @foreach ($santriOptions as $santri)
                            <option value="{{ $santri->id }}" @selected(old('santri_id', $preselectedSantriId) == $santri->id)>
                                {{ $santri->full_name }} ({{ $santri->nis }})
                            </option>
                        @endforeach
                    </select>
                    @error('santri_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Jenis Target <span class="text-danger">*</span></label>
                    <select name="target_type" class="form-select @error('target_type') is-invalid @enderror" required>
                        <option value="">Pilih Jenis</option>
                        @foreach ($typeOptions as $opt)
                            <option value="{{ $opt['value'] }}" @selected(old('target_type') === $opt['value'])>
                                {{ $opt['label'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('target_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Nilai Target <span class="text-danger">*</span></label>
                    <input type="number" name="target_value" class="form-control @error('target_value') is-invalid @enderror"
                           value="{{ old('target_value') }}" min="1" max="99999" required>
                    @error('target_value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Jumlah juz, surah, atau ayat yang ditargetkan.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Target Selesai</label>
                    <input type="date" name="target_date" class="form-control @error('target_date') is-invalid @enderror"
                           value="{{ old('target_date') }}">
                    @error('target_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Kosongkan jika tidak ada deadline.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2"
                              maxlength="1000">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan
                        </button>
                        <a href="{{ route('tahfidz.targets.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>