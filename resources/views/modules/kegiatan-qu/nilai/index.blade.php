<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">KegiatanQu</div>
                <h2 class="page-title mt-1">Nilai Kegiatan</h2>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNilai">
                <i class="ti ti-plus"></i> Tambah Nilai
            </button>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control" placeholder="Cari santri..." value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <select name="kegiatan_id" class="form-select">
                        <option value="">Semua Kegiatan</option>
                        @foreach ($kegiatans as $k)
                            <option value="{{ $k->id }}" @selected(request('kegiatan_id') == $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="aspek" class="form-select">
                        <option value="">Semua Aspek</option>
                        @foreach ($aspekList as $a)
                            <option value="{{ $a }}" @selected(request('aspek') === $a)>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Santri</th>
                        <th>Kegiatan</th>
                        <th>Aspek</th>
                        <th>Nilai</th>
                        <th>Catatan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($nilais as $n)
                        <tr>
                            <td class="fw-semibold">{{ $n->santri?->full_name ?? '-' }}</td>
                            <td>{{ $n->kegiatan?->nama ?? '-' }}</td>
                            <td><span class="badge bg-info">{{ $n->aspek }}</span></td>
                            <td>{{ $n->nilai ?? '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($n->catatan, 40) ?: '-' }}</td>
                            <td class="text-end">
                                <form action="{{ route('kegiatan.nilai.destroy', $n) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus nilai?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-secondary">Belum ada nilai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($nilais->hasPages())
            <div class="card-footer">{{ $nilais->links() }}</div>
        @endif
    </div>

    <div class="modal fade" id="modalNilai" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('kegiatan.nilai.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Nilai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Kegiatan</label>
                        <select name="kegiatan_id" class="form-select" required>
                            <option value="">Pilih Kegiatan</option>
                            @foreach ($kegiatans as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Santri</label>
                        <select name="santri_id" class="form-select" required>
                            <option value="">Pilih Santri</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Aspek</label>
                        <input type="text" name="aspek" class="form-control" placeholder="Misal: Kehadiran, Sikap, Praktek" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nilai (0-100)</label>
                        <input type="number" name="nilai" class="form-control" min="0" max="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
