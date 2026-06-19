<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h2 class="page-title mt-1">Katalog Kitab</h2>
                <div class="text-secondary small">Daftar kitab yang digunakan untuk hafalan.</div>
            </div>
            <a href="{{ route('kitab.kitab.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Tambah Kitab
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau pengarang..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoris as $k)
                            <option value="{{ $k->id }}" @selected(request('kategori') == $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                @if (request()->anyFilled(['search', 'kategori']))
                    <div class="col-12">
                        <a href="{{ route('kitab.kitab.index') }}" class="btn btn-ghost-secondary w-100">Reset</a>
                    </div>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama Kitab</th>
                        <th>Pengarang</th>
                        <th>Kategori</th>
                        <th>Total Setoran</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kitabs as $kitab)
                        <tr>
                            <td>
                                <a href="{{ route('kitab.kitab.show', $kitab) }}" class="text-reset text-decoration-none fw-semibold">{{ $kitab->nama }}</a>
                            </td>
                            <td class="text-secondary">{{ $kitab->pengarang ?: '-' }}</td>
                            <td><span class="badge bg-muted-lt">{{ $kitab->kategori?->nama ?? '-' }}</span></td>
                            <td>{{ number_format($kitab->setorans_count) }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('kitab.kitab.edit', $kitab) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="POST" action="{{ route('kitab.kitab.destroy', $kitab) }}" onsubmit="return confirm('Hapus kitab ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-secondary">Belum ada kitab.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($kitabs->hasPages())
            <div class="card-footer d-flex justify-content-center">{{ $kitabs->links() }}</div>
        @endif
    </div>
</x-app-layout>
