<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Kategori Aset</h2>
                <div class="text-secondary mt-1">Kelola kategori aset dan inventaris.</div>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Tambah Kategori</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inventaris.kategori.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label required">Nama Kategori</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon (Tabler)</label>
                            <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror"
                                   value="{{ old('icon') }}" placeholder="ti-device-desktop">
                            @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                <th>Icon</th>
                                <th>Nama</th>
                                <th>Jumlah Aset</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kategoris as $k)
                                <tr>
                                    <td>
                                        @if ($k->icon)
                                            <i class="ti {{ $k->icon }}"></i>
                                        @else
                                            <i class="ti ti-folder text-muted"></i>
                                        @endif
                                    </td>
                                    <td class="fw-semibold">{{ $k->name }}</td>
                                    <td>{{ number_format($k->asets_count) }}</td>
                                    <td>
                                        <button class="btn btn-icon btn-outline-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editKategori{{ $k->id }}" title="Edit">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                        <form action="{{ route('inventaris.kategori.destroy', $k) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Hapus kategori {{ $k->name }}?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-icon btn-outline-danger btn-sm" title="Hapus">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada kategori.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @foreach ($kategoris as $k)
        <div class="modal fade" id="editKategori{{ $k->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('inventaris.kategori.update', $k) }}">
                    @csrf @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Edit Kategori</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label required">Nama Kategori</label>
                                <input type="text" name="name" class="form-control" value="{{ $k->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Icon</label>
                                <input type="text" name="icon" class="form-control" value="{{ $k->icon }}" placeholder="ti-device-desktop">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="2">{{ $k->description }}</textarea>
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
