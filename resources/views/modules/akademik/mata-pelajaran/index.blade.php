<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h2 class="page-title">Mata Pelajaran</h2>
            </div>
            <div class="d-flex gap-2">
                <div class="d-none d-sm-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-clone">
                        <i class="ti ti-copy me-1"></i> Clone Mapel
                    </button>
                    <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#modal-grade">
                        <i class="ti ti-layers-difference me-1"></i> Kelola Tingkat
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create">
                        <i class="ti ti-plus me-1"></i> Tambah Mapel
                    </button>
                </div>
                <div class="dropdown d-sm-none">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 me-1"></i> Aksi
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-create">
                                <i class="ti ti-plus me-2 text-success"></i> Tambah Mapel
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-grade">
                                <i class="ti ti-layers-difference me-2 text-info"></i> Kelola Tingkat
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-clone">
                                <i class="ti ti-copy me-2 text-primary"></i> Clone Mapel
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Filter Tingkat --}}
    @if ($gradeLevels->isNotEmpty())
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-secondary fw-semibold small text-nowrap">Filter:</span>
                    <div class="d-flex gap-1 overflow-auto pb-1 filter-scroll">
                        <a href="{{ route('akademik.mata-pelajaran.index') }}"
                            class="btn btn-sm text-nowrap {{ !$selectedGradeId ? 'btn-primary' : 'btn-outline-primary' }}">
                            Semua
                        </a>
                        @foreach ($gradeLevels as $gl)
                            <a href="{{ route('akademik.mata-pelajaran.index', ['grade_level_id' => $gl->id]) }}"
                                class="btn btn-sm text-nowrap {{ (int) $selectedGradeId === $gl->id ? 'btn-primary' : 'btn-outline-primary' }}">
                                {{ $gl->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-mobile-md">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th class="d-none d-md-table-cell">Deskripsi</th>
                        <th>KKM</th>
                        <th class="d-none d-md-table-cell">Tingkat</th>
                        <th class="d-none d-md-table-cell">Jumlah Nilai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mapels as $mapel)
                        @php
                            $glNames = $mapel->gradeLevels->pluck('name')->implode(', ');
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $mapel->nama }}</td>
                            <td class="text-secondary d-none d-md-table-cell">{{ $mapel->deskripsi ?: '-' }}</td>
                            <td><span class="badge bg-azure-lt text-azure">{{ $mapel->kkm }}</span></td>
                            <td class="d-none d-md-table-cell">
                                @if ($glNames)
                                    <span class="text-secondary small">{{ $glNames }}</span>
                                @else
                                    <span class="badge bg-secondary-lt text-secondary">Global</span>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell">{{ number_format($mapel->nilai_santris_count) }}</td>
                            <td>
                                @if ($mapel->is_active)
                                    <span class="badge bg-success-lt text-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-lt text-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
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
                            <td colspan="7" class="text-secondary text-center py-4">
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

    {{-- Modal Grade Level --}}
    <div class="modal fade" id="modal-grade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kelola Tingkat / Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if ($gradeLevels->isEmpty())
                        <p class="text-secondary">Belum ada tingkat. Buat tingkat pertama.</p>
                    @endif
                    <div class="list-group mb-3">
                        @foreach ($gradeLevels as $gl)
                            <div class="list-group-item d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fw-semibold">{{ $gl->name }}</span>
                                    <span class="badge bg-secondary-lt text-secondary ms-2">Urutan {{ $gl->order }}</span>
                                </div>
                                <div class="d-flex gap-1">
                                    <form action="{{ route('akademik.grade-level.toggle', $gl) }}" method="POST">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-sm {{ $gl->is_active ? 'btn-success' : 'btn-secondary' }}">
                                            {{ $gl->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('akademik.grade-level.destroy', $gl) }}" method="POST"
                                        onsubmit="return confirm('Hapus tingkat {{ $gl->name }}? Semua relasi mapel akan terhapus.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <hr>
                    <form action="{{ route('akademik.grade-level.store') }}" method="POST">
                        @csrf
                        <div class="row g-2">
                            <div class="col">
                                <input type="text" name="name" class="form-control" placeholder="Nama tingkat (e.g. VII, VIII, IX)" required>
                            </div>
                            <div class="col-auto">
                                <input type="number" name="order" class="form-control" placeholder="Urutan" value="{{ $gradeLevels->count() + 1 }}" style="width:80px">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">Tambah</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Clone --}}
    <div class="modal fade" id="modal-clone" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('akademik.mata-pelajaran.clone') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Clone Template Mapel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Dari Tingkat <span class="text-danger">*</span></label>
                        <select name="from_grade_level_id" class="form-select" required>
                            <option value="">Pilih tingkat sumber</option>
                            @foreach ($gradeLevels as $gl)
                                <option value="{{ $gl->id }}">{{ $gl->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ke Tingkat <span class="text-danger">*</span></label>
                        <select name="to_grade_level_id" class="form-select" required>
                            <option value="">Pilih tingkat tujuan</option>
                            @foreach ($gradeLevels as $gl)
                                <option value="{{ $gl->id }}">{{ $gl->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Clone Sekarang</button>
                </div>
            </form>
        </div>
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
                    <div class="mb-3">
                        <label class="form-label">Tingkat / Kelas</label>
                        <div class="row g-2">
                            @forelse ($gradeLevels as $gl)
                                <div class="col-6">
                                    <label class="form-check">
                                        <input type="checkbox" name="grade_level_ids[]" value="{{ $gl->id }}"
                                            class="form-check-input"
                                            {{ in_array($gl->id, old('grade_level_ids', $selectedGradeId ? [$selectedGradeId] : [])) ? 'checked' : '' }}>
                                        <span class="form-check-label">{{ $gl->name }}</span>
                                    </label>
                                </div>
                            @empty
                                <div class="col-12 text-secondary small">Belum ada tingkat. Buat tingkat dulu.</div>
                            @endforelse
                        </div>
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
        @php
            $mapelGlIds = $mapel->gradeLevels->pluck('id')->toArray();
        @endphp
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
                            <label class="form-label">Tingkat / Kelas</label>
                            <div class="row g-2">
                                @forelse ($gradeLevels as $gl)
                                    <div class="col-6">
                                        <label class="form-check">
                                            <input type="checkbox" name="grade_level_ids[]" value="{{ $gl->id }}"
                                                class="form-check-input"
                                                {{ in_array($gl->id, old('grade_level_ids', $mapelGlIds)) ? 'checked' : '' }}>
                                            <span class="form-check-label">{{ $gl->name }}</span>
                                        </label>
                                    </div>
                                @empty
                                    <div class="col-12 text-secondary small">Belum ada tingkat. Buat tingkat dulu.</div>
                                @endforelse
                            </div>
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
