<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">PerpustakaanQu</div>
                <h2 class="page-title mt-1">Katalog Kitab</h2>
            </div>
            <a href="{{ route('perpustakaan.kitab.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Tambah Kitab
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body border-bottom py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari judul, pengarang, ISBN..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoris as $k)
                            <option value="{{ $k->id }}" @selected(request('kategori') == $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="kondisi" class="form-select">
                        <option value="">Semua Kondisi</option>
                        <option value="baik" @selected(request('kondisi') === 'baik')>Baik</option>
                        <option value="rusak_ringan" @selected(request('kondisi') === 'rusak_ringan')>Rusak Ringan</option>
                        <option value="rusak_berat" @selected(request('kondisi') === 'rusak_berat')>Rusak Berat</option>
                        <option value="hilang" @selected(request('kondisi') === 'hilang')>Hilang</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">Filter</button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('perpustakaan.kitab.index') }}" class="btn btn-ghost-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Pengarang</th>
                        <th>Kategori</th>
                        <th>Eksemplar</th>
                        <th>Tersedia</th>
                        <th>Kondisi</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kitabs as $k)
                        <tr>
                            <td class="fw-semibold">
                                <a href="{{ route('perpustakaan.kitab.show', $k) }}" class="text-reset text-decoration-none">{{ $k->judul }}</a>
                            </td>
                            <td class="text-secondary">{{ $k->pengarang ?: '-' }}</td>
                            <td>{{ $k->kategori?->nama }}</td>
                            <td>{{ $k->jumlah_eksemplar }}</td>
                            <td>
                                <span class="badge {{ $k->tersedia > 0 ? 'bg-success' : 'bg-danger' }}">{{ $k->tersedia }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $k->kondisi === 'baik' ? 'bg-success' : ($k->kondisi === 'rusak_berat' || $k->kondisi === 'hilang' ? 'bg-danger' : 'bg-warning') }}">
                                    {{ $k->kondisi }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('perpustakaan.kitab.edit', $k) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('perpustakaan.kitab.destroy', $k) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kitab ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-secondary">Belum ada kitab.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($kitabs->hasPages())
            <div class="card-footer d-flex justify-content-center">{{ $kitabs->links() }}</div>
        @endif
    </div>
</x-app-layout>
