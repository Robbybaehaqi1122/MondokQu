<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">KegiatanQu</div>
                <h2 class="page-title mt-1">Master Kegiatan</h2>
            </div>
            <a href="{{ route('kegiatan.kegiatan.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Tambah Kegiatan
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" placeholder="Cari kegiatan..." value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Pembina</th>
                        <th>Tempat</th>
                        <th>Kuota</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kegiatans as $k)
                        <tr>
                            <td>
                                <a href="{{ route('kegiatan.kegiatan.show', $k) }}" class="text-reset text-decoration-none fw-semibold">
                                    {{ $k->nama }}
                                </a>
                            </td>
                            <td>{{ $k->pembina?->name ?? '-' }}</td>
                            <td>{{ $k->tempat ?: '-' }}</td>
                            <td>{{ $k->kuota ?: 'Tak terbatas' }}</td>
                            <td>
                                <span class="badge {{ $k->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $k->status }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('kegiatan.kegiatan.edit', $k) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-secondary">Belum ada kegiatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($kegiatans->hasPages())
            <div class="card-footer">
                {{ $kegiatans->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
