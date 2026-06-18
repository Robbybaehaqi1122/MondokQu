<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">PpdbQu</div>
            <h2 class="page-title mt-1">Pendaftaran</h2>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control" placeholder="Cari nama / nomor pendaftaran..." value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <select name="gelombang_id" class="form-select">
                        <option value="">Semua Gelombang</option>
                        @foreach ($gelombangs as $g)
                            <option value="{{ $g->id }}" @selected(request('gelombang_id') == $g->id)>{{ $g->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                        <option value="diproses" @selected(request('status') === 'diproses')>Diproses</option>
                        <option value="diterima" @selected(request('status') === 'diterima')>Diterima</option>
                        <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
                        <option value="daftar_ulang" @selected(request('status') === 'daftar_ulang')>Daftar Ulang</option>
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
                        <th>No. Pendaftaran</th>
                        <th>Nama</th>
                        <th>Gelombang</th>
                        <th>No. HP</th>
                        <th>Status</th>
                        <th>Tgl Daftar</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendaftarans as $p)
                        <tr>
                            <td class="fw-semibold"><code>{{ $p->nomor_pendaftaran }}</code></td>
                            <td>
                                <a href="{{ route('ppdb.pendaftaran.show', $p) }}" class="text-reset text-decoration-none fw-semibold">
                                    {{ $p->nama_lengkap }}
                                </a>
                            </td>
                            <td>{{ $p->gelombang?->nama ?? '-' }}</td>
                            <td>{{ $p->no_hp }}</td>
                            <td>
                                <span class="badge {{ $p->status === 'diterima' || $p->status === 'daftar_ulang' ? 'bg-success' : ($p->status === 'ditolak' ? 'bg-danger' : 'bg-warning') }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                            <td>{{ $p->created_at->translatedFormat('d M Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('ppdb.pendaftaran.show', $p) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-secondary">Belum ada pendaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pendaftarans->hasPages())
            <div class="card-footer">{{ $pendaftarans->links() }}</div>
        @endif
    </div>
</x-app-layout>
