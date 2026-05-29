<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Catatan Pelanggaran</h2>
                <div class="text-secondary mt-1">Daftar pelanggaran santri.</div>
            </div>
            <a href="{{ route('pelanggaran.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>
                Catat Pelanggaran
            </a>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('pelanggaran.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Cari Santri</label>
                    <input type="text" name="q" class="form-control" placeholder="Nama atau NIS..." value="{{ $filters['q'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Santri</label>
                    <select name="santri" class="form-select">
                        <option value="">Semua Santri</option>
                        @foreach ($santriOptions as $s)
                            <option value="{{ $s->id }}" @selected($filters['santri'] == $s->id)>{{ $s->full_name }} ({{ $s->nis }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value="">Semua</option>
                        @foreach ($kategoriOptions as $k)
                            <option value="{{ $k->id }}" @selected($filters['kategori'] == $k->id)>{{ $k->nama }} ({{ $k->poin }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Sampai</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Santri</th>
                        <th>Kategori</th>
                        <th>Poin</th>
                        <th>Keterangan</th>
                        <th>Tanggal</th>
                        <th>Pencatat</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pelanggarans as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item->santri?->full_name ?? '-' }}</div>
                                <div class="text-secondary small">NIS {{ $item->santri?->nis ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-danger-lt text-danger">{{ $item->kategori?->nama ?? '-' }}</span>
                            </td>
                            <td class="fw-semibold">{{ number_format($item->poin) }}</td>
                            <td class="text-secondary">{{ $item->keterangan ?? '-' }}</td>
                            <td>{{ $item->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="text-secondary small">{{ $item->pencatat?->name ?? '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('pelanggaran.destroy', $item) }}" onsubmit="return confirm('Yakin ingin menghapus pelanggaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm btn-icon" aria-label="Hapus">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-secondary">Belum ada catatan pelanggaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pelanggarans->hasPages())
            <div class="card-footer">{{ $pelanggarans->links() }}</div>
        @endif
    </div>
</x-app-layout>
