<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Notifikasi</h2>
                <div class="text-secondary mt-1">Pantau informasi sistem dan hasil export yang sudah siap diunduh.</div>
            </div>

            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="ti ti-checks me-1"></i>
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="card">
        <div class="list-group list-group-flush">
            @forelse ($notifications as $notification)
                @php
                    $title = data_get($notification->data, 'title', 'Notifikasi');
                    $message = data_get($notification->data, 'message', 'Ada informasi baru untuk akun Anda.');
                    $icon = data_get($notification->data, 'icon', 'ti-bell');
                    $rowCount = data_get($notification->data, 'row_count');
                @endphp

                <a href="{{ route('notifications.show', $notification) }}" class="list-group-item list-group-item-action">
                    <div class="d-flex gap-3">
                        <span class="avatar {{ is_null($notification->read_at) ? 'bg-primary-lt text-primary' : 'bg-secondary-lt text-secondary' }}">
                            <i class="ti {{ $icon }}"></i>
                        </span>
                        <div class="min-width-0 flex-fill">
                            <div class="d-flex flex-column flex-lg-row justify-content-lg-between gap-1">
                                <div class="fw-semibold">{{ $title }}</div>
                                <div class="text-secondary small">{{ $notification->created_at->translatedFormat('d M Y H:i') }}</div>
                            </div>
                            <div class="text-secondary mt-1">{{ $message }}</div>
                            @if (! is_null($rowCount))
                                <div class="small text-secondary mt-1">{{ number_format((int) $rowCount) }} baris data</div>
                            @endif
                        </div>
                        @if (is_null($notification->read_at))
                            <span class="badge bg-primary align-self-start">Baru</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="empty">
                    <div class="empty-icon">
                        <i class="ti ti-bell-off"></i>
                    </div>
                    <p class="empty-title">Belum ada notifikasi</p>
                    <p class="empty-subtitle text-secondary">Notifikasi export selesai akan muncul di sini.</p>
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="card-footer">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
