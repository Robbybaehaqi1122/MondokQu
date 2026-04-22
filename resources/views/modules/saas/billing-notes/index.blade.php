<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">SaaS</div>
            <h2 class="page-title mt-1">Billing Notes</h2>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 w-100">
                        <div>
                            <h3 class="card-title">Riwayat Billing Tenant</h3>
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary"
                            id="open-create-billing-note-modal"
                            data-bs-toggle="modal"
                            data-bs-target="#createBillingNoteModal"
                        >
                            <i class="ti ti-receipt-2 me-1"></i>
                            Tambah Billing Note
                        </button>
                    </div>
                </div>

                <div class="card-body border-bottom user-filter-panel">
                    <form method="GET" action="{{ route('saas.billing-notes.index') }}" class="row g-3 align-items-end user-filter-form">
                            <div class="col-lg-4 col-xl-4">
                                <label for="billing-note-search" class="form-label">Cari Billing</label>
                                <input
                                    id="billing-note-search"
                                    name="search"
                                    type="text"
                                    class="form-control"
                                    value="{{ $filters['search'] ?? '' }}"
                                    placeholder="Cari tenant, catatan billing, metode, atau admin"
                                >
                            </div>
                            <div class="col-md-6 col-lg-2 col-xl-2">
                                <label for="billing-note-tenant" class="form-label">Tenant</label>
                                <select id="billing-note-tenant" name="tenant_id" class="form-select form-select-pretty">
                                    <option value="">Semua tenant</option>
                                    @foreach ($tenants as $tenant)
                                        <option value="{{ $tenant->id }}" @selected((string) ($filters['tenant_id'] ?? '') === (string) $tenant->id)>{{ $tenant->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2 col-xl-2">
                                <label for="billing-note-method" class="form-label">Metode</label>
                                <select id="billing-note-method" name="payment_method" class="form-select form-select-pretty">
                                    <option value="">Semua metode</option>
                                    @foreach (['transfer bank', 'cash', 'e-wallet', 'qris', 'lainnya'] as $method)
                                        <option value="{{ $method }}" @selected(($filters['payment_method'] ?? '') === $method)>{{ str($method)->headline() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 col-xl-2">
                                <div class="d-flex gap-2 user-filter-actions">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('saas.billing-notes.index') }}" class="btn btn-outline-secondary">Reset</a>
                                </div>
                            </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Tenant</th>
                                <th>Tanggal Bayar</th>
                                <th>Nominal</th>
                                <th>Metode</th>
                                <th>Periode</th>
                                <th>Catatan</th>
                                <th>Diinput Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($billingNotes as $billingNote)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $billingNote->tenant?->name ?? 'Tenant tidak ditemukan' }}</div>
                                        <div class="text-secondary small">{{ $billingNote->tenant?->slug ?? '-' }}</div>
                                    </td>
                                    <td class="text-secondary small">{{ $billingNote->paid_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                                    <td class="fw-semibold">Rp {{ number_format((float) $billingNote->amount, 0, ',', '.') }}</td>
                                    <td>{{ str($billingNote->payment_method)->headline() }}</td>
                                    <td>
                                        <div>{{ $billingNote->period_starts_at?->translatedFormat('d M Y') ?? '-' }}</div>
                                        <div class="text-secondary small mt-1">sampai {{ $billingNote->period_ends_at?->translatedFormat('d M Y') ?? '-' }}</div>
                                    </td>
                                    <td class="text-secondary small">{{ $billingNote->admin_note ?: 'Tanpa catatan billing' }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $billingNote->recordedByUser?->name ?? 'Sistem' }}</div>
                                        <div class="text-secondary small">{{ $billingNote->recordedByUser?->username ? '@'.$billingNote->recordedByUser->username : 'Tanpa akun login' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-secondary">Belum ada billing note yang tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($billingNotes->hasPages())
                    <div class="card-footer">
                        {{ $billingNotes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal modal-blur fade" id="createBillingNoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('saas.billing-notes.store') }}">
                    @csrf

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Tambah Billing Note</h5>
                            <div class="text-secondary small mt-1">Catat pembayaran tenant secara manual agar jejak billing tetap rapi.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @if ($errors->billingNote->any())
                            <div class="alert alert-danger" role="alert">
                                <div class="fw-semibold mb-2">Billing note belum bisa disimpan.</div>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->billingNote->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="tenant_id" class="form-label">Tenant</label>
                                <select id="tenant_id" name="tenant_id" class="form-select @if($errors->billingNote->has('tenant_id')) is-invalid @endif" required>
                                    <option value="">Pilih tenant</option>
                                    @foreach ($tenants as $tenant)
                                        <option value="{{ $tenant->id }}" @selected(old('tenant_id') == $tenant->id)>{{ $tenant->name }} ({{ $tenant->slug }})</option>
                                    @endforeach
                                </select>
                                @if ($errors->billingNote->has('tenant_id'))
                                    <div class="invalid-feedback">{{ $errors->billingNote->first('tenant_id') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="paid_at" class="form-label">Tanggal Bayar</label>
                                <input
                                    id="paid_at"
                                    name="paid_at"
                                    type="datetime-local"
                                    class="form-control @if($errors->billingNote->has('paid_at')) is-invalid @endif"
                                    value="{{ old('paid_at', now()->format('Y-m-d\\TH:i')) }}"
                                    required
                                >
                                @if ($errors->billingNote->has('paid_at'))
                                    <div class="invalid-feedback">{{ $errors->billingNote->first('paid_at') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="amount" class="form-label">Nominal</label>
                                <input
                                    id="amount"
                                    name="amount"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="form-control @if($errors->billingNote->has('amount')) is-invalid @endif"
                                    value="{{ old('amount') }}"
                                    placeholder="Contoh: 300000"
                                    required
                                >
                                @if ($errors->billingNote->has('amount'))
                                    <div class="invalid-feedback">{{ $errors->billingNote->first('amount') }}</div>
                                @else
                                    <div class="form-hint mt-2">Masukkan nominal pembayaran dalam rupiah.</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="payment_method" class="form-label">Metode Bayar</label>
                                <select id="payment_method" name="payment_method" class="form-select @if($errors->billingNote->has('payment_method')) is-invalid @endif" required>
                                    <option value="">Pilih metode bayar</option>
                                    @foreach (['transfer bank', 'cash', 'e-wallet', 'qris', 'lainnya'] as $method)
                                        <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ str($method)->headline() }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->billingNote->has('payment_method'))
                                    <div class="invalid-feedback">{{ $errors->billingNote->first('payment_method') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="period_starts_at" class="form-label">Periode Mulai</label>
                                <input
                                    id="period_starts_at"
                                    name="period_starts_at"
                                    type="date"
                                    class="form-control @if($errors->billingNote->has('period_starts_at')) is-invalid @endif"
                                    value="{{ old('period_starts_at', now()->toDateString()) }}"
                                    required
                                >
                                @if ($errors->billingNote->has('period_starts_at'))
                                    <div class="invalid-feedback">{{ $errors->billingNote->first('period_starts_at') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="period_ends_at" class="form-label">Periode Selesai</label>
                                <input
                                    id="period_ends_at"
                                    name="period_ends_at"
                                    type="date"
                                    class="form-control @if($errors->billingNote->has('period_ends_at')) is-invalid @endif"
                                    value="{{ old('period_ends_at', now()->addMonth()->toDateString()) }}"
                                    required
                                >
                                @if ($errors->billingNote->has('period_ends_at'))
                                    <div class="invalid-feedback">{{ $errors->billingNote->first('period_ends_at') }}</div>
                                @endif
                            </div>

                            <div class="col-12">
                                <label for="admin_note" class="form-label">Catatan Billing</label>
                                <textarea
                                    id="admin_note"
                                    name="admin_note"
                                    rows="3"
                                    class="form-control @if($errors->billingNote->has('admin_note')) is-invalid @endif"
                                    placeholder="Opsional. Contoh: Pembayaran diterima untuk paket basic 3 bulan."
                                >{{ old('admin_note') }}</textarea>
                                @if ($errors->billingNote->has('admin_note'))
                                    <div class="invalid-feedback">{{ $errors->billingNote->first('admin_note') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Billing Note</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if ($errors->billingNote->any())
                document.getElementById('open-create-billing-note-modal')?.click();
            @endif
        });
    </script>
</x-app-layout>
