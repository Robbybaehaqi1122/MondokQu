<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Lokasi Aset</h2>
                <div class="text-secondary mt-1">Kelola lokasi penyimpanan aset.</div>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Tambah Lokasi</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inventaris.lokasi.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label required">Nama Lokasi</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Gedung</label>
                                <input type="text" name="building" class="form-control @error('building') is-invalid @enderror"
                                       value="{{ old('building') }}">
                                @error('building') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lantai</label>
                                <input type="text" name="floor" class="form-control @error('floor') is-invalid @enderror"
                                       value="{{ old('floor') }}">
                                @error('floor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Nama Lokasi</th>
                                <th>Gedung</th>
                                <th>Lantai</th>
                                <th>Jumlah Aset</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lokasis as $l)
                                <tr>
                                    <td class="fw-semibold">{{ $l->name }}</td>
                                    <td>{{ $l->building ?? '-' }}</td>
                                    <td>{{ $l->floor ?? '-' }}</td>
                                    <td>{{ number_format($l->asets_count) }}</td>
                                    <td>
                                        <button class="btn btn-icon btn-outline-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editLokasi{{ $l->id }}" title="Edit">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                        <form action="{{ route('inventaris.lokasi.destroy', $l) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Hapus lokasi {{ $l->name }}?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-icon btn-outline-danger btn-sm" title="Hapus">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada lokasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @foreach ($lokasis as $l)
        <div class="modal fade" id="editLokasi{{ $l->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('inventaris.lokasi.update', $l) }}">
                    @csrf @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Edit Lokasi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label required">Nama Lokasi</label>
                                <input type="text" name="name" class="form-control" value="{{ $l->name }}" required>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Gedung</label>
                                    <input type="text" name="building" class="form-control" value="{{ $l->building }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Lantai</label>
                                    <input type="text" name="floor" class="form-control" value="{{ $l->floor }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="2">{{ $l->description }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</x-app-layout>
