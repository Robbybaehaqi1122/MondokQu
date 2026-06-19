<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title mt-1">Tambah Setoran</h2>
            <div class="text-secondary small">Catat setoran hafalan kitab santri.</div>
        </div>
    </x-slot>

    <form action="{{ route('kitab.setoran.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-header"><h3 class="card-title">Data Setoran</h3></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Santri</label>
                        <select name="santri_id" class="form-select @error('santri_id') is-invalid @enderror" required>
                            <option value="">Pilih Santri</option>
                            @foreach ($santris as $s)
                                <option value="{{ $s->id }}" @selected(old('santri_id') == $s->id)>{{ $s->full_name }} ({{ $s->nis }})</option>
                            @endforeach
                        </select>
                        @error('santri_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Kitab</label>
                        <select name="kitab_id" class="form-select @error('kitab_id') is-invalid @enderror" required>
                            <option value="">Pilih Kitab</option>
                            @foreach ($kitabs as $k)
                                <option value="{{ $k->id }}" @selected(old('kitab_id') == $k->id)>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                        @error('kitab_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Tanggal Setoran</label>
                        <input type="date" name="tanggal_setoran" class="form-control @error('tanggal_setoran') is-invalid @enderror" value="{{ old('tanggal_setoran', now()->format('Y-m-d')) }}" required>
                        @error('tanggal_setoran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Materi</label>
                        <input type="text" name="materi" class="form-control @error('materi') is-invalid @enderror" value="{{ old('materi') }}" placeholder="Contoh: Bab 1-3, Halaman 10-20">
                        @error('materi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3">{{ old('catatan') }}</textarea>
                        @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="{{ route('kitab.setoran.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Setoran</button>
            </div>
        </div>
    </form>
</x-app-layout>
