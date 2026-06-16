<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Sampah Pesan</h2>
                <div class="text-secondary mt-1">Pesan yang telah dihapus. Akan dihapus permanen setelah 30 hari.</div>
            </div>
            <a href="{{ route('komunikasi.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i>Kembali ke Inbox
            </a>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('komunikasi.trash') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari Pesan</label>
                    <input type="text" name="q" class="form-control" placeholder="Isi pesan..." value="{{ $filters['q'] }}">
                </div>
                <div class="col-md-1 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-filter"></i></button>
                    <a href="{{ route('komunikasi.trash') }}" class="btn btn-secondary"><i class="ti ti-refresh"></i></a>
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
                        <th>Pesan</th>
                        <th>Pengirim</th>
                        <th>Dihapus Pada</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($communications as $comm)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $comm->santri?->full_name ?? '-' }}</div>
                                <div class="text-secondary small">NIS {{ $comm->santri?->nis ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 300px;">{{ $comm->message }}</span>
                            </td>
                            <td>
                                @if ($comm->direction === 'incoming')
                                    <span class="badge bg-info-lt text-info">Pondok</span>
                                @else
                                    <span class="badge bg-success-lt text-success">Wali</span>
                                @endif
                                @if ($comm->user)
                                    <div class="text-secondary small mt-1">{{ $comm->user->name }}</div>
                                @endif
                            </td>
                            <td class="text-secondary">
                                {{ $comm->deleted_at->translatedFormat('d M Y H:i') }}
                                <div class="small">{{ $comm->deleted_at->diffForHumans() }}</div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <form method="POST" action="{{ route('komunikasi.restore', $comm) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Kembalikan">
                                            <i class="ti ti-refresh"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('komunikasi.force-delete', $comm) }}" class="d-inline" onsubmit="return confirm('Hapus permanen pesan ini? Tindakan ini tidak bisa dibatalkan.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Permanen">
                                            <i class="ti ti-trash-x"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-secondary text-center py-5">Sampah kosong.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($communications->hasPages())
            <div class="card-footer">{{ $communications->links() }}</div>
        @endif
    </div>
</x-app-layout>
