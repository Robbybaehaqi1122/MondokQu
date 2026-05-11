<x-app-layout>
    @php
        $statusBadgeClasses = [
            'active' => 'bg-success-lt text-success',
            'leave' => 'bg-warning-lt text-warning',
            'exited' => 'bg-danger-lt text-danger',
            'alumni' => 'bg-blue-lt text-blue',
        ];

        $invoiceBadgeClasses = [
            'pending' => 'bg-warning-lt text-warning',
            'partial' => 'bg-blue-lt text-blue',
            'paid' => 'bg-success-lt text-success',
            'overdue' => 'bg-danger-lt text-danger',
        ];
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="page-title">Portal Wali Santri</h2>
            <div class="text-secondary mt-1">Ringkasan santri dan pembayaran yang terhubung dengan akun Anda.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="card-title mb-0">Santri Terhubung</h3>
                        <span class="avatar avatar-sm bg-indigo-lt text-indigo">
                            <i class="ti ti-users-group"></i>
                        </span>
                    </div>
                    <div class="fs-2 fw-bold mt-3">{{ number_format($summary['children_count']) }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="card-title mb-0">Belum Lunas</h3>
                        <span class="avatar avatar-sm bg-warning-lt text-warning">
                            <i class="ti ti-receipt"></i>
                        </span>
                    </div>
                    <div class="fs-2 fw-bold mt-3">{{ number_format($summary['outstanding_invoices']) }}</div>
                    <div class="text-secondary small">Rp {{ number_format((float) $summary['outstanding_amount'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="card-title mb-0">Tunggakan</h3>
                        <span class="avatar avatar-sm bg-danger-lt text-danger">
                            <i class="ti ti-alert-triangle"></i>
                        </span>
                    </div>
                    <div class="fs-2 fw-bold mt-3">{{ number_format($summary['overdue_invoices']) }}</div>
                    <div class="text-secondary small">Tagihan lewat tempo</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="card-title mb-0">Terbayar Bulan Ini</h3>
                        <span class="avatar avatar-sm bg-success-lt text-success">
                            <i class="ti ti-cash"></i>
                        </span>
                    </div>
                    <div class="fs-2 fw-bold mt-3">Rp {{ number_format((float) $summary['paid_this_month'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    @if ($childSummaries->isEmpty())
        <div class="row row-cards mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <span class="avatar avatar-xl bg-indigo-lt text-indigo mb-3">
                            <i class="ti ti-link-off fs-1"></i>
                        </span>
                        <h3 class="card-title">Belum Ada Santri Terhubung</h3>
                        <p class="text-secondary mb-0">Hubungi admin pondok agar akun wali Anda ditautkan ke data santri.</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row row-cards mt-3">
            @foreach ($childSummaries as $childSummary)
                @php
                    $santri = $childSummary['santri'];
                    $lastPayment = $childSummary['last_payment'];
                @endphp

                <div class="col-md-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">{{ $childSummary['relationship'] }}</div>
                                    <h3 class="card-title mt-2 mb-1">{{ $santri->full_name }}</h3>
                                    <div class="text-secondary small">NIS {{ $santri->nis }}</div>
                                </div>
                                <span class="badge {{ $statusBadgeClasses[$santri->status] ?? 'bg-secondary-lt text-secondary' }}">
                                    {{ $santri->statusLabel() }}
                                </span>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-6">
                                    <div class="text-secondary small">Kamar</div>
                                    <div class="fw-semibold mt-1">{{ $santri->room_name ?: 'Belum diatur' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-secondary small">Angkatan</div>
                                    <div class="fw-semibold mt-1">{{ $santri->entry_year ?: '-' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-secondary small">Tagihan Aktif</div>
                                    <div class="fw-semibold mt-1">{{ number_format($childSummary['outstanding_invoices']) }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-secondary small">Sisa Tagihan</div>
                                    <div class="fw-semibold mt-1">Rp {{ number_format((float) $childSummary['outstanding_amount'], 0, ',', '.') }}</div>
                                </div>
                            </div>

                            <div class="border-top mt-4 pt-3">
                                <div class="text-secondary small">Pembayaran Terakhir</div>
                                @if ($lastPayment)
                                    <div class="fw-semibold mt-1">Rp {{ number_format((float) $lastPayment->amount, 0, ',', '.') }}</div>
                                    <div class="text-secondary small">{{ $lastPayment->paid_at?->translatedFormat('d M Y H:i') }}</div>
                                @else
                                    <div class="text-secondary mt-1">Belum ada pembayaran.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row row-cards mt-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Tagihan Aktif</h3>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Tagihan</th>
                                <th>Santri</th>
                                <th>Jatuh Tempo</th>
                                <th>Sisa</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($upcomingInvoices as $invoice)
                                @php
                                    $displayStatus = $invoice->isOverdue() ? 'overdue' : $invoice->status;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $invoice->title }}</div>
                                        <div class="text-secondary small">{{ $invoice->invoice_number }}</div>
                                    </td>
                                    <td>{{ $invoice->santri?->full_name ?? '-' }}</td>
                                    <td class="text-secondary">{{ $invoice->due_date?->translatedFormat('d M Y') ?? '-' }}</td>
                                    <td>Rp {{ number_format($invoice->outstandingAmount(), 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge {{ $invoiceBadgeClasses[$displayStatus] ?? 'bg-secondary-lt text-secondary' }}">
                                            {{ $invoice->statusLabel() }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-secondary">Tidak ada tagihan aktif untuk santri terhubung.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Pembayaran Terakhir</h3>
                    </div>
                </div>
                <div class="card-body">
                    @forelse ($recentPayments as $payment)
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="avatar avatar-sm bg-success-lt text-success">
                                <i class="ti ti-check"></i>
                            </span>
                            <div class="flex-fill">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-sm-between gap-1">
                                    <div class="fw-semibold">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</div>
                                    <div class="text-secondary small">{{ $payment->paid_at?->translatedFormat('d M Y') }}</div>
                                </div>
                                <div class="text-secondary small mt-1">
                                    {{ $payment->santri?->full_name ?? '-' }} - {{ $payment->invoice?->title ?? 'Tanpa tagihan' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary">Belum ada pembayaran untuk santri terhubung.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
