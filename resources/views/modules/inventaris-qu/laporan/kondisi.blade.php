<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Laporan Kondisi Aset</h2>
            </div>
            <div>
                <a href="{{ route('inventaris.laporan.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        @forelse ($data as $item)
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center p-4">
                        <div class="h2 mb-1">{{ number_format($item['total']) }}</div>
                        <div class="text-secondary">{{ $item['kondisi'] }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card"><div class="card-body text-center text-secondary py-4">Belum ada data aset.</div></div>
            </div>
        @endforelse
    </div>
</x-app-layout>
