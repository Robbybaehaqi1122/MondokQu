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
        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
            @forelse ($communications as $comm)
                <div class="d-flex mb-3 {{ $comm->direction === 'incoming' ? 'justify-content-start' : 'justify-content-end' }}">
                    <div class="p-3 rounded-2 {{ $comm->direction === 'incoming' ? 'bg-info-lt text-info' : 'bg-primary-lt text-primary' }}" style="max-width: 75%;">
                        <div class="fw-semibold small mb-1">
                            {{ $comm->direction === 'incoming' ? 'Pondok' : $comm->user?->name ?? 'Wali Santri' }}
                        </div>
                        <p class="mb-1">{{ $comm->message }}</p>
                        <div class="small opacity-75 text-end">
                            {{ $comm->created_at->translatedFormat('d M Y H:i') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-secondary text-center py-5">
                    Belum ada pesan.
                </div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('komunikasi.store', $santri) }}">
                @csrf
                <div class="mb-3">
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
</x-app-layout>
