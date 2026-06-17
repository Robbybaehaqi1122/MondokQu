<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">KegiatanQu</div>
                <h2 class="page-title mt-1">Pendaftaran Peserta</h2>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDaftar">
                <i class="ti ti-plus"></i> Daftarkan Santri
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
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="terdaftar" @selected(request('status') === 'terdaftar')>Terdaftar</option>
                        <option value="terkonfirmasi" @selected(request('status') === 'terkonfirmasi')>Terkonfirmasi</option>
                        <option value="dibatalkan" @selected(request('status') === 'dibatalkan')>Dibatalkan</option>
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
                        <th>Status</th>
                        <th>Tgl Daftar</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendaftarans as $p)
                        <tr>
                            <td class="fw-semibold">{{ $p->santri?->full_name ?? '-' }}</td>
                            <td>{{ $p->kegiatan?->nama ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $p->status === 'terkonfirmasi' ? 'bg-success' : ($p->status === 'dibatalkan' ? 'bg-danger' : 'bg-warning') }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                            <td>{{ $p->created_at->translatedFormat('d M Y') }}</td>
                            <td class="text-end">
                                @if ($p->status === 'terdaftar')
                                    <form action="{{ route('kegiatan.pendaftaran.update', $p) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="terkonfirmasi">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Konfirmasi</button>
                                    </form>
                                @endif
                                @if ($p->status !== 'dibatalkan')
                                    <form action="{{ route('kegiatan.pendaftaran.update', $p) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="dibatalkan">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Batalkan pendaftaran ini?')">Batalkan</button>
                                    </form>
                                @endif
                                <form action="{{ route('kegiatan.pendaftaran.destroy', $p) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Hapus pendaftaran?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-secondary">Belum ada pendaftaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pendaftarans->hasPages())
            <div class="card-footer">{{ $pendaftarans->links() }}</div>
        @endif
    </div>

    <div class="modal fade" id="modalDaftar" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('kegiatan.pendaftaran.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Daftarkan Santri</h5>
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
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Daftarkan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
