<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Mata Pelajaran</h2>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create">
                <i class="ti ti-plus me-1"></i> Tambah Mapel
            </button>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>KKM</th>
                        <th>Jumlah Nilai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mapels as $mapel)
                        <tr>
                            <td class="fw-semibold">{{ $mapel->nama }}</td>
                            <td class="text-secondary">{{ $mapel->deskripsi ?: '-' }}</td>
                            <td><span class="badge bg-azure-lt text-azure">{{ $mapel->kkm }}</span></td>
                            <td>{{ number_format($mapel->nilai_santris_count) }}</td>
                            <td>
                                @if ($mapel->is_active)
                                    <span class="badge bg-success-lt text-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-lt text-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#modal-edit-{{ $mapel->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    @if ($mapel->nilai_santris_count === 0)
                                        <form action="{{ route('akademik.mata-pelajaran.destroy', $mapel) }}" method="POST"
                                            onsubmit="return confirm('Hapus mata pelajaran {{ $mapel->nama }}?')">
                                            @csrf @method('DELETE')
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
                            <td colspan="6" class="text-secondary text-center py-4">
                                Belum ada mata pelajaran. Klik "Tambah Mapel" untuk memulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($mapels->hasPages())
            <div class="card-footer">{{ $mapels->links() }}</div>
        @endif
    </div>

    {{-- Modal Create --}}
    <div class="modal fade" id="modal-create" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('akademik.mata-pelajaran.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Mata Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                            value="{{ old('nama') }}" required maxlength="255">
                        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="2">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">KKM (Kriteria Ketuntasan Minimal) <span class="text-danger">*</span></label>
                        <input type="number" name="kkm" class="form-control @error('kkm') is-invalid @enderror"
                            value="{{ old('kkm', 70) }}" min="0" max="100" required>
                        @error('kkm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    @foreach ($mapels as $mapel)
        <div class="modal fade" id="modal-edit-{{ $mapel->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form action="{{ route('akademik.mata-pelajaran.update', $mapel) }}" method="POST" class="modal-content">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Mata Pelajaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                                value="{{ old('nama', $mapel->nama) }}" required maxlength="255">
                            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="2">{{ old('deskripsi', $mapel->deskripsi) }}</textarea>
                            @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">KKM <span class="text-danger">*</span></label>
                            <input type="number" name="kkm" class="form-control @error('kkm') is-invalid @enderror"
                                value="{{ old('kkm', $mapel->kkm) }}" min="0" max="100" required>
                            @error('kkm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-check form-switch">
                                <input type="checkbox" name="is_active" class="form-check-input" value="1"
                                    {{ old('is_active', $mapel->is_active) ? 'checked' : '' }}>
                                <span class="form-check-label">Aktif</span>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</x-app-layout>
