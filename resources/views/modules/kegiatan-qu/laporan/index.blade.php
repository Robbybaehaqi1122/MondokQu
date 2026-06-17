<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KegiatanQu</div>
            <h2 class="page-title mt-1">Laporan Kegiatan</h2>
        </div>
    </x-slot>

    @if ($summary)
        <div class="row row-cards mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-secondary small text-uppercase fw-bold">Total Kegiatan</div>
                        <div class="fs-2 fw-bold mb-0">{{ number_format($summary['totalKegiatan']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-secondary small text-uppercase fw-bold">Kegiatan Aktif</div>
                        <div class="fs-2 fw-bold mb-0">{{ number_format($summary['kegiatanAktif']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-secondary small text-uppercase fw-bold">Total Pendaftar</div>
                        <div class="fs-2 fw-bold mb-0">{{ number_format($summary['totalPendaftar']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-secondary small text-uppercase fw-bold">Total Pertemuan</div>
                        <div class="fs-2 fw-bold mb-0">{{ number_format($summary['totalPertemuan']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-4">
            <a href="{{ route('kegiatan.laporan.kehadiran') }}" class="card card-link">
                <div class="card-body text-center p-4">
                    <div class="mb-2">
                        <i class="ti ti-clipboard-check" style="font-size:2rem"></i>
                    </div>
                    <h4 class="card-title mb-1">Laporan Kehadiran</h4>
                    <div class="text-secondary small">Rekap presensi peserta per kegiatan</div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('kegiatan.laporan.nilai') }}" class="card card-link">
                <div class="card-body text-center p-4">
                    <div class="mb-2">
                        <i class="ti ti-star" style="font-size:2rem"></i>
                    </div>
                    <h4 class="card-title mb-1">Laporan Nilai</h4>
                    <div class="text-secondary small">Rekap penilaian perkembangan santri</div>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
