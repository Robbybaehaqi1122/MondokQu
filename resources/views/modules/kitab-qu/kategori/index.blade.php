<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h2 class="page-title mt-1">Kategori Kitab</h2>
                <div class="text-secondary small">Kelola kategori untuk pengelompokan kitab.</div>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createKategoriModal">
                <i class="ti ti-plus"></i> Tambah Kategori
            </button>
        </div>
    </x-slot>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Total Kitab</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kategoris as $kategori)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $kategori->nama }}</div>
                                @if ($kategori->deskripsi)
                                    <div class="text-secondary small">{{ $kategori->deskripsi }}</div>
                                @endif
                            </td>
                            <td><span class="badge bg-primary-lt">{{ number_format($kategori->kitabs_count) }}</span></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editKategoriModal{{ $kategori->id }}">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('kitab.kategori.destroy', $kategori) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-secondary">Belum ada kategori.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($kategoris->hasPages())
            <div class="card-footer d-flex justify-content-center">{{ $kategoris->links() }}</div>
        @endif
    </div>

    {{-- Create Modal --}}
    <div class="modal modal-blur fade" id="createKategoriModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('kitab.kategori.store') }}">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Tambah Kategori</h5>
                            <div class="text-secondary small mt-1">Buat kategori baru untuk mengelompokkan kitab.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Nama Kategori</label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="2">{{ old('deskripsi') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modals --}}
    @foreach ($kategoris as $kategori)
        <div class="modal modal-blur fade" id="editKategoriModal{{ $kategori->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('kitab.kategori.update', $kategori) }}">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Edit Kategori</h5>
                                <div class="text-secondary small mt-1">Perbarui nama atau deskripsi kategori.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label required">Nama Kategori</label>
                                <input type="text" name="nama" class="form-control" value="{{ $kategori->nama }}" required>
                            </div>
                            <div>
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="2">{{ $kategori->deskripsi }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</x-app-layout>
