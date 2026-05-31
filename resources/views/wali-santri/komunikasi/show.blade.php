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
        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
            @forelse ($communications as $comm)
                <div class="d-flex mb-3 {{ $comm->direction === 'outgoing' ? 'justify-content-end' : 'justify-content-start' }}">
                    <div class="p-3 rounded-2 {{ $comm->direction === 'outgoing' ? 'bg-primary-lt text-primary' : 'bg-info-lt text-info' }}" style="max-width: 75%;">
                        <div class="fw-semibold small mb-1">
                            {{ $comm->direction === 'outgoing' ? 'Saya' : 'Pondok' }}
                        </div>
                        <p class="mb-1">{{ $comm->message }}</p>
                        <div class="small opacity-75 text-end">
                            {{ $comm->created_at->translatedFormat('d M Y H:i') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-secondary text-center py-5">
                    Belum ada pesan. Kirim pesan pertama ke pihak pondok.
                </div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('wali-santri.komunikasi.store') }}">
                @csrf
                <input type="hidden" name="santri_id" value="{{ $santri->id }}">
                <div class="mb-3">
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
</x-app-layout>
