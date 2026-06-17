<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Laporan Keuangan</h2>
            <div class="text-secondary mt-1">Pilih jenis laporan keuangan yang ingin dilihat.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-md-6 col-xl-4">
            <a href="{{ route('keuangan.laporan.profit-loss') }}" class="card card-link">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="ti ti-report-money text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="card-title">Laporan Laba / Rugi</h3>
                    <p class="text-secondary">Ringkasan pendapatan dan beban per bulan. Menampilkan laba atau rugi bersih.</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-4">
            <a href="{{ route('keuangan.laporan.cash-flow') }}" class="card card-link">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="ti ti-arrow-wave-right-up text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="card-title">Laporan Arus Kas</h3>
                    <p class="text-secondary">Pemantauan arus kas masuk dan keluar setiap bulan.</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-4">
            <a href="{{ route('keuangan.laporan.ledger') }}" class="card card-link">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="ti ti-book text-info" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="card-title">Buku Besar</h3>
                    <p class="text-secondary">Riwayat transaksi per akun. Melihat semua mutase debit dan kredit.</p>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
