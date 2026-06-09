<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Komunikasi dengan {{ $santri->full_name }}</h2>
                <div class="text-secondary mt-1">NIS {{ $santri->nis }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('komunikasi.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body" style="max-height: 500px; overflow-y: auto;" id="messageThread">
            @forelse ($communications as $comm)
                @if (!$comm->parent_id)
                    <div class="mb-3">
                        <div class="d-flex {{ $comm->direction === 'incoming' ? 'justify-content-start' : 'justify-content-end' }}">
                            <div class="p-3 rounded-2 {{ $comm->direction === 'incoming' ? 'bg-info-lt text-info' : 'bg-primary-lt text-primary' }}" style="max-width: 75%;">
                                <div class="fw-semibold small mb-1 d-flex align-items-center gap-2">
                                    <span>{{ $comm->direction === 'incoming' ? 'Pondok' : $comm->user?->name ?? 'Wali Santri' }}</span>
                                    @if ($comm->forwardedFrom)
                                        <span class="badge bg-warning-lt text-warning"><i class="ti ti-arrow-forward"></i> Forward</span>
                                    @endif
                                    <span class="ms-auto small">
                                        @if ($comm->direction === 'outgoing' && $comm->is_read && $comm->is_replied)
                                            <span class="badge bg-green-lt text-green" title="Sudah dibalas"><i class="ti ti-message-reply"></i> Dibalas</span>
                                        @elseif ($comm->direction === 'outgoing' && $comm->is_read)
                                            <span class="badge bg-blue-lt text-blue" title="Sudah terbaca"><i class="ti ti-eye"></i> Terbaca</span>
                                        @elseif ($comm->direction === 'outgoing')
                                            <span class="badge bg-secondary-lt text-secondary" title="Terkirim"><i class="ti ti-check"></i> Terkirim</span>
                                        @endif
                                    </span>
                                </div>
                                @if ($comm->forwardedFrom)
                                    <div class="small opacity-75 mb-1">
                                        <i class="ti ti-arrow-forward"></i>
                                        Diteruskan dari {{ $comm->forwardedFrom->santri?->full_name ?? 'percakapan lain' }}
                                    </div>
                                @endif
                                <p class="mb-1">{{ $comm->message }}</p>
                                <div class="small opacity-75 text-end d-flex align-items-center justify-content-between">
                                    <span>{{ $comm->created_at->translatedFormat('d M Y H:i') }}</span>
                                    <span class="d-flex gap-1">
                                        @if ($comm->direction === 'incoming')
                                            <a href="#" class="text-reset text-decoration-none reply-btn" data-message-id="{{ $comm->id }}" data-message="{{ Str::limit($comm->message, 50) }}">
                                                <i class="ti ti-reply"></i>
                                            </a>
                                            <div class="dropdown d-inline-block">
                                                <a href="#" class="text-reset text-decoration-none" data-bs-toggle="dropdown">
                                                    <i class="ti ti-arrow-forward"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <form method="POST" action="{{ route('komunikasi.forward', ['santri' => $santri, 'communication' => $comm]) }}" class="px-3 py-2" style="min-width: 250px;">
                                                        @csrf
                                                        <label class="form-label fw-semibold">Teruskan pesan ke:</label>
                                                        <select name="target_santri_id" class="form-select form-select-sm mb-2" required>
                                                            <option value="">-- Pilih Santri --</option>
                                                            @foreach ($santriList as $s)
                                                                <option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->nis }})</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="btn btn-primary btn-sm w-100">Teruskan</button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if ($comm->replies->isNotEmpty())
                            <div class="mt-2 ms-4 ps-3 border-start border-2 border-secondary">
                                @foreach ($comm->replies as $reply)
                                    <div class="d-flex mb-2 {{ $reply->direction === 'incoming' ? 'justify-content-start' : 'justify-content-end' }}">
                                        <div class="p-2 rounded-2 {{ $reply->direction === 'incoming' ? 'bg-info-lt text-info' : 'bg-primary-lt text-primary' }}" style="max-width: 75%;">
                                            <div class="fw-semibold small mb-1">
                                                {{ $reply->direction === 'incoming' ? 'Pondok' : $reply->user?->name ?? 'Wali Santri' }}
                                            </div>
                                            <p class="mb-1 small">{{ $reply->message }}</p>
                                            <div class="small opacity-75 text-end">
                                                {{ $reply->created_at->translatedFormat('d M Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            @empty
                <div class="text-secondary text-center py-5">
                    Belum ada pesan.
                </div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('komunikasi.store', $santri) }}" id="replyForm">
                @csrf
                <input type="hidden" name="parent_id" id="parentId" value="">
                <div class="mb-3">
                    <div id="replyIndicator" class="d-none align-items-center gap-2 mb-2 p-2 bg-secondary-lt rounded-2">
                        <i class="ti ti-reply"></i>
                        <span class="small flex-grow-1" id="replyMessagePreview">Membalas pesan...</span>
                        <button type="button" class="btn-close" id="cancelReply" aria-label="Batal balas"></button>
                    </div>
                    <label class="form-label">Balas Pesan</label>
                    <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="3" placeholder="Tulis balasan..." required></textarea>
                    @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-send me-1"></i>
                        Kirim Balasan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const replyBtns = document.querySelectorAll('.reply-btn');
            const cancelReplyBtn = document.getElementById('cancelReply');
            const parentIdInput = document.getElementById('parentId');
            const replyIndicator = document.getElementById('replyIndicator');
            const replyMessagePreview = document.getElementById('replyMessagePreview');

            replyBtns.forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const messageId = this.dataset.messageId;
                    const messageText = this.dataset.message;
                    parentIdInput.value = messageId;
                    replyMessagePreview.textContent = 'Membalas: ' + messageText;
                    replyIndicator.classList.remove('d-none');
                    replyIndicator.classList.add('d-flex');
                    document.getElementById('messageThread').scrollTop = document.getElementById('messageThread').scrollHeight;
                });
            });

            if (cancelReplyBtn) {
                cancelReplyBtn.addEventListener('click', function () {
                    parentIdInput.value = '';
                    replyIndicator.classList.add('d-none');
                    replyIndicator.classList.remove('d-flex');
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
