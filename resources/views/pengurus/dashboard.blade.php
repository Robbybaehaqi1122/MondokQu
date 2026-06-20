<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Dashboard Pengurus</h2>
            <div class="text-secondary mt-1">Ringkasan seluruh aktivitas pondok — santri, keuangan, kamar, dan izin.</div>
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
                    <h3 class="card-title mb-1">Santri Libur</h3>
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
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-1">Izin Menunggu Approval</h3>
                    <div class="fs-2 fw-bold">{{ number_format($leaveStats['pending_approval']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-1">Izin Disetujui Hari Ini</h3>
                    <div class="fs-2 fw-bold">{{ number_format($leaveStats['approved_today']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-1">Santri Sedang Izin</h3>
                    <div class="fs-2 fw-bold">{{ number_format($leaveStats['currently_on_leave']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-1">Izin Lewat Tanggal Kembali</h3>
                    <div class="fs-2 fw-bold text-danger">{{ number_format($leaveStats['overdue_return']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mt-3">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-1">Pemasukan Bulan Ini</h3>
                    <div class="fs-2 fw-bold">Rp {{ number_format($financeStats['paid_this_month'] / 100, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-1">Total Tagihan</h3>
                    <div class="fs-2 fw-bold">{{ number_format($financeStats['total_invoices']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-1">Sisa Tagihan</h3>
                    <div class="fs-2 fw-bold">Rp {{ number_format($financeStats['total_outstanding'] / 100, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-1">Tagihan Menunggak</h3>
                    <div class="fs-2 fw-bold text-danger">{{ number_format($financeStats['overdue_invoices']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mt-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Sebaran Kamar</h3>
                    <div class="text-secondary small mb-3">
                        {{ number_format($roomSummary['active']) }} dari {{ number_format($roomSummary['total']) }} kamar aktif
                        @if ($roomSummary['capacity'])
                            &middot; kapasitas {{ number_format((float) $roomSummary['capacity']) }}
                        @endif
                    </div>
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
        <div class="col-lg-6">
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
                                    <div class="text-secondary small">NIS: {{ $santri->nis }} - {{ $santri->displayRoomName('Belum kamar') }}</div>
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
                    <h3 class="card-title">Pembayaran Terbaru</h3>
                    @if($recentPayments->isNotEmpty())
                        <ul class="list-unstyled mb-0">
                            @foreach($recentPayments as $payment)
                                <li class="mb-2">
                                    <span class="fw-semibold">{{ $payment->santri?->full_name ?? '-' }}</span>
                                    <div class="text-secondary small">Rp {{ number_format($payment->amount / 100, 0, ',', '.') }} &middot; {{ $payment->paid_at?->translatedFormat('d M Y H:i') }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-secondary mb-0">Belum ada pembayaran tercatat.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
