<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Laporan Inventaris</h2>
                <div class="text-secondary mt-1">Pilih jenis laporan inventaris yang ingin dilihat.</div>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-md-4">
            <a href="{{ route('inventaris.laporan.per-lokasi') }}" class="card card-link">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="ti ti-building-warehouse" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="card-title">Per Lokasi</h3>
                    <p class="text-secondary">Daftar aset berdasarkan lokasi/ruangan</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('inventaris.laporan.per-kategori') }}" class="card card-link">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="ti ti-category" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="card-title">Per Kategori</h3>
                    <p class="text-secondary">Daftar aset berdasarkan kategori</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('inventaris.laporan.kondisi') }}" class="card card-link">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="ti ti-alert-triangle" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="card-title">Kondisi Aset</h3>
                    <p class="text-secondary">Breakdown kondisi aset (baik/rusak/hilang)</p>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
