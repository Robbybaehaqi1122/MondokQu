<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Komunikasi Wali Santri</h2>
                <div class="text-secondary mt-1">Pesan dari wali santri.</div>
            </div>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('komunikasi.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari Santri</label>
                    <input type="text" name="q" class="form-control" placeholder="Nama santri..." value="{{ $filters['q'] }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        @forelse ($santris as $santri)
            @php
                $latestMessage = $latestMessages->get($santri->id);
                $unreadCount = $unreadCounts->get($santri->id, 0);
            @endphp
            <a href="{{ route('komunikasi.show', $santri) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-bottom px-4 py-3 text-reset text-decoration-none">
                <span class="avatar avatar-sm bg-primary-lt text-primary">
                    {{ strtoupper(substr($santri->full_name, 0, 1)) }}
                </span>
                <div class="flex-grow-1 min-width-0">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-semibold">{{ $santri->full_name }}</span>
                        <span class="text-secondary small">NIS {{ $santri->nis }}</span>
                        @if ($unreadCount > 0)
                            <span class="badge bg-red ms-auto">{{ $unreadCount }} belum dibaca</span>
                        @endif
                    </div>
                    @if ($latestMessage)
                        <div class="text-secondary small text-truncate mt-1">
                            @if ($latestMessage->direction === 'outgoing')
                                <i class="ti ti-arrow-up-right text-success"></i>
                            @else
                                <i class="ti ti-arrow-down-left text-info"></i>
                            @endif
                            {{ $latestMessage->message }}
                            @if ($latestMessage->is_replied)
                                <span class="badge bg-green-lt text-green ms-1"><i class="ti ti-message-reply"></i></span>
                            @endif
                            @if ($latestMessage->forwarded_from_id)
                                <span class="badge bg-warning-lt text-warning ms-1"><i class="ti ti-arrow-forward"></i></span>
                            @endif
                            <span class="text-secondary ms-2">{{ $latestMessage->created_at->diffForHumans() }}</span>
                        </div>
                    @endif
                </div>
            </a>
        @empty
            <div class="card-body text-secondary text-center py-5">
                Belum ada komunikasi dari wali santri.
            </div>
        @endforelse
    </div>
</x-app-layout>
