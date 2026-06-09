<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Pesan dengan Pondok</h2>
                <div class="text-secondary mt-1">{{ $santri->full_name }} &middot; NIS {{ $santri->nis }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('wali-santri.komunikasi.index') }}" class="btn btn-outline-secondary">
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
                        <div class="d-flex {{ $comm->direction === 'outgoing' ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="p-3 rounded-2 {{ $comm->direction === 'outgoing' ? 'bg-primary-lt text-primary' : 'bg-info-lt text-info' }}" style="max-width: 75%;">
                                <div class="fw-semibold small mb-1 d-flex align-items-center gap-2">
                                    <span>{{ $comm->direction === 'outgoing' ? 'Saya' : 'Pondok' }}</span>
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
                                    <span>
                                        @if ($comm->direction === 'incoming')
                                            <a href="#" class="text-reset text-decoration-none reply-btn" data-message-id="{{ $comm->id }}" data-message="{{ Str::limit($comm->message, 50) }}">
                                                <i class="ti ti-reply"></i>
                                            </a>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if ($comm->replies->isNotEmpty())
                            <div class="mt-2 ms-4 ps-3 border-start border-2 border-secondary">
                                @foreach ($comm->replies as $reply)
                                    <div class="d-flex mb-2 {{ $reply->direction === 'outgoing' ? 'justify-content-end' : 'justify-content-start' }}">
                                        <div class="p-2 rounded-2 {{ $reply->direction === 'outgoing' ? 'bg-primary-lt text-primary' : 'bg-info-lt text-info' }}" style="max-width: 75%;">
                                            <div class="fw-semibold small mb-1">
                                                {{ $reply->direction === 'outgoing' ? 'Saya' : 'Pondok' }}
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
                    Belum ada pesan. Kirim pesan pertama ke pihak pondok.
                </div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('wali-santri.komunikasi.store') }}" id="replyForm">
                @csrf
                <input type="hidden" name="santri_id" value="{{ $santri->id }}">
                <input type="hidden" name="parent_id" id="parentId" value="">
                <div class="mb-3">
                    <div id="replyIndicator" class="d-none align-items-center gap-2 mb-2 p-2 bg-secondary-lt rounded-2">
                        <i class="ti ti-reply"></i>
                        <span class="small flex-grow-1" id="replyMessagePreview">Membalas pesan...</span>
                        <button type="button" class="btn-close" id="cancelReply" aria-label="Batal balas"></button>
                    </div>
                    <label class="form-label">Pesan Baru</label>
                    <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="3" placeholder="Tulis pesan untuk pihak pondok..." required></textarea>
                    @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-send me-1"></i>
                        Kirim Pesan
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
