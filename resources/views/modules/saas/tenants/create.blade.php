<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">Pengaturan Sistem</div>
            <h2 class="page-title mt-1">Tambah Tenant Baru (Wizard)</h2>
            <div class="text-secondary mt-2">Isi data pondok secara bertahap melalui 3 langkah mudah.</div>
        </div>
    </x-slot>

    <form
        method="POST"
        action="{{ route('saas.tenants.wizard-store') }}"
        enctype="multipart/form-data"
        x-data="tenantWizard()"
        x-init="init()"
    >
        @csrf

        {{-- Step Indicators --}}
        <div class="mb-4">
            <div class="steps steps-counter d-flex justify-content-center">
                <template x-for="(step, index) in steps" :key="index">
                    <a
                        href="#"
                        class="step-item"
                        :class="{
                            'active': currentStep === index,
                            'step-completed': currentStep > index
                        }"
                        @click.prevent="goToStep(index)"
                        style="cursor: pointer;"
                    >
                        <div class="step-item-content">
                            <div class="step-item-title" x-text="step"></div>
                        </div>
                    </a>
                </template>
            </div>
        </div>

        {{-- Validation Errors Summary --}}
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Step 1: Data Pondok --}}
        <div class="card" x-show="currentStep === 0" x-cloak>
            <div class="card-header">
                <h3 class="card-title">Langkah 1: Data Pondok</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="name" class="form-label required">Nama Pondok / Tenant</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            required
                            x-model="form.name"
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="slug" class="form-label">Slug</label>
                        <input
                            id="slug"
                            name="slug"
                            type="text"
                            class="form-control @error('slug') is-invalid @enderror"
                            value="{{ old('slug') }}"
                            placeholder="Otomatis"
                            x-model="form.slug"
                        >
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-hint mt-2">Biarkan kosong untuk generate otomatis.</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="category" class="form-label">Kategori Pondok</label>
                        <select
                            id="category"
                            name="category"
                            class="form-select @error('category') is-invalid @enderror"
                            x-model="form.category"
                        >
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="contact_email" class="form-label">Email Kontak</label>
                        <input
                            id="contact_email"
                            name="contact_email"
                            type="email"
                            class="form-control @error('contact_email') is-invalid @enderror"
                            value="{{ old('contact_email') }}"
                            x-model="form.contact_email"
                        >
                        @error('contact_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="contact_phone_number" class="form-label">Nomor Telepon</label>
                        <input
                            id="contact_phone_number"
                            name="contact_phone_number"
                            type="text"
                            class="form-control @error('contact_phone_number') is-invalid @enderror"
                            value="{{ old('contact_phone_number') }}"
                            x-model="form.contact_phone_number"
                        >
                        @error('contact_phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label">Alamat Pondok</label>
                        <textarea
                            id="address"
                            name="address"
                            class="form-control @error('address') is-invalid @enderror"
                            rows="2"
                            x-model="form.address"
                        >{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="logo" class="form-label">Logo Pondok</label>
                        <input
                            id="logo"
                            name="logo"
                            type="file"
                            accept="image/png,image/jpg,image/jpeg,image/svg+xml"
                            class="form-control @error('logo') is-invalid @enderror"
                            @change="previewLogo($event)"
                        >
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-hint mt-2">Format PNG, JPG, SVG. Maks 2 MB.</div>
                        @enderror
                        <div class="mt-2" x-show="logoPreview" x-cloak>
                            <img :src="logoPreview" class="img-thumbnail" style="max-height: 80px;" alt="Preview logo">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Kapasitas Awal</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <label for="max_users" class="form-label small">Max User</label>
                                <input
                                    id="max_users"
                                    name="max_users"
                                    type="number"
                                    min="1"
                                    class="form-control @error('max_users') is-invalid @enderror"
                                    value="{{ old('max_users', $defaultLimits['max_users']) }}"
                                    x-model="form.max_users"
                                >
                                @error('max_users')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4">
                                <label for="max_santri" class="form-label small">Max Santri</label>
                                <input
                                    id="max_santri"
                                    name="max_santri"
                                    type="number"
                                    min="1"
                                    class="form-control @error('max_santri') is-invalid @enderror"
                                    value="{{ old('max_santri', $defaultLimits['max_santri']) }}"
                                    x-model="form.max_santri"
                                >
                                @error('max_santri')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4">
                                <label for="max_storage_mb" class="form-label small">Storage (MB)</label>
                                <input
                                    id="max_storage_mb"
                                    name="max_storage_mb"
                                    type="number"
                                    min="1"
                                    class="form-control @error('max_storage_mb') is-invalid @enderror"
                                    value="{{ old('max_storage_mb', $defaultLimits['max_storage_mb']) }}"
                                    x-model="form.max_storage_mb"
                                >
                                @error('max_storage_mb')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: Admin Tenant --}}
        <div class="card" x-show="currentStep === 1" x-cloak>
            <div class="card-header">
                <h3 class="card-title">Langkah 2: Admin Tenant</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-1"></i>
                    Kosongkan jika ingin membuat tenant tanpa akun admin. Admin bisa ditambahkan nanti.
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="owner_name" class="form-label">Nama Admin</label>
                        <input
                            id="owner_name"
                            name="owner_name"
                            type="text"
                            class="form-control @error('owner_name') is-invalid @enderror"
                            value="{{ old('owner_name') }}"
                            x-model="form.owner_name"
                        >
                        @error('owner_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="owner_username" class="form-label">Username</label>
                        <input
                            id="owner_username"
                            name="owner_username"
                            type="text"
                            class="form-control @error('owner_username') is-invalid @enderror"
                            value="{{ old('owner_username') }}"
                            x-model="form.owner_username"
                        >
                        @error('owner_username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="owner_email" class="form-label">Email</label>
                        <input
                            id="owner_email"
                            name="owner_email"
                            type="email"
                            class="form-control @error('owner_email') is-invalid @enderror"
                            value="{{ old('owner_email') }}"
                            x-model="form.owner_email"
                        >
                        @error('owner_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="owner_phone_number" class="form-label">No. HP</label>
                        <input
                            id="owner_phone_number"
                            name="owner_phone_number"
                            type="text"
                            class="form-control @error('owner_phone_number') is-invalid @enderror"
                            value="{{ old('owner_phone_number') }}"
                            x-model="form.owner_phone_number"
                        >
                        @error('owner_phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="owner_password" class="form-label">Password</label>
                        <input
                            id="owner_password"
                            name="owner_password"
                            type="password"
                            class="form-control @error('owner_password') is-invalid @enderror"
                            x-model="form.owner_password"
                        >
                        @error('owner_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-hint mt-2">Minimal 8 karakter.</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="owner_password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input
                            id="owner_password_confirmation"
                            name="owner_password_confirmation"
                            type="password"
                            class="form-control"
                            x-model="form.owner_password_confirmation"
                        >
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Modul Aktif --}}
        <div class="card" x-show="currentStep === 2" x-cloak>
            <div class="card-header">
                <h3 class="card-title">Langkah 3: Modul Aktif</h3>
            </div>
            <div class="card-body">
                <p class="text-secondary mb-3">Pilih modul yang ingin diaktifkan untuk tenant ini. Modul yang tidak dipilih tetap bisa diaktifkan nanti melalui manajemen permission.</p>
                <div class="row g-3">
                    @foreach ($modules as $module)
                        <div class="col-md-4">
                            <label class="form-check card card-sm p-3 border-0 bg-body-tertiary" style="cursor: pointer;">
                                <div class="d-flex align-items-center gap-3">
                                    <input
                                        type="checkbox"
                                        name="active_modules[]"
                                        value="{{ $module['key'] }}"
                                        class="form-check-input"
                                        @checked(in_array($module['key'], old('active_modules', $defaultActiveModules)))
                                        x-model="form.active_modules"
                                    >
                                    <div>
                                        <i class="ti {{ $module['icon'] }} me-1"></i>
                                        <span>{{ $module['label'] }}</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('active_modules')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Step 4: Review --}}
        <div class="card" x-show="currentStep === 3" x-cloak>
            <div class="card-header">
                <h3 class="card-title">Review Data Tenant</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <i class="ti ti-checks me-1"></i>
                    Silakan periksa kembali data di bawah sebelum menyimpan.
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card bg-body-tertiary border-0">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Data Pondok</h5>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4 text-secondary">Nama</dt>
                                    <dd class="col-sm-8" x-text="form.name || '-'"></dd>
                                    <dt class="col-sm-4 text-secondary">Slug</dt>
                                    <dd class="col-sm-8" x-text="form.slug || '(otomatis)'"></dd>
                                    <dt class="col-sm-4 text-secondary">Kategori</dt>
                                    <dd class="col-sm-8" x-text="form.category || '-'"></dd>
                                    <dt class="col-sm-4 text-secondary">Email</dt>
                                    <dd class="col-sm-8" x-text="form.contact_email || '-'"></dd>
                                    <dt class="col-sm-4 text-secondary">Telepon</dt>
                                    <dd class="col-sm-8" x-text="form.contact_phone_number || '-'"></dd>
                                    <dt class="col-sm-4 text-secondary">Alamat</dt>
                                    <dd class="col-sm-8" x-text="form.address || '-'"></dd>
                                    <dt class="col-sm-4 text-secondary">Logo</dt>
                                    <dd class="col-sm-8">
                                        <template x-if="logoPreview">
                                            <img :src="logoPreview" class="img-thumbnail" style="max-height: 60px;">
                                        </template>
                                        <template x-if="!logoPreview">
                                            <span>(tidak ada)</span>
                                        </template>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-body-tertiary border-0">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Kapasitas</h5>
                                <dl class="row mb-0">
                                    <dt class="col-sm-5 text-secondary">Max User</dt>
                                    <dd class="col-sm-7" x-text="form.max_users"></dd>
                                    <dt class="col-sm-5 text-secondary">Max Santri</dt>
                                    <dd class="col-sm-7" x-text="form.max_santri"></dd>
                                    <dt class="col-sm-5 text-secondary">Storage</dt>
                                    <dd class="col-sm-7" x-text="form.max_storage_mb + ' MB'"></dd>
                                </dl>
                            </div>
                        </div>

                        <div class="card bg-body-tertiary border-0 mt-3">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Admin Tenant</h5>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4 text-secondary">Nama</dt>
                                    <dd class="col-sm-8" x-text="form.owner_name || '(tidak dibuat)'"></dd>
                                    <dt class="col-sm-4 text-secondary">Username</dt>
                                    <dd class="col-sm-8" x-text="form.owner_username || '-'"></dd>
                                    <dt class="col-sm-4 text-secondary">Email</dt>
                                    <dd class="col-sm-8" x-text="form.owner_email || '-'"></dd>
                                </dl>
                            </div>
                        </div>

                        <div class="card bg-body-tertiary border-0 mt-3">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Modul Aktif</h5>
                                <div x-show="form.active_modules.length > 0">
                                    <template x-for="mod in form.active_modules" :key="mod">
                                        <span class="badge bg-primary me-1 mb-1" x-text="mod.charAt(0).toUpperCase() + mod.slice(1)"></span>
                                    </template>
                                </div>
                                <div x-show="form.active_modules.length === 0">
                                    <span class="text-secondary">(tidak ada modul dipilih)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation Buttons --}}
        <div class="d-flex justify-content-between mt-4">
            <div>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    x-show="currentStep > 0"
                    @click="prevStep"
                >
                    <i class="ti ti-arrow-left me-1"></i>Sebelumnya
                </button>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('saas.tenants.index') }}" class="btn btn-link link-secondary">Batal</a>
                <button
                    type="button"
                    class="btn btn-primary"
                    x-show="currentStep < 3"
                    @click="nextStep"
                >
                    Selanjutnya<i class="ti ti-arrow-right ms-1"></i>
                </button>
                <button
                    type="submit"
                    class="btn btn-success"
                    x-show="currentStep === 3"
                >
                    <i class="ti ti-device-floppy me-1"></i>Simpan Tenant
                </button>
            </div>
        </div>
    </form>

    @push('scripts')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <script>
        function tenantWizard() {
            return {
                currentStep: 0,
                steps: ['Data Pondok', 'Admin Tenant', 'Modul Aktif', 'Review'],
                form: {
                    name: '{{ old('name', '') }}',
                    slug: '{{ old('slug', '') }}',
                    category: '{{ old('category', '') }}',
                    contact_email: '{{ old('contact_email', '') }}',
                    contact_phone_number: '{{ old('contact_phone_number', '') }}',
                    address: '{{ old('address', '') }}',
                    max_users: '{{ old('max_users', $defaultLimits['max_users']) }}',
                    max_santri: '{{ old('max_santri', $defaultLimits['max_santri']) }}',
                    max_storage_mb: '{{ old('max_storage_mb', $defaultLimits['max_storage_mb']) }}',
                    owner_name: '{{ old('owner_name', '') }}',
                    owner_username: '{{ old('owner_username', '') }}',
                    owner_email: '{{ old('owner_email', '') }}',
                    owner_phone_number: '{{ old('owner_phone_number', '') }}',
                    owner_password: '',
                    owner_password_confirmation: '',
                    active_modules: @js(old('active_modules', $defaultActiveModules)),
                },
                logoPreview: null,

                init() {
                    @if ($errors->any())
                        const stepErrors = @js($errors->keys());
                        if (stepErrors.some(e => ['name', 'slug', 'category', 'contact_email', 'contact_phone_number', 'address', 'logo', 'max_users', 'max_santri', 'max_storage_mb'].includes(e))) {
                            this.currentStep = 0;
                        } else if (stepErrors.some(e => e.startsWith('owner_'))) {
                            this.currentStep = 1;
                        } else if (stepErrors.some(e => e.startsWith('active_modules'))) {
                            this.currentStep = 2;
                        }
                    @endif
                },

                previewLogo(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.logoPreview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                validateStep(step) {
                    if (step === 0) {
                        if (!this.form.name.trim()) {
                            alert('Nama pondok wajib diisi.');
                            return false;
                        }
                    }
                    if (step === 1) {
                        if (this.form.owner_email && !this.form.owner_name.trim()) {
                            alert('Nama admin wajib diisi jika mengisi email.');
                            return false;
                        }
                        if (this.form.owner_email && !this.form.owner_username.trim()) {
                            alert('Username admin wajib diisi jika mengisi email.');
                            return false;
                        }
                        if (this.form.owner_email && !this.form.owner_password) {
                            alert('Password admin wajib diisi jika mengisi email.');
                            return false;
                        }
                        if (this.form.owner_password && this.form.owner_password.length < 8) {
                            alert('Password minimal 8 karakter.');
                            return false;
                        }
                        if (this.form.owner_password !== this.form.owner_password_confirmation) {
                            alert('Konfirmasi password tidak cocok.');
                            return false;
                        }
                    }
                    return true;
                },

                nextStep() {
                    if (this.validateStep(this.currentStep)) {
                        this.currentStep = Math.min(this.currentStep + 1, 3);
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                prevStep() {
                    this.currentStep = Math.max(this.currentStep - 1, 0);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                goToStep(index) {
                    if (index < this.currentStep) {
                        this.currentStep = index;
                        return;
                    }
                    for (let i = this.currentStep; i < index; i++) {
                        if (!this.validateStep(i)) return;
                    }
                    this.currentStep = index;
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
