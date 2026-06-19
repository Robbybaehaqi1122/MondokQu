<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h2 class="page-title mt-1">Data Pengurus</h2>
                <div class="text-secondary small">Daftar pengurus pondok pesantren.</div>
            </div>
            <a href="{{ route('kepengurusan.pengurus.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Tambah Pengurus
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau jabatan..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                @if (request()->anyFilled(['search']))
                    <div class="col-12">
                        <a href="{{ route('kepengurusan.pengurus.index') }}" class="btn btn-ghost-secondary">Reset</a>
                    </div>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>No. Telp</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($penguruses as $pengurus)
                        <tr>
                            <td>
                                <a href="{{ route('kepengurusan.pengurus.show', $pengurus) }}" class="text-reset text-decoration-none fw-semibold">{{ $pengurus->nama }}</a>
                            </td>
                            <td>{{ $pengurus->jabatan ?: '-' }}</td>
                            <td>{{ $pengurus->no_telp ?: '-' }}</td>
                            <td>
                                <span class="badge {{ $pengurus->status ? 'bg-success' : 'bg-danger' }}">
                                    {{ $pengurus->status ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('kepengurusan.pengurus.edit', $pengurus) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="POST" action="{{ route('kepengurusan.pengurus.destroy', $pengurus) }}" onsubmit="return confirm('Hapus pengurus ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-secondary">Belum ada pengurus.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($penguruses->hasPages())
            <div class="card-footer d-flex justify-content-center">{{ $penguruses->links() }}</div>
        @endif
    </div>
</x-app-layout>
