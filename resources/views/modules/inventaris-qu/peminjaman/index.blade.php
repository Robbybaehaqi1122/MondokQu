<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Peminjaman Aset</h2>
                <div class="text-secondary mt-1">Catat dan kelola peminjaman aset pondok.</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('inventaris.peminjaman.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Pinjam Aset
                </a>
            </div>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body border-bottom py-2">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari peminjam..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">Cari</button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Aset</th>
                        <th>Peminjam</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                        <th>Status</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($peminjaman as $p)
                        <tr>
                            <td>
                                <a href="{{ route('inventaris.aset.show', $p->aset) }}" class="text-reset fw-semibold">
                                    {{ $p->aset->name ?? '-' }}
                                </a>
                                <div class="text-secondary small">{{ $p->aset->kode_aset ?? '' }}</div>
                            </td>
                            <td>{{ $p->peminjam }}
                                @if ($p->role_peminjam)
                                    <div class="text-secondary small">{{ $p->role_peminjam }}</div>
                                @endif
                            </td>
                            <td>{{ $p->tanggal_pinjam->format('d M Y') }}</td>
                            <td>{{ $p->tanggal_kembali?->format('d M Y') ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $p->status === 'dipinjam' ? 'warning' : 'success' }}">
                                    {{ $p->status === 'dipinjam' ? 'Dipinjam' : 'Dikembalikan' }}
                                </span>
                            </td>
                            <td>
                                @if ($p->status === 'dipinjam')
                                    <form action="{{ route('inventaris.peminjaman.kembali', $p) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-icon btn-outline-success btn-sm" title="Kembalikan">
                                            <i class="ti ti-check"></i>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('inventaris.peminjaman.destroy', $p) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus riwayat ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-icon btn-outline-danger btn-sm" title="Hapus">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada peminjaman.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($peminjaman instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="card-footer d-flex justify-content-center">{{ $peminjaman->links() }}</div>
        @endif
    </div>
</x-app-layout>
