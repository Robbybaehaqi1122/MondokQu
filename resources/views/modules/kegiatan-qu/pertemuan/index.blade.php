<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">KegiatanQu</div>
                <h2 class="page-title mt-1">Pertemuan Kegiatan</h2>
            </div>
            <a href="{{ route('kegiatan.pertemuan.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Tambah Pertemuan
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control" placeholder="Cari materi..." value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <select name="kegiatan_id" class="form-select">
                        <option value="">Semua Kegiatan</option>
                        @foreach ($kegiatans as $k)
                            <option value="{{ $k->id }}" @selected(request('kegiatan_id') == $k->id)>{{ $k->nama }}</option>
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
                        <th>Tanggal</th>
                        <th>Kegiatan</th>
                        <th>Jam</th>
                        <th>Materi</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pertemuans as $p)
                        <tr>
                            <td>{{ $p->tanggal->translatedFormat('d M Y') }}</td>
                            <td class="fw-semibold">{{ $p->kegiatan?->nama ?? '-' }}</td>
                            <td>{{ $p->jam_mulai ? $p->jam_mulai . ' - ' . ($p->jam_selesai ?? 'selesai') : '-' }}</td>
                            <td>{{ $p->materi ?: '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('kegiatan.pertemuan.show', $p) }}" class="btn btn-sm btn-outline-primary">Presensi</a>
                                <a href="{{ route('kegiatan.pertemuan.edit', $p) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('kegiatan.pertemuan.destroy', $p) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus pertemuan?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-secondary">Belum ada pertemuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pertemuans->hasPages())
            <div class="card-footer">{{ $pertemuans->links() }}</div>
        @endif
    </div>
</x-app-layout>
