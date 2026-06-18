<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">PpdbQu</div>
                <h2 class="page-title mt-1">Gelombang Pendaftaran</h2>
            </div>
            <a href="{{ route('ppdb.gelombang.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Tambah Gelombang
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Periode</th>
                        <th>Kuota</th>
                        <th>Biaya</th>
                        <th>Pendaftar</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($gelombangs as $g)
                        <tr>
                            <td class="fw-semibold">{{ $g->nama }}</td>
                            <td>{{ $g->tanggal_mulai->translatedFormat('d M Y') }} - {{ $g->tanggal_selesai->translatedFormat('d M Y') }}</td>
                            <td>{{ $g->kuota ?: 'Tak terbatas' }}</td>
                            <td>Rp {{ number_format($g->biaya_pendaftaran, 0) }}</td>
                            <td><span class="badge bg-primary">{{ number_format($g->pendaftarans_count) }}</span></td>
                            <td>
                                <span class="badge {{ $g->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">{{ $g->status }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('ppdb.gelombang.edit', $g) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-secondary">Belum ada gelombang pendaftaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($gelombangs->hasPages())
            <div class="card-footer">{{ $gelombangs->links() }}</div>
        @endif
    </div>
</x-app-layout>
