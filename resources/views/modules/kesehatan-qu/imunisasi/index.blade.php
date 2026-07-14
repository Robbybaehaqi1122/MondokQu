<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KesehatanQu</div>
            <h2 class="page-title mt-1">Imunisasi</h2>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#imunisasiModal">Catat Imunisasi</button>
    </x-slot>

    <div class="row mb-3">
        <div class="col-lg-6">
            <form method="GET" action="{{ route('kesehatan.imunisasi.index') }}" class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="q" class="form-control" placeholder="Cari santri atau jenis imunisasi..." value="{{ $filters['q'] }}">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="belum" @selected($filters['status'] === 'belum')>Belum</option>
                        <option value="sudah" @selected($filters['status'] === 'sudah')>Sudah</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                @if ($filters['q'] || $filters['status'])
                    <div class="col-12">
                        <a href="{{ route('kesehatan.imunisasi.index') }}" class="btn btn-outline-secondary btn-sm">Reset Filter</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-mobile-md">
                <thead>
                    <tr>
                        <th>Santri</th>
                        <th>Jenis Imunisasi</th>
                        <th class="d-none d-md-table-cell">Tanggal</th>
                        <th>Status</th>
                        <th class="d-none d-md-table-cell">Diberikan Oleh</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($imunisasis as $imunisasi)
                        <tr>
                            <td class="fw-semibold">{{ $imunisasi->santri?->full_name ?? 'Unknown' }}</td>
                            <td>{{ $imunisasi->jenis_imunisasi }}</td>
                            <td class="d-none d-md-table-cell">{{ $imunisasi->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $imunisasi->status === 'sudah' ? 'bg-success' : 'bg-warning' }}">
                                    {{ ucfirst($imunisasi->status) }}
                                </span>
                            </td>
                            <td class="d-none d-md-table-cell">{{ $imunisasi->pemberi?->name ?? '-' }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#imunisasiModal"
                                    data-id="{{ $imunisasi->id }}"
                                    data-santri_id="{{ $imunisasi->santri_id }}"
                                    data-jenis_imunisasi="{{ $imunisasi->jenis_imunisasi }}"
                                    data-tanggal="{{ $imunisasi->tanggal?->toDateString() }}"
                                    data-status="{{ $imunisasi->status }}"
                                    data-catatan="{{ $imunisasi->catatan }}"
                                    data-diberikan_oleh="{{ $imunisasi->diberikan_oleh }}">Edit</button>
                                <form method="POST" action="{{ route('kesehatan.imunisasi.destroy', $imunisasi) }}" class="d-inline" onsubmit="return confirm('Hapus imunisasi {{ $imunisasi->jenis_imunisasi }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-secondary">Belum ada data imunisasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($imunisasis->hasPages())
            <div class="card-footer">
                {{ $imunisasis->links() }}
            </div>
        @endif
    </div>

    <div class="modal fade" id="imunisasiModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('kesehatan.imunisasi.store') }}" class="modal-content" id="imunisasiForm">
                @csrf
                <input type="hidden" name="_method" value="POST" id="imunisasiFormMethod">
                <input type="hidden" name="imunisasi_id" id="imunisasi_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="imunisasiModalTitle">Catat Imunisasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="santri_id" class="form-label">Santri <span class="text-danger">*</span></label>
                        <select id="santri_id" name="santri_id" class="form-select @error('santri_id') is-invalid @enderror" required>
                            <option value="">Pilih Santri</option>
                            @foreach ($santriOptions as $santri)
                                <option value="{{ $santri->id }}">{{ $santri->full_name }} (NIS {{ $santri->nis }})</option>
                            @endforeach
                        </select>
                        @error('santri_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="jenis_imunisasi" class="form-label">Jenis Imunisasi <span class="text-danger">*</span></label>
                        <input type="text" id="jenis_imunisasi" name="jenis_imunisasi" class="form-control @error('jenis_imunisasi') is-invalid @enderror" value="{{ old('jenis_imunisasi') }}" placeholder="Misal: BCG, Polio, Campak" required>
                        @error('jenis_imunisasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', now()->toDateString()) }}" required>
                            @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="belum">Belum</option>
                                <option value="sudah">Sudah</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="diberikan_oleh" class="form-label">Diberikan Oleh</label>
                        <select id="diberikan_oleh" name="diberikan_oleh" class="form-select">
                            <option value="">Pilih Petugas (opsional)</option>
                            @foreach ($petugasOptions as $petugas)
                                <option value="{{ $petugas->id }}">{{ $petugas->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="catatan_imunisasi" class="form-label">Catatan</label>
                        <textarea id="catatan_imunisasi" name="catatan" class="form-control" rows="2">{{ old('catatan') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="imunisasiModalSubmit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    document.getElementById('imunisasiModal')?.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        const isEdit = btn?.dataset?.id;
        const form = document.getElementById('imunisasiForm');
        const title = document.getElementById('imunisasiModalTitle');
        const submit = document.getElementById('imunisasiModalSubmit');

        if (isEdit) {
            title.textContent = 'Edit Imunisasi';
            submit.textContent = 'Update';
            form.action = '{{ route("kesehatan.imunisasi.index") }}/' + btn.dataset.id;
            document.getElementById('imunisasiFormMethod').value = 'PATCH';
            document.getElementById('imunisasi_id').value = btn.dataset.id;
            document.getElementById('santri_id').value = btn.dataset.santri_id;
            document.getElementById('jenis_imunisasi').value = btn.dataset.jenis_imunisasi;
            document.getElementById('tanggal').value = btn.dataset.tanggal;
            document.getElementById('status').value = btn.dataset.status;
            document.getElementById('diberikan_oleh').value = btn.dataset.diberikan_oleh;
            document.getElementById('catatan_imunisasi').value = btn.dataset.catatan;
        } else {
            title.textContent = 'Catat Imunisasi';
            submit.textContent = 'Simpan';
            form.action = '{{ route("kesehatan.imunisasi.store") }}';
            document.getElementById('imunisasiFormMethod').value = 'POST';
            form.reset();
            document.getElementById('imunisasi_id').value = '';
            document.getElementById('tanggal').value = '{{ now()->toDateString() }}';
        }
    });
</script>
@endpush
