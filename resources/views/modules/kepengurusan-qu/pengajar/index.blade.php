<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h2 class="page-title mt-1">Data Pengajar</h2>
                <div class="text-secondary small">Daftar guru dan ustadz pondok.</div>
            </div>
            <a href="{{ route('kepengurusan.pengajar.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Tambah Pengajar
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, NIP, atau bidang keahlian..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                @if (request()->anyFilled(['search']))
                    <div class="col-12">
                        <a href="{{ route('kepengurusan.pengajar.index') }}" class="btn btn-ghost-secondary">Reset</a>
                    </div>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Bidang Keahlian</th>
                        <th>No. Telp</th>
                        <th>Status</th>
                        <th>Total Jadwal</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengajars as $pengajar)
                        <tr>
                            <td>
                                <a href="{{ route('kepengurusan.pengajar.show', $pengajar) }}" class="text-reset text-decoration-none fw-semibold">{{ $pengajar->nama }}</a>
                            </td>
                            <td class="text-secondary">{{ $pengajar->nip ?: '-' }}</td>
                            <td>{{ $pengajar->bidang_keahlian ?: '-' }}</td>
                            <td>{{ $pengajar->no_telp ?: '-' }}</td>
                            <td>
                                <span class="badge {{ $pengajar->status ? 'bg-success' : 'bg-danger' }}">
                                    {{ $pengajar->status ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>{{ number_format($pengajar->jadwals_count) }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('kepengurusan.pengajar.edit', $pengajar) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="POST" action="{{ route('kepengurusan.pengajar.destroy', $pengajar) }}" onsubmit="return confirm('Hapus pengajar ini? Semua jadwal terkait juga akan dihapus.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-secondary">Belum ada pengajar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pengajars->hasPages())
            <div class="card-footer d-flex justify-content-center">{{ $pengajars->links() }}</div>
        @endif
    </div>
</x-app-layout>
