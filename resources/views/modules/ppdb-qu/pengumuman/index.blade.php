<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">PpdbQu</div>
                <h2 class="page-title mt-1">Pengumuman</h2>
            </div>
            <a href="{{ route('ppdb.pengumuman.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Buat Pengumuman
            </a>
        </div>
    </x-slot>

    <div class="row row-cards">
        @forelse ($pengumumans as $p)
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $p->judul }}</h3>
                        @if ($p->published_at)
                            <span class="badge bg-success ms-auto">Published</span>
                        @else
                            <span class="badge bg-warning ms-auto">Draft</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="text-secondary small mb-2">
                            {{ $p->gelombang?->nama }} &middot;
                            {{ $p->tanggal_pengumuman->translatedFormat('d M Y') }} &middot;
                            Oleh: {{ $p->creator?->name ?? '-' }}
                        </div>
                        @if ($p->deskripsi)
                            <p class="mb-0">{{ Str::limit($p->deskripsi, 150) }}</p>
                        @endif
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <a href="{{ route('ppdb.pengumuman.show', $p) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        @if (! $p->published_at)
                            <form action="{{ route('ppdb.pengumuman.publish', $p) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success">Publikasikan</button>
                            </form>
                        @endif
                        <form action="{{ route('ppdb.pengumuman.destroy', $p) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus?')">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card"><div class="card-body text-secondary">Belum ada pengumuman.</div></div>
            </div>
        @endforelse
    </div>
</x-app-layout>
