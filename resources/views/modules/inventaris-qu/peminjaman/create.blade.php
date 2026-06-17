<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Catat Peminjaman Aset</h2>
            </div>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('inventaris.peminjaman.store') }}">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label required">Aset</label>
                        <select name="aset_id" class="form-select @error('aset_id') is-invalid @enderror" required>
                            <option value="">Pilih Aset</option>
                            @foreach ($asets as $a)
                                <option value="{{ $a->id }}" {{ old('aset_id') == $a->id ? 'selected' : '' }}>
                                    {{ $a->kode_aset }} - {{ $a->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('aset_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Tanggal Pinjam</label>
                        <input type="date" name="tanggal_pinjam" class="form-control @error('tanggal_pinjam') is-invalid @enderror"
                               value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" required>
                        @error('tanggal_pinjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rencana Kembali</label>
                        <input type="date" name="tanggal_kembali" class="form-control @error('tanggal_kembali') is-invalid @enderror"
                               value="{{ old('tanggal_kembali') }}">
                        @error('tanggal_kembali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Peminjam</label>
                        <input type="text" name="peminjam" class="form-control @error('peminjam') is-invalid @enderror"
                               value="{{ old('peminjam') }}" required>
                        @error('peminjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role / Jabatan</label>
                        <input type="text" name="role_peminjam" class="form-control @error('role_peminjam') is-invalid @enderror"
                               value="{{ old('role_peminjam') }}" placeholder="Santri, Ustadz, Karyawan...">
                        @error('role_peminjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Tujuan Peminjaman</label>
                        <textarea name="tujuan" class="form-control @error('tujuan') is-invalid @enderror"
                                  rows="2">{{ old('tujuan') }}</textarea>
                        @error('tujuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('inventaris.peminjaman.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
