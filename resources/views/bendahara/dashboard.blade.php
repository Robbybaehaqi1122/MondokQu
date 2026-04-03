<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Dashboard Bendahara</h2>
            <div class="text-secondary mt-1">Pembayaran dan laporan keuangan pondok.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Tugas Bendahara</h3>
                    <p class="text-secondary mb-0">Bendahara dapat mengelola pembayaran dan melihat laporan keuangan.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('bendahara.laporan') }}" class="btn btn-primary">Buka Laporan</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
