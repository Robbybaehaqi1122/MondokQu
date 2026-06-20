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

        $leaveBadgeClasses = [
            'pending' => 'bg-warning-lt text-warning',
            'approved' => 'bg-success-lt text-success',
            'rejected' => 'bg-danger-lt text-danger',
            'completed' => 'bg-info-lt text-info',
        ];

        $attendanceBadgeClasses = [
            'present' => 'bg-success-lt text-success',
            'permission' => 'bg-azure-lt text-azure',
            'sick' => 'bg-warning-lt text-warning',
            'absent' => 'bg-danger-lt text-danger',
            'late' => 'bg-orange-lt text-orange',
        ];

        $confirmationBadgeClasses = [
            'pending' => 'bg-warning-lt text-warning',
            'approved' => 'bg-success-lt text-success',
            'rejected' => 'bg-danger-lt text-danger',
        ];
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="page-title">Portal Wali Santri</h2>
            <div class="text-secondary mt-1">Ringkasan santri, pembayaran, dan izin yang terhubung dengan akun Anda.</div>
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
                    <div class="text-secondary small">Rp {{ number_format($summary['outstanding_amount'] / 100, 0, ',', '.') }}</div>
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
                    <div class="fs-2 fw-bold mt-3">Rp {{ number_format($summary['paid_this_month'] / 100, 0, ',', '.') }}</div>
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
                                    <div class="fw-semibold mt-1">{{ $santri->displayRoomName('Belum diatur') }}</div>
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
                                    <div class="fw-semibold mt-1">Rp {{ number_format($childSummary['outstanding_amount'] / 100, 0, ',', '.') }}</div>
                                </div>
                            </div>

                            <div class="border-top mt-4 pt-3">
                                <div class="text-secondary small">Pembayaran Terakhir</div>
                                @if ($lastPayment)
                                    <div class="fw-semibold mt-1">Rp {{ number_format($lastPayment->amount / 100, 0, ',', '.') }}</div>
                                    <div class="text-secondary small">{{ $lastPayment->paid_at?->translatedFormat('d M Y H:i') }}</div>
                                @else
                                    <div class="text-secondary mt-1">Belum ada pembayaran.</div>
                                @endif
                            </div>

                            <div class="d-grid gap-2 mt-3">
                                <a href="{{ route('wali-santri.profil-santri', $santri) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="ti ti-user me-1"></i>
                                    Profil Santri
                                </a>
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="{{ route('wali-santri.absensi', $santri) }}" class="btn btn-outline-azure btn-sm">
                                        <i class="ti ti-clipboard-check me-1"></i>
                                        Absensi
                                    </a>
                                    <a href="{{ route('wali-santri.pelanggaran', $santri) }}" class="btn btn-outline-danger btn-sm">
                                        <i class="ti ti-alert-triangle me-1"></i>
                                        Pelanggaran
                                    </a>
                                    <a href="{{ route('wali-santri.tahfidz', $santri) }}" class="btn btn-outline-success btn-sm">
                                        <i class="ti ti-book me-1"></i>
                                        Tahfidz
                                    </a>
                                    <a href="{{ route('wali-santri.akademik', $santri) }}" class="btn btn-outline-info btn-sm">
                                        <i class="ti ti-books me-1"></i>
                                        Akademik
                                    </a>
                                    <a href="{{ route('wali-santri.rapor', $santri) }}" class="btn btn-outline-warning btn-sm">
                                        <i class="ti ti-report-analytics me-1"></i>
                                        Rapor
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row row-cards mt-3">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Ringkasan Absensi</h3>
                        <div class="text-secondary small mt-2">Akumulasi absensi santri terhubung dalam 30 hari terakhir.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                        <span class="text-secondary">Total Catatan</span>
                        <span class="fs-2 fw-bold">{{ number_format($attendanceSummary['total']) }}</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-secondary small">Hadir</div>
                            <div class="fs-2 fw-bold">{{ number_format($attendanceSummary['present']) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-secondary small">Izin</div>
                            <div class="fs-2 fw-bold">{{ number_format($attendanceSummary['permission']) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-secondary small">Sakit</div>
                            <div class="fs-2 fw-bold">{{ number_format($attendanceSummary['sick']) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-secondary small">Alpa</div>
                            <div class="fs-2 fw-bold">{{ number_format($attendanceSummary['absent']) }}</div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                <span class="text-secondary">Terlambat</span>
                                <span class="fw-semibold">{{ number_format($attendanceSummary['late']) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Riwayat Absensi Santri</h3>
                        <div class="text-secondary small mt-2">Catatan terbaru dari sesi absensi santri terhubung.</div>
                    </div>
                </div>
                <div class="card-body">
                    @forelse ($recentAttendanceRecords as $attendanceRecord)
                        <div class="d-flex align-items-start gap-3 py-3 @unless($loop->last) border-bottom @endunless">
                            <span class="avatar avatar-sm {{ $attendanceBadgeClasses[$attendanceRecord->status] ?? 'bg-secondary-lt text-secondary' }}">
                                <i class="ti ti-clipboard-check"></i>
                            </span>
                            <div class="flex-fill min-width-0">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-start justify-content-sm-between gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ $attendanceRecord->santri?->full_name ?? '-' }}</div>
                                        <div class="text-secondary small">
                                            {{ $attendanceRecord->session?->activity?->name ?? '-' }}
                                            &bull;
                                            {{ $attendanceRecord->session?->session_date?->translatedFormat('d M Y') ?? '-' }}
                                        </div>
                                    </div>
                                    <span class="badge {{ $attendanceBadgeClasses[$attendanceRecord->status] ?? 'bg-secondary-lt text-secondary' }}">
                                        {{ $attendanceRecord->statusLabel() }}
                                    </span>
                                </div>
                                @if ($attendanceRecord->notes)
                                    <div class="text-secondary mt-2">{{ \Illuminate\Support\Str::limit($attendanceRecord->notes, 120) }}</div>
                                @endif
                                @if ($attendanceRecord->recorded_at)
                                    <div class="text-secondary small mt-2">Diinput {{ $attendanceRecord->recorded_at->translatedFormat('d M Y H:i') }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary">Belum ada riwayat absensi untuk santri terhubung.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mt-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Riwayat Izin Santri</h3>
                    </div>
                </div>
                <div class="card-body">
                    @forelse ($recentLeaveRequests as $leaveRequest)
                        <div class="d-flex align-items-start gap-3 py-3 @unless($loop->last) border-bottom @endunless">
                            <span class="avatar avatar-sm {{ $leaveBadgeClasses[$leaveRequest->status] ?? 'bg-secondary-lt text-secondary' }}">
                                <i class="ti ti-clipboard-check"></i>
                            </span>
                            <div class="flex-fill min-width-0">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-start justify-content-sm-between gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ $leaveRequest->santri?->full_name ?? '-' }}</div>
                                        <div class="text-secondary small">
                                            {{ $leaveRequest->start_date?->translatedFormat('d M Y') }}
                                            @if ($leaveRequest->end_date && ! $leaveRequest->start_date?->isSameDay($leaveRequest->end_date))
                                                s/d {{ $leaveRequest->end_date?->translatedFormat('d M Y') }}
                                            @endif
                                        </div>
                                    </div>
                                    <span class="badge {{ $leaveBadgeClasses[$leaveRequest->status] ?? 'bg-secondary-lt text-secondary' }}">
                                        {{ $leaveRequest->statusLabel() }}
                                    </span>
                                </div>
                                <div class="text-secondary mt-2">{{ \Illuminate\Support\Str::limit($leaveRequest->reason, 120) }}</div>
                                @if ($leaveRequest->approved_at)
                                    <div class="text-secondary small mt-2">Diproses {{ $leaveRequest->approved_at->translatedFormat('d M Y H:i') }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary">Belum ada riwayat izin untuk santri terhubung.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Ringkasan Izin</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-secondary small">Menunggu</div>
                            <div class="fs-2 fw-bold">{{ number_format($leaveSummary['pending']) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-secondary small">Sedang Izin</div>
                            <div class="fs-2 fw-bold">{{ number_format($leaveSummary['active_today']) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-secondary small">Disetujui</div>
                            <div class="fs-2 fw-bold">{{ number_format($leaveSummary['approved']) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-secondary small">Selesai</div>
                            <div class="fs-2 fw-bold">{{ number_format($leaveSummary['completed']) }}</div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                <span class="text-secondary">Ditolak</span>
                                <span class="fw-semibold">{{ number_format($leaveSummary['rejected']) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($upcomingInvoices as $invoice)
                                @php
                                    $displayStatus = $invoice->isOverdue() ? 'overdue' : $invoice->status;
                                    $pendingConfirmations = $pendingPaymentConfirmationsByInvoice->get($invoice->id, collect());
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $invoice->title }}</div>
                                        <div class="text-secondary small">{{ $invoice->invoice_number }}</div>
                                        @if ($pendingConfirmations->isNotEmpty())
                                            <span class="badge mt-2 {{ $confirmationBadgeClasses['pending'] }}">
                                                Bukti menunggu verifikasi
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $invoice->santri?->full_name ?? '-' }}</td>
                                    <td class="text-secondary">{{ $invoice->due_date?->translatedFormat('d M Y') ?? '-' }}</td>
                                    <td>Rp {{ number_format($invoice->outstandingAmount() / 100, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge {{ $invoiceBadgeClasses[$displayStatus] ?? 'bg-secondary-lt text-secondary' }}">
                                            {{ $invoice->statusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#uploadProofModal{{ $invoice->id }}">
                                                <i class="ti ti-upload me-1"></i>
                                                Bukti Bayar
                                            </button>
                                            <a href="{{ route('wali-santri.invoices.show', $invoice) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="ti ti-eye me-1"></i>
                                                Detail
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-secondary">Tidak ada tagihan aktif untuk santri terhubung.</td>
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
                        <div class="d-flex align-items-start gap-3 py-3 @unless($loop->last) border-bottom @endunless">
                            <span class="avatar avatar-sm bg-success-lt text-success">
                                <i class="ti ti-check"></i>
                            </span>
                            <div class="flex-fill min-width-0">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-sm-between gap-1">
                                    <div class="fw-semibold">Rp {{ number_format($payment->amount / 100, 0, ',', '.') }}</div>
                                    <div class="text-secondary small">{{ $payment->paid_at?->translatedFormat('d M Y') }}</div>
                                </div>
                                <div class="text-secondary small mt-1">
                                    <span>{{ $payment->santri?->full_name ?? '-' }}</span>
                                    &middot;
                                    <span>{{ $payment->invoice?->title ?? 'Tanpa tagihan' }}</span>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <span class="badge bg-success-lt text-success">
                                        {{ $payment->payment_method ? str($payment->payment_method)->headline() : 'Pembayaran' }}
                                    </span>
                                    @if ($payment->reference_number)
                                        <span class="badge bg-secondary-lt text-secondary">{{ $payment->reference_number }}</span>
                                    @endif
                                </div>
                                @if ($payment->invoice)
                                    <a href="{{ route('wali-santri.invoices.show', $payment->invoice) }}" class="btn btn-outline-primary btn-sm mt-3">
                                        <i class="ti ti-eye me-1"></i>
                                        Detail Tagihan
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary">Belum ada pembayaran untuk santri terhubung.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @foreach ($upcomingInvoices as $invoice)
        <div class="modal modal-blur fade" id="uploadProofModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('wali-santri.invoices.payment-confirmations.store', $invoice) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="confirmation_invoice_id" value="{{ $invoice->id }}">

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Upload Bukti Bayar</h5>
                                <div class="text-secondary small mt-1">{{ $invoice->invoice_number }} &middot; {{ $invoice->santri?->full_name ?? '-' }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            @if ($errors->paymentConfirmation->any() && (string) old('confirmation_invoice_id') === (string) $invoice->id)
                                <div class="alert alert-danger" role="alert">
                                    <div class="fw-semibold mb-2">Bukti bayar belum bisa dikirim.</div>
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->paymentConfirmation->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="proof_amount_{{ $invoice->id }}" class="form-label">Nominal Transfer</label>
                                    <input
                                        id="proof_amount_{{ $invoice->id }}"
                                        name="amount"
                                        type="number"
                                        min="1"
                                        max="{{ $invoice->outstandingAmount() / 100 }}"
                                        step="1"
                                        class="form-control @if($errors->paymentConfirmation->has('amount') && (string) old('confirmation_invoice_id') === (string) $invoice->id) is-invalid @endif"
                                        value="{{ old('confirmation_invoice_id') == $invoice->id ? old('amount') : $invoice->outstandingAmount() / 100 }}"
                                        required
                                    >
                                </div>
                                <div class="col-md-6">
                                    <label for="proof_paid_at_{{ $invoice->id }}" class="form-label">Tanggal Transfer</label>
                                    <input
                                        id="proof_paid_at_{{ $invoice->id }}"
                                        name="paid_at"
                                        type="datetime-local"
                                        class="form-control @if($errors->paymentConfirmation->has('paid_at') && (string) old('confirmation_invoice_id') === (string) $invoice->id) is-invalid @endif"
                                        value="{{ old('confirmation_invoice_id') == $invoice->id ? old('paid_at') : now()->format('Y-m-d\\TH:i') }}"
                                        required
                                    >
                                </div>
                                <div class="col-md-6">
                                    <label for="proof_method_{{ $invoice->id }}" class="form-label">Metode Pembayaran</label>
                                    <select
                                        id="proof_method_{{ $invoice->id }}"
                                        name="payment_method"
                                        class="form-select form-select-pretty @if($errors->paymentConfirmation->has('payment_method') && (string) old('confirmation_invoice_id') === (string) $invoice->id) is-invalid @endif"
                                        required
                                    >
                                        @foreach ($paymentMethods as $method)
                                            <option value="{{ $method }}" @selected((old('confirmation_invoice_id') == $invoice->id ? old('payment_method') : 'transfer bank') === $method)>
                                                {{ str($method)->headline() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="proof_reference_{{ $invoice->id }}" class="form-label">No. Referensi</label>
                                    <input
                                        id="proof_reference_{{ $invoice->id }}"
                                        name="reference_number"
                                        type="text"
                                        maxlength="100"
                                        class="form-control @if($errors->paymentConfirmation->has('reference_number') && (string) old('confirmation_invoice_id') === (string) $invoice->id) is-invalid @endif"
                                        value="{{ old('confirmation_invoice_id') == $invoice->id ? old('reference_number') : '' }}"
                                        placeholder="Opsional"
                                    >
                                </div>
                                <div class="col-12">
                                    <label for="proof_file_{{ $invoice->id }}" class="form-label">File Bukti Bayar</label>
                                    <input
                                        id="proof_file_{{ $invoice->id }}"
                                        name="proof"
                                        type="file"
                                        class="form-control @if($errors->paymentConfirmation->has('proof') && (string) old('confirmation_invoice_id') === (string) $invoice->id) is-invalid @endif"
                                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                        required
                                    >
                                    <div class="form-hint mt-2">JPG, PNG, atau WEBP. Maksimal 4 MB.</div>
                                </div>
                                <div class="col-12">
                                    <label for="proof_note_{{ $invoice->id }}" class="form-label">Catatan</label>
                                    <textarea
                                        id="proof_note_{{ $invoice->id }}"
                                        name="note"
                                        rows="3"
                                        maxlength="1000"
                                        class="form-control @if($errors->paymentConfirmation->has('note') && (string) old('confirmation_invoice_id') === (string) $invoice->id) is-invalid @endif"
                                        placeholder="Opsional. Contoh: transfer atas nama orang tua."
                                    >{{ old('confirmation_invoice_id') == $invoice->id ? old('note') : '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-upload me-1"></i>
                                Kirim Bukti Bayar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if ($errors->paymentConfirmation->any() && old('confirmation_invoice_id'))
                const proofModalElement = document.getElementById('uploadProofModal{{ old('confirmation_invoice_id') }}');

                if (proofModalElement && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(proofModalElement).show();
                }
            @endif
        });
    </script>
</x-app-layout>
