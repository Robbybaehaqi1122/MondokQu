<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">SaaS</div>
            <h2 class="page-title mt-1">Riwayat Subscription</h2>
            <div class="text-secondary mt-2">Catatan perubahan trial, subscription, grace, dan expired untuk seluruh tenant.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Log Subscription Tenant</h3>
                        <p class="text-secondary mb-0">Menampilkan tenant mana yang berubah, kapan berubah, catatan admin, dan siapa yang melakukan perubahan.</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Tenant</th>
                                <th>Waktu</th>
                                <th>Aksi</th>
                                <th>Periode</th>
                                <th>Catatan Admin</th>
                                <th>Diubah Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($histories as $history)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $history->tenant?->name ?? 'Tenant tidak ditemukan' }}</div>
                                        <div class="text-secondary small">{{ $history->tenant?->slug ?? '-' }}</div>
                                    </td>
                                    <td class="text-secondary small">{{ $history->created_at?->translatedFormat('d M Y H:i:s') ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-azure-lt text-azure">{{ str($history->action)->headline() }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $history->period_starts_at?->translatedFormat('d M Y H:i') ?? '-' }}</div>
                                        <div class="text-secondary small mt-1">sampai {{ $history->period_ends_at?->translatedFormat('d M Y H:i') ?? '-' }}</div>
                                    </td>
                                    <td class="text-secondary small">{{ $history->admin_note ?: 'Tanpa catatan admin' }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $history->changedByUser?->name ?? 'Sistem' }}</div>
                                        <div class="text-secondary small">{{ $history->changedByUser?->username ? '@'.$history->changedByUser->username : 'Tanpa akun login' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-secondary">Belum ada riwayat subscription yang tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($histories->hasPages())
                    <div class="card-footer">
                        {{ $histories->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
