<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Komunikasi dengan Pondok</h2>
                <div class="text-secondary mt-1">Riwayat pesan dengan pihak pondok.</div>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        @foreach ($santris as $s)
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        <span class="avatar avatar-lg mb-2 bg-primary-lt text-primary">
                            {{ strtoupper(substr($s->full_name, 0, 1)) }}
                        </span>
                        <div class="fw-semibold">{{ $s->full_name }}</div>
                        <div class="text-secondary small">NIS {{ $s->nis }}</div>
                        <a href="{{ route('wali-santri.komunikasi.show', $s) }}" class="btn btn-outline-primary btn-sm mt-2">
                            <i class="ti ti-message me-1"></i>
                            Lihat Pesan
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat Pesan</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Santri</th>
                        <th>Pesan</th>
                        <th>Dari</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($communications as $comm)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $comm->santri?->full_name ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 300px;">{{ $comm->message }}</span>
                                @if ($comm->is_replied)
                                    <span class="badge bg-green-lt text-green ms-1"><i class="ti ti-message-reply"></i></span>
                                @endif
                                @if ($comm->forwarded_from_id)
                                    <span class="badge bg-warning-lt text-warning ms-1"><i class="ti ti-arrow-forward"></i></span>
                                @endif
                            </td>
                            <td>
                                @if ($comm->direction === 'incoming')
                                    <span class="badge bg-info-lt text-info">Pondok</span>
                                @else
                                    <span class="badge bg-success-lt text-success">Wali</span>
                                @endif
                                @if ($comm->direction === 'outgoing' && $comm->is_read)
                                    <span class="badge bg-blue-lt text-blue ms-1"><i class="ti ti-eye"></i></span>
                                @endif
                            </td>
                            <td class="text-secondary">{{ $comm->created_at->translatedFormat('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-secondary">Belum ada pesan.</td>
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
