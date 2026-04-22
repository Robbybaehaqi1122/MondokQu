<x-app-layout>
    @php
        $statusBadgeClasses = [
            'trial' => 'bg-azure-lt text-azure',
            'active' => 'bg-success-lt text-success',
            'grace' => 'bg-warning-lt text-warning',
            'expired' => 'bg-danger-lt text-danger',
        ];
    @endphp

    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">SaaS</div>
            <h2 class="page-title mt-1">Tenant Management</h2>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 w-100">
                        <div>
                            <h3 class="card-title">Daftar Tenant</h3>
                        </div>

                        <div class="d-flex align-items-center">
                            <button
                                type="button"
                                class="btn btn-primary"
                                id="open-create-tenant-modal"
                                data-bs-toggle="modal"
                                data-bs-target="#createTenantModal"
                            >
                                <i class="ti ti-building-plus me-1"></i>
                                Tambah Tenant
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body border-bottom user-filter-panel">
                    <form method="GET" action="{{ route('saas.tenants.index') }}" class="row g-3 align-items-end user-filter-form">
                            <div class="col-lg-4 col-xl-4">
                                <label for="tenant-search" class="form-label">Cari Tenant</label>
                                <input
                                    id="tenant-search"
                                    name="search"
                                    type="text"
                                    class="form-control"
                                    value="{{ $filters['search'] ?? '' }}"
                                    placeholder="Cari nama pondok, slug, email, atau nomor kontak"
                                >
                            </div>
                            <div class="col-md-6 col-lg-2 col-xl-2">
                                <label for="tenant-status" class="form-label">Status</label>
                                <select id="tenant-status" name="status" class="form-select form-select-pretty">
                                    <option value="">Semua status</option>
                                    <option value="trial" @selected(($filters['status'] ?? '') === 'trial')>Trial</option>
                                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                                    <option value="grace" @selected(($filters['status'] ?? '') === 'grace')>Grace</option>
                                    <option value="expired" @selected(($filters['status'] ?? '') === 'expired')>Expired</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 col-xl-2">
                                <div class="d-flex gap-2 user-filter-actions">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('saas.tenants.index') }}" class="btn btn-outline-secondary">Reset</a>
                                </div>
                            </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Tenant</th>
                                <th>Status</th>
                                <th>User</th>
                                <th>Santri</th>
                                <th>Owner</th>
                                <th>Trial Ends</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tenants as $tenant)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $tenant->name }}</div>
                                        <div class="text-secondary small mt-1">{{ $tenant->slug }}</div>
                                        <div class="text-secondary small mt-1">{{ $tenant->contact_email ?: 'Email kontak belum diisi' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusBadgeClasses[$tenant->subscription_status] ?? 'bg-secondary-lt text-secondary' }}">
                                            {{ str($tenant->subscription_status)->headline() }}
                                        </span>
                                    </td>
                                    <td>{{ $tenant->users_count }}</td>
                                    <td>{{ $tenant->santris_count }}</td>
                                    <td>{{ $tenant->owner?->name ?? 'Belum ada owner' }}</td>
                                    <td>{{ $tenant->trial_ends_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Action
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end p-3 user-action-menu">
                                                <a href="{{ route('saas.tenants.show', $tenant) }}" class="btn btn-outline-secondary btn-sm w-100">
                                                    Detail
                                                </a>

                                                <div class="dropdown-divider my-3"></div>

                                                <button
                                                    type="button"
                                                    class="btn btn-outline-primary btn-sm w-100"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#subscriptionControlModal{{ $tenant->id }}"
                                                >
                                                    Subscription Control
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-secondary">Belum ada tenant yang terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($tenants->hasPages())
                    <div class="card-footer">
                        {{ $tenants->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @foreach ($tenants as $tenant)
        <div class="modal modal-blur fade" id="subscriptionControlModal{{ $tenant->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Subscription Control</h5>
                            <div class="text-secondary small mt-1">{{ $tenant->name }} · {{ $tenant->slug }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @if ($errors->subscriptionControl->any() && (string) old('subscription_tenant_id') === (string) $tenant->id)
                            <div class="alert alert-danger" role="alert">
                                <div class="fw-semibold mb-2">Subscription control belum bisa disimpan.</div>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->subscriptionControl->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('saas.tenants.update-subscription', $tenant) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="subscription_tenant_id" value="{{ $tenant->id }}">
                                    <input type="hidden" name="action" value="activate_trial">
                                    <label for="activate_trial_ends_at_{{ $tenant->id }}" class="form-label">Aktifkan Trial Sampai</label>
                                    <input
                                        id="activate_trial_ends_at_{{ $tenant->id }}"
                                        name="trial_ends_at"
                                        type="datetime-local"
                                        class="form-control @if($errors->subscriptionControl->has('trial_ends_at') && (string) old('subscription_tenant_id') === (string) $tenant->id) is-invalid @endif"
                                        value="{{ old('subscription_tenant_id') == $tenant->id
                                            ? old('trial_ends_at')
                                            : optional($tenant->trial_ends_at?->isFuture() ? $tenant->trial_ends_at : now()->addDays(config('saas.trial_days', 14)))->format('Y-m-d\\TH:i') }}"
                                    >
                                    <label for="admin_note_activate_trial_{{ $tenant->id }}" class="form-label mt-2">Catatan Admin</label>
                                    <textarea
                                        id="admin_note_activate_trial_{{ $tenant->id }}"
                                        name="admin_note"
                                        rows="2"
                                        class="form-control @if($errors->subscriptionControl->has('admin_note') && (string) old('subscription_tenant_id') === (string) $tenant->id) is-invalid @endif"
                                        placeholder="Opsional. Contoh: Trial diberikan karena tenant baru onboarding."
                                    >{{ old('subscription_tenant_id') == $tenant->id ? old('admin_note') : '' }}</textarea>
                                    <button type="submit" class="btn btn-outline-primary w-100 mt-2" onclick="return confirm('Aktifkan ulang trial tenant ini?')">
                                        Aktifkan Trial
                                    </button>
                                </form>
                            </div>

                            <div class="col-md-6">
                                <form method="POST" action="{{ route('saas.tenants.update-subscription', $tenant) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="subscription_tenant_id" value="{{ $tenant->id }}">
                                    <input type="hidden" name="action" value="extend_trial">
                                    <label for="extend_trial_ends_at_{{ $tenant->id }}" class="form-label">Perpanjang Trial Sampai</label>
                                    <input
                                        id="extend_trial_ends_at_{{ $tenant->id }}"
                                        name="trial_ends_at"
                                        type="datetime-local"
                                        class="form-control @if($errors->subscriptionControl->has('trial_ends_at') && (string) old('subscription_tenant_id') === (string) $tenant->id) is-invalid @endif"
                                        value="{{ old('subscription_tenant_id') == $tenant->id
                                            ? old('trial_ends_at')
                                            : optional(($tenant->trial_ends_at && $tenant->trial_ends_at->isFuture() ? $tenant->trial_ends_at : now()->addDays(config('saas.trial_days', 14))))->format('Y-m-d\\TH:i') }}"
                                    >
                                    <label for="admin_note_extend_trial_{{ $tenant->id }}" class="form-label mt-2">Catatan Admin</label>
                                    <textarea
                                        id="admin_note_extend_trial_{{ $tenant->id }}"
                                        name="admin_note"
                                        rows="2"
                                        class="form-control @if($errors->subscriptionControl->has('admin_note') && (string) old('subscription_tenant_id') === (string) $tenant->id) is-invalid @endif"
                                        placeholder="Opsional. Contoh: Perpanjangan trial karena tenant masih evaluasi."
                                    >{{ old('subscription_tenant_id') == $tenant->id ? old('admin_note') : '' }}</textarea>
                                    <button type="submit" class="btn btn-outline-primary w-100 mt-2" onclick="return confirm('Perpanjang trial tenant ini?')">
                                        Perpanjang Trial
                                    </button>
                                </form>
                            </div>

                            <div class="col-md-6">
                                <form method="POST" action="{{ route('saas.tenants.update-subscription', $tenant) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="subscription_tenant_id" value="{{ $tenant->id }}">
                                    <input type="hidden" name="action" value="activate_subscription">
                                    <label for="subscription_duration_{{ $tenant->id }}" class="form-label">Durasi Subscription</label>
                                    <select
                                        id="subscription_duration_{{ $tenant->id }}"
                                        name="subscription_duration"
                                        class="form-select form-select-pretty @if($errors->subscriptionControl->has('subscription_duration') && (string) old('subscription_tenant_id') === (string) $tenant->id) is-invalid @endif"
                                    >
                                        <option value="">Pilih durasi</option>
                                        <option value="1_month" @selected(old('subscription_tenant_id') == $tenant->id && old('subscription_duration') === '1_month')>1 Bulan</option>
                                        <option value="3_months" @selected(old('subscription_tenant_id') == $tenant->id && old('subscription_duration') === '3_months')>3 Bulan</option>
                                        <option value="6_months" @selected(old('subscription_tenant_id') == $tenant->id && old('subscription_duration') === '6_months')>6 Bulan</option>
                                        <option value="12_months" @selected(old('subscription_tenant_id') == $tenant->id && old('subscription_duration') === '12_months')>1 Tahun</option>
                                    </select>
                                    <label for="admin_note_activate_subscription_{{ $tenant->id }}" class="form-label mt-2">Catatan Admin</label>
                                    <textarea
                                        id="admin_note_activate_subscription_{{ $tenant->id }}"
                                        name="admin_note"
                                        rows="2"
                                        class="form-control @if($errors->subscriptionControl->has('admin_note') && (string) old('subscription_tenant_id') === (string) $tenant->id) is-invalid @endif"
                                        placeholder="Opsional. Contoh: Subscription aktif setelah pembayaran manual diterima."
                                    >{{ old('subscription_tenant_id') == $tenant->id ? old('admin_note') : '' }}</textarea>
                                    <button type="submit" class="btn btn-success w-100 mt-2" onclick="return confirm('Aktifkan subscription tenant ini?')">
                                        Aktifkan Subscription
                                    </button>
                                </form>
                            </div>

                            <div class="col-md-6">
                                <form method="POST" action="{{ route('saas.tenants.update-subscription', $tenant) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="subscription_tenant_id" value="{{ $tenant->id }}">
                                    <input type="hidden" name="action" value="mark_grace">
                                    <label for="grace_ends_at_{{ $tenant->id }}" class="form-label">Grace Sampai</label>
                                    <input
                                        id="grace_ends_at_{{ $tenant->id }}"
                                        name="grace_ends_at"
                                        type="datetime-local"
                                        class="form-control @if($errors->subscriptionControl->has('grace_ends_at') && (string) old('subscription_tenant_id') === (string) $tenant->id) is-invalid @endif"
                                        value="{{ old('subscription_tenant_id') == $tenant->id
                                            ? old('grace_ends_at')
                                            : optional(($tenant->grace_ends_at && $tenant->grace_ends_at->isFuture() ? $tenant->grace_ends_at : now()->addDays(config('saas.grace_days', 5))))->format('Y-m-d\\TH:i') }}"
                                    >
                                    <label for="admin_note_mark_grace_{{ $tenant->id }}" class="form-label mt-2">Catatan Admin</label>
                                    <textarea
                                        id="admin_note_mark_grace_{{ $tenant->id }}"
                                        name="admin_note"
                                        rows="2"
                                        class="form-control @if($errors->subscriptionControl->has('admin_note') && (string) old('subscription_tenant_id') === (string) $tenant->id) is-invalid @endif"
                                        placeholder="Opsional. Contoh: Tenant diberi grace sambil menunggu pelunasan."
                                    >{{ old('subscription_tenant_id') == $tenant->id ? old('admin_note') : '' }}</textarea>
                                    <button type="submit" class="btn btn-warning w-100 mt-2" onclick="return confirm('Pindahkan tenant ini ke grace period?')">
                                        Ubah ke Grace
                                    </button>
                                </form>
                            </div>

                            <div class="col-md-6">
                                <form method="POST" action="{{ route('saas.tenants.update-subscription', $tenant) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="subscription_tenant_id" value="{{ $tenant->id }}">
                                    <input type="hidden" name="action" value="mark_expired">
                                    <label class="form-label">Tandai Tenant</label>
                                    <div class="text-secondary small mb-3">Gunakan aksi ini jika akses tenant harus langsung diblokir sekarang.</div>
                                    <label for="admin_note_mark_expired_{{ $tenant->id }}" class="form-label">Catatan Admin</label>
                                    <textarea
                                        id="admin_note_mark_expired_{{ $tenant->id }}"
                                        name="admin_note"
                                        rows="2"
                                        class="form-control @if($errors->subscriptionControl->has('admin_note') && (string) old('subscription_tenant_id') === (string) $tenant->id) is-invalid @endif"
                                        placeholder="Opsional. Contoh: Tenant belum memperpanjang sampai batas akhir grace."
                                    >{{ old('subscription_tenant_id') == $tenant->id ? old('admin_note') : '' }}</textarea>
                                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Tandai tenant ini sebagai expired?')">
                                        Ubah ke Expired
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="modal modal-blur fade" id="createTenantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('saas.tenants.store') }}">
                    @csrf

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Tambah Tenant Baru</h5>
                            <div class="text-secondary small mt-1">Tenant baru akan langsung mendapat masa trial awal sesuai konfigurasi SaaS.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="name" class="form-label">Nama Pondok / Tenant</label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    class="form-control @if($errors->createTenant->has('name')) is-invalid @endif"
                                    value="{{ old('name') }}"
                                    required
                                >
                                @if ($errors->createTenant->has('name'))
                                    <div class="invalid-feedback">{{ $errors->createTenant->first('name') }}</div>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="slug" class="form-label">Slug</label>
                                <input
                                    id="slug"
                                    name="slug"
                                    type="text"
                                    class="form-control @if($errors->createTenant->has('slug')) is-invalid @endif"
                                    value="{{ old('slug') }}"
                                    placeholder="Opsional"
                                >
                                @if ($errors->createTenant->has('slug'))
                                    <div class="invalid-feedback">{{ $errors->createTenant->first('slug') }}</div>
                                @else
                                    <div class="form-hint mt-2">Kalau kosong, slug akan dibuat otomatis dari nama tenant.</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="contact_email" class="form-label">Email Kontak</label>
                                <input
                                    id="contact_email"
                                    name="contact_email"
                                    type="email"
                                    class="form-control @if($errors->createTenant->has('contact_email')) is-invalid @endif"
                                    value="{{ old('contact_email') }}"
                                >
                                @if ($errors->createTenant->has('contact_email'))
                                    <div class="invalid-feedback">{{ $errors->createTenant->first('contact_email') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="contact_phone_number" class="form-label">Nomor Kontak</label>
                                <input
                                    id="contact_phone_number"
                                    name="contact_phone_number"
                                    type="text"
                                    class="form-control @if($errors->createTenant->has('contact_phone_number')) is-invalid @endif"
                                    value="{{ old('contact_phone_number') }}"
                                >
                                @if ($errors->createTenant->has('contact_phone_number'))
                                    <div class="invalid-feedback">{{ $errors->createTenant->first('contact_phone_number') }}</div>
                                @endif
                            </div>

                            <div class="col-12">
                                <label class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="create_owner_account"
                                        id="create_owner_account"
                                        value="1"
                                        @checked(old('create_owner_account'))
                                    >
                                    <span class="form-check-label">Sekalian buat akun owner/admin tenant sekarang</span>
                                </label>
                                <div class="form-hint mt-2">Jika dicentang, sistem akan membuat akun admin tenant dan otomatis memberi role awal <strong>Admin</strong>.</div>
                            </div>

                            <div class="col-12" id="tenant-owner-fields" @if (! old('create_owner_account')) style="display: none;" @endif>
                                <div class="card bg-body-tertiary border-0">
                                    <div class="card-body">
                                        <div class="text-secondary text-uppercase small fw-bold mb-3">Owner / Admin Tenant</div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="owner_name" class="form-label">Nama Admin Tenant</label>
                                                <input
                                                    id="owner_name"
                                                    name="owner_name"
                                                    type="text"
                                                    class="form-control @if($errors->createTenant->has('owner_name')) is-invalid @endif"
                                                    value="{{ old('owner_name') }}"
                                                >
                                                @if ($errors->createTenant->has('owner_name'))
                                                    <div class="invalid-feedback">{{ $errors->createTenant->first('owner_name') }}</div>
                                                @endif
                                            </div>

                                            <div class="col-md-6">
                                                <label for="owner_username" class="form-label">Username Admin Tenant</label>
                                                <input
                                                    id="owner_username"
                                                    name="owner_username"
                                                    type="text"
                                                    class="form-control @if($errors->createTenant->has('owner_username')) is-invalid @endif"
                                                    value="{{ old('owner_username') }}"
                                                >
                                                @if ($errors->createTenant->has('owner_username'))
                                                    <div class="invalid-feedback">{{ $errors->createTenant->first('owner_username') }}</div>
                                                @endif
                                            </div>

                                            <div class="col-md-6">
                                                <label for="owner_email" class="form-label">Email Admin Tenant</label>
                                                <input
                                                    id="owner_email"
                                                    name="owner_email"
                                                    type="email"
                                                    class="form-control @if($errors->createTenant->has('owner_email')) is-invalid @endif"
                                                    value="{{ old('owner_email') }}"
                                                >
                                                @if ($errors->createTenant->has('owner_email'))
                                                    <div class="invalid-feedback">{{ $errors->createTenant->first('owner_email') }}</div>
                                                @endif
                                            </div>

                                            <div class="col-md-6">
                                                <label for="owner_phone_number" class="form-label">No. HP Admin Tenant</label>
                                                <input
                                                    id="owner_phone_number"
                                                    name="owner_phone_number"
                                                    type="text"
                                                    class="form-control @if($errors->createTenant->has('owner_phone_number')) is-invalid @endif"
                                                    value="{{ old('owner_phone_number') }}"
                                                >
                                                @if ($errors->createTenant->has('owner_phone_number'))
                                                    <div class="invalid-feedback">{{ $errors->createTenant->first('owner_phone_number') }}</div>
                                                @endif
                                            </div>

                                            <div class="col-md-6">
                                                <label for="owner_password" class="form-label">Password Awal</label>
                                                <input
                                                    id="owner_password"
                                                    name="owner_password"
                                                    type="password"
                                                    class="form-control @if($errors->createTenant->has('owner_password')) is-invalid @endif"
                                                >
                                                @if ($errors->createTenant->has('owner_password'))
                                                    <div class="invalid-feedback">{{ $errors->createTenant->first('owner_password') }}</div>
                                                @else
                                                    <div class="form-hint mt-2">Minimal 8 karakter.</div>
                                                @endif
                                            </div>

                                            <div class="col-md-6">
                                                <label for="owner_password_confirmation" class="form-label">Konfirmasi Password</label>
                                                <input
                                                    id="owner_password_confirmation"
                                                    name="owner_password_confirmation"
                                                    type="password"
                                                    class="form-control"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Tenant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const createOwnerCheckbox = document.getElementById('create_owner_account');
            const ownerFields = document.getElementById('tenant-owner-fields');

            const syncOwnerFields = () => {
                if (! createOwnerCheckbox || ! ownerFields) {
                    return;
                }

                ownerFields.style.display = createOwnerCheckbox.checked ? '' : 'none';
            };

            createOwnerCheckbox?.addEventListener('change', syncOwnerFields);
            syncOwnerFields();

            @if ($errors->createTenant->any())
                document.getElementById('open-create-tenant-modal')?.click();
            @endif

            @if ($errors->subscriptionControl->any() && old('subscription_tenant_id'))
                const subscriptionModalElement = document.getElementById('subscriptionControlModal{{ old('subscription_tenant_id') }}');

                if (subscriptionModalElement && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(subscriptionModalElement).show();
                }
            @endif
        });
    </script>
</x-app-layout>
