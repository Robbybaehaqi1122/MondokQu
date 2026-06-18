<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">PerpustakaanQu</div>
                <h2 class="page-title mt-1">Peminjaman Kitab</h2>
            </div>
            <a href="{{ route('perpustakaan.peminjaman.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Tambah Peminjaman
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body border-bottom py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="dipinjam" @selected(request('status') === 'dipinjam')>Dipinjam</option>
                        <option value="dikembalikan" @selected(request('status') === 'dikembalikan')>Dikembalikan</option>
                        <option value="terlambat" @selected(request('status') === 'terlambat')>Terlambat</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">Filter</button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-ghost-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kitab</th>
                        <th>Santri</th>
                        <th>Tgl Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Tgl Kembali</th>
                        <th>Denda</th>
                        <th>Status</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($peminjamans as $p)
                        <tr>
                            <td class="fw-semibold">{{ $p->kitab?->judul }}</td>
                            <td>{{ $p->santri?->full_name }}</td>
                            <td>{{ $p->tanggal_pinjam->translatedFormat('d M Y') }}</td>
                            <td>{{ $p->tanggal_jatuh_tempo->translatedFormat('d M Y') }}</td>
                            <td>{{ $p->tanggal_kembali?->translatedFormat('d M Y') ?: '-' }}</td>
                            <td>Rp {{ number_format($p->denda, 0) }}</td>
                            <td>
                                <span class="badge {{ $p->status === 'dipinjam' ? 'bg-warning' : ($p->status === 'terlambat' ? 'bg-danger' : 'bg-success') }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if ($p->status === 'dipinjam')
                                    <form action="{{ route('perpustakaan.peminjaman.kembalikan', $p) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Kembalikan kitab ini?')">Kembalikan</button>
                                    </form>
                                @endif
                                <form action="{{ route('perpustakaan.peminjaman.destroy', $p) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus record?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-secondary">Belum ada peminjaman.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($peminjamans->hasPages())
            <div class="card-footer d-flex justify-content-center">{{ $peminjamans->links() }}</div>
        @endif
    </div>
</x-app-layout>
