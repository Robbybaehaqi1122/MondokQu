<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Kategori Pelanggaran</h2>
                <div class="text-secondary mt-1">Kelola kategori dan poin pelanggaran santri.</div>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createKategoriModal">
                <i class="ti ti-plus me-1"></i>
                Tambah Kategori
            </button>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('pelanggaran.kategori.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari Kategori</label>
                    <input type="text" name="q" class="form-control" placeholder="Nama kategori..." value="{{ $filters['q'] }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-filter"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama Kategori</th>
                        <th>Poin</th>
                        <th>Deskripsi</th>
                        <th>Digunakan</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kategoris as $kategori)
                        <tr>
                            <td class="fw-semibold">{{ $kategori->nama }}</td>
                            <td>
                                <span class="badge bg-orange-lt text-orange">{{ $kategori->poin }} poin</span>
                            </td>
                            <td class="text-secondary">{{ $kategori->deskripsi ?? '-' }}</td>
                            <td>{{ $kategori->pelanggarans_count }} kali</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editKategoriModal{{ $kategori->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    @if ($kategori->pelanggarans_count === 0)
                                        <form method="POST" action="{{ route('pelanggaran.kategori.destroy', $kategori) }}" onsubmit="return confirm('Yakin ingin menghapus kategori {{ $kategori->nama }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-secondary">Belum ada kategori pelanggaran. Buat kategori baru untuk mulai mencatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($kategoris->hasPages())
            <div class="card-footer">
                {{ $kategoris->links() }}
            </div>
        @endif
    </div>

    {{-- Create Modal --}}
    <div class="modal modal-blur fade" id="createKategoriModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('pelanggaran.kategori.store') }}">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Tambah Kategori Pelanggaran</h5>
                            <div class="text-secondary small mt-1">Buat kategori pelanggaran baru dengan poin tertentu.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" placeholder="Contoh: Merokok" required>
                            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Poin <span class="text-danger">*</span></label>
                            <input type="number" name="poin" class="form-control @error('poin') is-invalid @enderror" value="{{ old('poin', 10) }}" min="1" max="999" required>
                            @error('poin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3" placeholder="Penjelasan kategori...">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <form method="POST" action="{{ route('pelanggaran.kategori.update', $kategori) }}">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Edit Kategori Pelanggaran</h5>
                                <div class="text-secondary small mt-1">Ubah nama, poin, atau deskripsi kategori.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" value="{{ $kategori->nama }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Poin <span class="text-danger">*</span></label>
                                <input type="number" name="poin" class="form-control" value="{{ $kategori->poin }}" min="1" max="999" required>
                            </div>
                            <div>
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3">{{ $kategori->deskripsi }}</textarea>
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
    @endforeach
</x-app-layout>
