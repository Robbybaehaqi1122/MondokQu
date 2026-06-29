<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Catatan Pelanggaran</h2>
                <div class="text-secondary mt-1">Daftar pelanggaran santri.</div>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPelanggaranModal">
                <i class="ti ti-plus me-1"></i>
                Catat Pelanggaran
            </button>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('pelanggaran.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Cari Santri</label>
                    <input type="text" name="q" class="form-control" placeholder="Nama atau NIS..." value="{{ $filters['q'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Santri</label>
                    <select name="santri" class="form-select">
                        <option value="">Semua Santri</option>
                        @foreach ($santriOptions as $s)
                            <option value="{{ $s->id }}" @selected($filters['santri'] == $s->id)>{{ $s->full_name }} ({{ $s->nis }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value="">Semua</option>
                        @foreach ($kategoriOptions as $k)
                            <option value="{{ $k->id }}" @selected($filters['kategori'] == $k->id)>{{ $k->nama }} ({{ $k->poin }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Sampai</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Santri</th>
                        <th>Kategori</th>
                        <th>Poin</th>
                        <th>Keterangan</th>
                        <th>Tanggal</th>
                        <th>Pencatat</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pelanggarans as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item->santri?->full_name ?? '-' }}</div>
                                <div class="text-secondary small">NIS {{ $item->santri?->nis ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-danger-lt text-danger">{{ $item->kategori?->nama ?? '-' }}</span>
                            </td>
                            <td class="fw-semibold">{{ number_format($item->poin) }}</td>
                            <td class="text-secondary">{{ $item->keterangan ?? '-' }}</td>
                            <td>{{ $item->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="text-secondary small">{{ $item->pencatat?->name ?? '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('pelanggaran.destroy', $item) }}" onsubmit="return confirm('Yakin ingin menghapus pelanggaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm btn-icon" aria-label="Hapus">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-secondary">Belum ada catatan pelanggaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pelanggarans->hasPages())
            <div class="card-footer">{{ $pelanggarans->links() }}</div>
        @endif
    </div>

    <div class="modal modal-blur fade" id="createPelanggaranModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('pelanggaran.store') }}">
                    @csrf

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Catat Pelanggaran</h5>
                            <div class="text-secondary small mt-1">Catat pelanggaran yang dilakukan santri.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <div class="fw-semibold mb-2">Pelanggaran belum bisa disimpan.</div>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="santri_id" class="form-label">Santri <span class="text-danger">*</span></label>
                                <select id="santri_id" name="santri_id" class="form-select @error('santri_id') is-invalid @enderror" required>
                                    <option value="">Pilih Santri</option>
                                    @foreach ($santriOptions as $santri)
                                        <option value="{{ $santri->id }}" @selected(old('santri_id') == $santri->id)>
                                            {{ $santri->full_name }} (NIS {{ $santri->nis }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('santri_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="kategori_id" class="form-label">Kategori Pelanggaran <span class="text-danger">*</span></label>
                                <select id="kategori_id" name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($kategoriOptions as $kategori)
                                        <option value="{{ $kategori->id }}" @selected(old('kategori_id') == $kategori->id)>
                                            {{ $kategori->nama }} ({{ $kategori->poin }} poin)
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="tanggal" class="form-label">Tanggal Pelanggaran <span class="text-danger">*</span></label>
                                <input type="date" id="tanggal" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                                    value="{{ old('tanggal', now()->toDateString()) }}" required>
                                @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <textarea id="keterangan" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3" placeholder="Deskripsi pelanggaran...">{{ old('keterangan') }}</textarea>
                                @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan Pelanggaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        @if (old('santri_id') || $errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                const modal = new window.bootstrap.Modal('#createPelanggaranModal');
                modal.show();
            });
        @endif
    </script>
    @endpush
</x-app-layout>
