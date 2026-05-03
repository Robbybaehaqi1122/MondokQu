<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Dashboard Pengurus</h2>
            <div class="text-secondary mt-1">Operasional santri, kamar, dan izin.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Selamat datang, Pengurus</h3>
                    <p class="text-secondary mb-0">Menampilkan ringkasan data santri untuk pondok <strong>{{ $tenantName }}</strong>.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mt-3">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-1">Total Santri</h3>
                    <div class="fs-2 fw-bold">{{ number_format($stats['total_santri']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-1">Santri Aktif</h3>
                    <div class="fs-2 fw-bold">{{ number_format($stats['active_santri']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-1">Santri Cuti</h3>
                    <div class="fs-2 fw-bold">{{ number_format($stats['leave_santri']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-1">Santri Alumni</h3>
                    <div class="fs-2 fw-bold">{{ number_format($stats['alumni_santri']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mt-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Tugas Pengurus</h3>
                    <p class="text-secondary mb-2">Pengurus dapat input data santri, mengatur kamar, dan mengelola izin.</p>
                    <a href="{{ route('pengurus.santri') }}" class="btn btn-primary">Buka Data Santri</a>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Sebaran Kamar</h3>
                    @if(count($roomStats) > 0)
                        <ul class="list-unstyled mb-0">
                            @foreach($roomStats as $room)
                                <li class="mb-2">
                                    <span class="fw-semibold">{{ $room['room_name'] }}:</span>
                                    <span class="text-secondary">{{ $room['count'] }} santri</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-secondary mb-0">Belum ada pengaturan kamar.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Sebaran Angkatan</h3>
                    @if(count($entryYearStats) > 0)
                        <ul class="list-unstyled mb-0">
                            @foreach($entryYearStats as $entryYear)
                                <li class="mb-2">
                                    <span class="fw-semibold">{{ $entryYear['entry_year'] }}:</span>
                                    <span class="text-secondary">{{ $entryYear['count'] }} santri</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-secondary mb-0">Belum ada data angkatan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mt-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Santri Terbaru</h3>
                    @if($recentSantri->isNotEmpty())
                        <ul class="list-unstyled mb-0">
                            @foreach($recentSantri as $santri)
                                <li class="mb-2">
                                    <span class="fw-semibold">{{ $santri->full_name }}</span>
                                    <div class="text-secondary small">NIS: {{ $santri->nis }} - {{ $santri->room_name ?: 'Belum kamar' }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-secondary mb-0">Belum ada santri baru.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Terakhir Diperbarui</h3>
                    @if($recentlyUpdatedSantri->isNotEmpty())
                        <ul class="list-unstyled mb-0">
                            @foreach($recentlyUpdatedSantri as $santri)
                                <li class="mb-2">
                                    <span class="fw-semibold">{{ $santri->full_name }}</span>
                                    <div class="text-secondary small">Diubah {{ $santri->updated_at->diffForHumans() }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-secondary mb-0">Belum ada pembaruan santri.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
