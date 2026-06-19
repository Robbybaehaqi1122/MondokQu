<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h2 class="page-title">Akun Pembayaran</h2>
                <div class="text-secondary mt-1">Kelola rekening bank, e-wallet, dan QRIS untuk pembayaran santri.</div>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                <i class="ti ti-plus"></i> Tambah Akun
            </button>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($accounts->where('type', 'qris')->isEmpty() || $accounts->whereIn('type', ['bank', 'e_wallet'])->isEmpty())
        <div class="alert alert-info">
            <i class="ti ti-info-circle"></i>
            Tambahkan setidaknya satu akun bank/e-wallet untuk metode TRANSFER dan satu akun QRIS untuk metode QRIS.
        </div>
    @endif

    <div class="row row-cards">
        @forelse ($accounts as $account)
            <div class="col-sm-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge {{ $account->type === 'bank' ? 'bg-primary' : ($account->type === 'e_wallet' ? 'bg-success' : 'bg-info') }}">
                                {{ $account->type === 'bank' ? 'Bank' : ($account->type === 'e_wallet' ? 'E-Wallet' : 'QRIS') }}
                            </span>
                            @if (! $account->is_active)
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </div>
                        <h5 class="card-title mb-1">{{ $account->name }}</h5>
                        @if ($account->bank_name)
                            <div class="text-secondary small">{{ $account->bank_name }}</div>
                        @endif
                        @if ($account->account_name)
                            <div class="text-secondary small">{{ $account->account_name }}</div>
                        @endif
                        @if ($account->account_number)
                            <div class="fw-bold mt-1">{{ $account->account_number }}</div>
                        @endif
                        @if ($account->type === 'qris' && $account->qrisImageUrl())
                            <img src="{{ $account->qrisImageUrl() }}" alt="QRIS" class="img-fluid mt-2" style="max-height:150px">
                        @endif
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="modal"
                            data-bs-target="#editAccountModal{{ $account->id }}">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('santri.payments.accounts.destroy', $account) }}"
                            onsubmit="return confirm('Hapus akun ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Edit Modal --}}
            <div class="modal modal-blur fade" id="editAccountModal{{ $account->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('santri.payments.accounts.update', $account) }}" enctype="multipart/form-data">
                            @csrf @method('PATCH')
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Akun</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label required">Nama Akun</label>
                                        <input type="text" name="name" class="form-control" value="{{ $account->name }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label required">Tipe</label>
                                        <select name="type" class="form-select" required>
                                            <option value="bank" @selected($account->type === 'bank')>Bank</option>
                                            <option value="e_wallet" @selected($account->type === 'e_wallet')>E-Wallet</option>
                                            <option value="qris" @selected($account->type === 'qris')>QRIS</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Pemilik</label>
                                        <input type="text" name="account_name" class="form-control" value="{{ $account->account_name }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nomor Rekening</label>
                                        <input type="text" name="account_number" class="form-control" value="{{ $account->account_number }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Bank</label>
                                        <input type="text" name="bank_name" class="form-control" value="{{ $account->bank_name }}" placeholder="Contoh: BCA, Mandiri">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Gambar QRIS</label>
                                        <input type="file" name="qris_image" class="form-control" accept="image/png,image/jpg,image/jpeg">
                                        @if ($account->qrisImageUrl())
                                            <div class="mt-2"><img src="{{ $account->qrisImageUrl() }}" alt="QRIS" style="max-height:80px"></div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="hidden" name="is_active" value="0">
                                            <input type="checkbox" name="is_active" class="form-check-input" value="1" id="is_active_{{ $account->id }}" @checked($account->is_active)>
                                            <label class="form-check-label" for="is_active_{{ $account->id }}">Aktif</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Urutan</label>
                                        <input type="number" name="sort_order" class="form-control" value="{{ $account->sort_order }}" min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="text-secondary">Belum ada akun pembayaran.</div>
                        <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                            Tambah Akun Pertama
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Create Modal --}}
    <div class="modal modal-blur fade" id="createAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('santri.payments.accounts.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Akun Pembayaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label required">Nama Akun</label>
                                <input type="text" name="name" class="form-control" required placeholder="Contoh: BCA Pondok, DANA Ustadz">
                            </div>
                            <div class="col-12">
                                <label class="form-label required">Tipe</label>
                                <select name="type" class="form-select" required>
                                    <option value="">Pilih tipe</option>
                                    <option value="bank">Bank</option>
                                    <option value="e_wallet">E-Wallet</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Pemilik</label>
                                <input type="text" name="account_name" class="form-control" placeholder="Atas nama">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Rekening</label>
                                <input type="text" name="account_number" class="form-control" placeholder="Nomor rekening/ID">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Bank</label>
                                <input type="text" name="bank_name" class="form-control" placeholder="Contoh: BCA, Mandiri">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gambar QRIS</label>
                                <input type="file" name="qris_image" class="form-control" accept="image/png,image/jpg,image/jpeg">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" class="form-check-input" value="1" id="create_is_active" checked>
                                    <label class="form-check-label" for="create_is_active">Aktif</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Urutan</label>
                                <input type="number" name="sort_order" class="form-control" value="0" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
