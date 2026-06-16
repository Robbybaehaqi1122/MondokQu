<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Tingkat Sanksi Pelanggaran</h2>
                <div class="text-secondary mt-1">Atur ambang batas poin dan jenis sanksi otomatis.</div>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Tambah Tingkat Sanksi</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('pelanggaran.sanction-thresholds.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Nama Sanksi <span class="text-danger">*</span></label>
                    <input name="name" class="form-control @error('name') is-invalid @enderror" required maxlength="255">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jenis <span class="text-danger">*</span></label>
                    <select name="sanction_type" class="form-select @error('sanction_type') is-invalid @enderror" required>
                        @foreach (\App\Models\SanctionThreshold::sanctionTypes() as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('sanction_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Min Poin <span class="text-danger">*</span></label>
                    <input name="min_points" type="number" class="form-control @error('min_points') is-invalid @enderror" required min="0">
                    @error('min_points') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Max Poin</label>
                    <input name="max_points" type="number" class="form-control @error('max_points') is-invalid @enderror" min="0">
                    @error('max_points') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-plus"></i>
                    </button>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi (opsional)</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" maxlength="1000"></textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Tingkat Sanksi</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th>Min Poin</th>
                        <th>Max Poin</th>
                        <th>Deskripsi</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($thresholds as $t)
                        <tr>
                            <td class="fw-semibold">{{ $t->name }}</td>
                            <td><span class="badge bg-primary-lt text-primary">{{ $t->typeLabel() }}</span></td>
                            <td>{{ number_format($t->min_points) }}</td>
                            <td>{{ $t->max_points !== null ? number_format($t->max_points) : '&infin;' }}</td>
                            <td class="text-secondary small">{{ $t->description ?? '-' }}</td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn btn-outline-secondary btn-icon dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-{{ $t->id }}">
                                            <i class="ti ti-edit me-2"></i> Edit
                                        </button>
                                        <form method="POST" action="{{ route('pelanggaran.sanction-thresholds.destroy', $t) }}" onsubmit="return confirm('Hapus tingkat sanksi ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="ti ti-trash me-2"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="modal fade" id="edit-{{ $t->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('pelanggaran.sanction-thresholds.update', $t) }}" class="modal-content">
                                            @csrf @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Sanksi</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Nama</label>
                                                    <input name="name" class="form-control" value="{{ $t->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Jenis</label>
                                                    <select name="sanction_type" class="form-select" required>
                                                        @foreach (\App\Models\SanctionThreshold::sanctionTypes() as $val => $label)
                                                            <option value="{{ $val }}" {{ $t->sanction_type === $val ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label">Min Poin</label>
                                                        <input name="min_points" type="number" class="form-control" value="{{ $t->min_points }}" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Max Poin</label>
                                                        <input name="max_points" type="number" class="form-control" value="{{ $t->max_points }}">
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Deskripsi</label>
                                                    <textarea name="description" class="form-control" rows="2">{{ $t->description }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-secondary text-center">Belum ada tingkat sanksi. Tambahkan di atas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
