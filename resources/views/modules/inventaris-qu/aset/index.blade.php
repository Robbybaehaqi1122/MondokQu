<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Daftar Aset</h2>
                <div class="text-secondary mt-1">Kelola seluruh aset dan inventaris pondok.</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('inventaris.aset.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Tambah Aset
                </a>
            </div>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body border-bottom py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, kode, merk..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoris as $k)
                            <option value="{{ $k->id }}" {{ request('kategori') == $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="lokasi" class="form-select">
                        <option value="">Semua Lokasi</option>
                        @foreach ($lokasis as $l)
                            <option value="{{ $l->id }}" {{ request('lokasi') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="kondisi" class="form-select">
                        <option value="">Semua Kondisi</option>
                        @foreach ($kondisiList as $key => $label)
                            <option value="{{ $key }}" {{ request('kondisi') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">Filter</button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Aset</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Nilai</th>
                        <th>Kondisi</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asets as $aset)
                        <tr>
                            <td class="text-muted">{{ $aset->kode_aset }}</td>
                            <td>
                                <a href="{{ route('inventaris.aset.show', $aset) }}" class="text-reset fw-semibold">{{ $aset->name }}</a>
                            </td>
                            <td>{{ $aset->kategori->name ?? '-' }}</td>
                            <td>{{ $aset->lokasi->name ?? '-' }}</td>
                            <td>Rp {{ number_format($aset->harga_perolehan, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-{{ $aset->kondisi === 'baik' ? 'success' : ($aset->kondisi === 'hilang' ? 'warning' : 'danger') }}">
                                    {{ $kondisiList[$aset->kondisi] ?? $aset->kondisi }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('inventaris.aset.edit', $aset) }}" class="btn btn-icon btn-outline-primary btn-sm" title="Edit">
                                    <i class="ti ti-pencil"></i>
                                </a>
                                <form action="{{ route('inventaris.aset.destroy', $aset) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus aset ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-icon btn-outline-danger btn-sm" title="Hapus">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">Belum ada aset.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($asets instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="card-footer d-flex justify-content-center">
                {{ $asets->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
