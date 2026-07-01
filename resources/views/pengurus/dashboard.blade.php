<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">Panel Pengurus</div>
            <h2 class="page-title mt-1">Dashboard Operasional Pondok</h2>
            <div class="text-secondary mt-2">Ringkasan seluruh aktivitas pondok — santri, keuangan, kamar, dan izin.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-4">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar bg-primary-lt text-primary">
                                    <i class="ti ti-building-community fs-2"></i>
                                </span>
                                <div>
                                    <h3 class="card-title mb-0">Selamat datang, {{ auth()->user()->name }}</h3>
                                    <p class="text-secondary mb-0">{{ $tenantName }} &middot; {{ now()->translatedFormat('l, d F Y') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('santri.index') }}" class="btn btn-primary">
                                <i class="ti ti-users me-1"></i> Kelola Santri
                            </a>
                            <a href="{{ route('santri.index') }}#create-santri-modal" class="btn btn-outline-primary">
                                <i class="ti ti-user-plus me-1"></i> Tambah Santri
                            </a>
                            <a href="{{ route('santri.payments.invoices') }}" class="btn btn-outline-success">
                                <i class="ti ti-cash me-1"></i> Tagihan & Pembayaran
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="row g-3">
                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-azure-lt text-azure">
                                    <i class="ti ti-users fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">Total Santri</div>
                                    <div class="h1 mb-1">{{ number_format($stats['total_santri']) }}</div>
                                    <div class="text-secondary small d-none d-sm-block">Seluruh santri terdaftar.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-success-lt text-success">
                                    <i class="ti ti-user-check fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">Santri Aktif</div>
                                    <div class="h1 mb-1">{{ number_format($stats['active_santri']) }}</div>
                                    <div class="text-secondary small d-none d-sm-block">Santri yang aktif mondok.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-warning-lt text-warning">
                                    <i class="ti ti-user-x fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">Santri Libur</div>
                                    <div class="h1 mb-1">{{ number_format($stats['leave_santri']) }}</div>
                                    <div class="text-secondary small d-none d-sm-block">Santri sedang libur.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-purple-lt text-purple">
                                    <i class="ti ti-graduation-cap fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">Santri Alumni</div>
                                    <div class="h1 mb-1">{{ number_format($stats['alumni_santri']) }}</div>
                                    <div class="text-secondary small d-none d-sm-block">Telah menyelesaikan pendidikan.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="row g-3">
                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-warning-lt text-warning">
                                    <i class="ti ti-clock fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">Izin Menunggu</div>
                                    <div class="h2 mb-1">{{ number_format($leaveStats['pending_approval']) }}</div>
                                    <div class="text-secondary small d-none d-sm-block">Perlu persetujuan segera.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-success-lt text-success">
                                    <i class="ti ti-circle-check fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">Disetujui Hari Ini</div>
                                    <div class="h2 mb-1">{{ number_format($leaveStats['approved_today']) }}</div>
                                    <div class="text-secondary small d-none d-sm-block">Izin yang sudah di-approve.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-blue-lt text-blue">
                                    <i class="ti ti-logout fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">Sedang Izin</div>
                                    <div class="h2 mb-1">{{ number_format($leaveStats['currently_on_leave']) }}</div>
                                    <div class="text-secondary small d-none d-sm-block">Santri di luar pondok.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-danger-lt text-danger">
                                    <i class="ti ti-alert-triangle fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">Lewat Batas Kembali</div>
                                    <div class="h2 mb-1">{{ number_format($leaveStats['overdue_return']) }}</div>
                                    <div class="text-secondary small d-none d-sm-block">Belum kembali dari izin.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="row g-3">
                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-green-lt text-green">
                                    <i class="ti ti-cash fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">Pemasukan Bulan Ini</div>
                                    <div class="h2 mb-1">Rp {{ number_format($financeStats['paid_this_month'] / 100, 0, ',', '.') }}</div>
                                    <div class="text-secondary small d-none d-sm-block">Total pembayaran masuk.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-cyan-lt text-cyan">
                                    <i class="ti ti-file-invoice fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">Total Tagihan</div>
                                    <div class="h2 mb-1">{{ number_format($financeStats['total_invoices']) }}</div>
                                    <div class="text-secondary small d-none d-sm-block">Seluruh tagihan diterbitkan.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-orange-lt text-orange">
                                    <i class="ti ti-currency-dollar fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">Sisa Tagihan</div>
                                    <div class="h2 mb-1">Rp {{ number_format($financeStats['total_outstanding'] / 100, 0, ',', '.') }}</div>
                                    <div class="text-secondary small d-none d-sm-block">Nominal yang belum dibayar.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-danger-lt text-danger">
                                    <i class="ti ti-alert-circle fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">Tagihan Menunggak</div>
                                    <div class="h2 mb-1">{{ number_format($financeStats['overdue_invoices']) }}</div>
                                    <div class="text-secondary small d-none d-sm-block">Lewat jatuh tempo.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Status Santri</h3>
                        <div class="text-secondary small">Komposisi santri berdasarkan status.</div>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-xs bg-success-lt text-success">
                                <i class="ti ti-user-check"></i>
                            </span>
                            <span>Aktif</span>
                        </div>
                        <span class="badge bg-success-lt text-success">{{ number_format($stats['active_santri']) }}</span>
                    </div>
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-xs bg-warning-lt text-warning">
                                <i class="ti ti-user-x"></i>
                            </span>
                            <span>Libur</span>
                        </div>
                        <span class="badge bg-warning-lt text-warning">{{ number_format($stats['leave_santri']) }}</span>
                    </div>
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-xs bg-purple-lt text-purple">
                                <i class="ti ti-graduation-cap"></i>
                            </span>
                            <span>Alumni</span>
                        </div>
                        <span class="badge bg-purple-lt text-purple">{{ number_format($stats['alumni_santri']) }}</span>
                    </div>
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-xs bg-secondary-lt text-secondary">
                                <i class="ti ti-user-off"></i>
                            </span>
                            <span>Keluar</span>
                        </div>
                        <span class="badge bg-secondary-lt text-secondary">{{ number_format($stats['exited_santri']) }}</span>
                    </div>
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-xs bg-blue-lt text-blue">
                                <i class="ti ti-man"></i>
                            </span>
                            <span>Laki-laki</span>
                        </div>
                        <span class="badge bg-blue-lt text-blue">{{ number_format($genderStats['male']) }}</span>
                    </div>
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-xs bg-pink-lt text-pink">
                                <i class="ti ti-woman"></i>
                            </span>
                            <span>Perempuan</span>
                        </div>
                        <span class="badge bg-pink-lt text-pink">{{ number_format($genderStats['female']) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Sebaran Santri per Kamar</h3>
                        <div class="text-secondary small">Distribusi santri berdasarkan kamar.</div>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $maxRoomCount = count($roomStats) > 0 ? max(array_column($roomStats, 'count')) : 0;
                    @endphp
                    @if(count($roomStats) > 0)
                        <div class="d-flex flex-column gap-4">
                            @foreach($roomStats as $room)
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div>
                                            <div class="fw-semibold">{{ $room['room_name'] }}</div>
                                            <div class="text-secondary small">{{ $room['count'] }} santri</div>
                                        </div>
                                        <div class="fw-bold text-secondary">{{ $room['count'] }}</div>
                                    </div>
                                    <div class="progress progress-sm">
                                        <div
                                            class="progress-bar bg-{{ ['azure', 'blue', 'cyan', 'green', 'orange', 'purple', 'pink', 'indigo'][$loop->index % 8] }}"
                                            style="width: {{ $maxRoomCount > 0 ? max(4, ($room['count'] / $maxRoomCount) * 100) : 0 }}%"
                                            role="progressbar"
                                            aria-valuenow="{{ $room['count'] }}"
                                            aria-valuemin="0"
                                            aria-valuemax="{{ $maxRoomCount }}"
                                        ></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-secondary">Belum ada data kamar.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Sebaran Angkatan</h3>
                        <div class="text-secondary small">Komposisi santri berdasarkan tahun masuk.</div>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $maxEntryYearCount = count($entryYearStats) > 0 ? max(array_column($entryYearStats, 'count')) : 0;
                    @endphp
                    @if(count($entryYearStats) > 0)
                        <div class="d-flex flex-column gap-4">
                            @foreach($entryYearStats as $entryYear)
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div>
                                            <div class="fw-semibold">Angkatan {{ $entryYear['entry_year'] }}</div>
                                            <div class="text-secondary small">{{ $entryYear['count'] }} santri</div>
                                        </div>
                                        <div class="fw-bold text-secondary">{{ $entryYear['count'] }}</div>
                                    </div>
                                    <div class="progress progress-sm">
                                        <div
                                            class="progress-bar bg-{{ ['indigo', 'purple', 'pink', 'cyan', 'orange'][$loop->index % 5] }}"
                                            style="width: {{ $maxEntryYearCount > 0 ? max(4, ($entryYear['count'] / $maxEntryYearCount) * 100) : 0 }}%"
                                            role="progressbar"
                                            aria-valuenow="{{ $entryYear['count'] }}"
                                            aria-valuemin="0"
                                            aria-valuemax="{{ $maxEntryYearCount }}"
                                        ></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-secondary">Belum ada data angkatan.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Santri Terbaru</h3>
                        <div class="text-secondary small">Data santri yang paling baru masuk ke sistem.</div>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentSantri as $santri)
                        <div class="list-group-item d-flex align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                @if($santri->photoUrl())
                                    <span class="avatar avatar-md" style="background-image: url('{{ $santri->photoUrl() }}')"></span>
                                @else
                                    <span class="avatar avatar-md {{ $santri->gender === 'male' ? 'bg-blue-lt text-blue' : 'bg-pink-lt text-pink' }}">
                                        {{ strtoupper(substr($santri->full_name, 0, 1)) }}
                                    </span>
                                @endif
                                <div>
                                    <div class="fw-semibold">{{ $santri->full_name }}</div>
                                    <div class="text-secondary small">NIS {{ $santri->nis }} &middot; {{ $santri->displayRoomName('Kamar belum diatur') }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success-lt text-success">{{ $santri->statusLabel() }}</span>
                                <div class="text-secondary small mt-1">{{ $santri->created_at->translatedFormat('d M Y') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Belum ada santri baru.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Pembayaran Terbaru</h3>
                        <div class="text-secondary small">Transaksi pembayaran yang paling baru tercatat.</div>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentPayments as $payment)
                        <div class="list-group-item d-flex align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                @php $paymentSantri = $payment->santri; @endphp
                                @if($paymentSantri && $paymentSantri->photoUrl())
                                    <span class="avatar avatar-md" style="background-image: url('{{ $paymentSantri->photoUrl() }}')"></span>
                                @else
                                    <span class="avatar avatar-md bg-success-lt text-success">
                                        <i class="ti ti-cash"></i>
                                    </span>
                                @endif
                                <div>
                                    <div class="fw-semibold">{{ $paymentSantri?->full_name ?? '-' }}</div>
                                    <div class="text-secondary small">Rp {{ number_format($payment->amount / 100, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="text-end text-secondary small">
                                {{ $payment->paid_at?->translatedFormat('d M Y H:i') }}
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Belum ada pembayaran tercatat.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Ringkasan Kamar</h3>
                        <div class="text-secondary small">Status kamar dan kapasitas pondok.</div>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-xs bg-cyan-lt text-cyan">
                                <i class="ti ti-door"></i>
                            </span>
                            <div>
                                <div class="fw-semibold">Total Kamar</div>
                                <div class="text-secondary small">Seluruh kamar yang terdaftar.</div>
                            </div>
                        </div>
                        <span class="badge bg-cyan-lt text-cyan">{{ number_format($roomSummary['total']) }}</span>
                    </div>
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-xs bg-green-lt text-green">
                                <i class="ti ti-door-open"></i>
                            </span>
                            <div>
                                <div class="fw-semibold">Kamar Aktif</div>
                                <div class="text-secondary small">Kamar yang tersedia dan aktif.</div>
                            </div>
                        </div>
                        <span class="badge bg-green-lt text-green">{{ number_format($roomSummary['active']) }}</span>
                    </div>
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-xs bg-orange-lt text-orange">
                                <i class="ti ti-bed"></i>
                            </span>
                            <div>
                                <div class="fw-semibold">Kapasitas Total</div>
                                <div class="text-secondary small">Total kapasitas maksimum kamar.</div>
                            </div>
                        </div>
                        <span class="badge bg-orange-lt text-orange">{{ $roomSummary['capacity'] ? number_format((float) $roomSummary['capacity']) : '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
