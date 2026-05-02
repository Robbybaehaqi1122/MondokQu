<x-guest-layout>
    <div class="text-center mb-4">
        <h1 class="h2 mb-2">Buat Akun Baru</h1>
        <p class="text-secondary mb-0">
            Daftar untuk mulai menggunakan Mondok Qu.
        </p>
    </div>

    @if (session('status'))
        <div class="alert alert-info auth-inline-alert d-flex align-items-start gap-3" role="alert">
            <i class="ti ti-info-circle fs-3"></i>
            <div>
                <div class="fw-semibold">Informasi</div>
                <div>{{ session('status') }}</div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger auth-inline-alert d-flex align-items-start gap-3" role="alert">
            <i class="ti ti-alert-circle fs-3"></i>
            <div>
                <div class="fw-semibold">Registrasi gagal</div>
                <div>{{ $errors->first() }}</div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" id="register-form">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nama Lengkap</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                class="form-control @error('name') is-invalid @enderror"
                placeholder="Masukkan nama lengkap"
                required
                autofocus
                autocomplete="name"
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input
                id="username"
                type="text"
                name="username"
                value="{{ old('username') }}"
                class="form-control @error('username') is-invalid @enderror"
                placeholder="Masukkan username"
                required
                autocomplete="username"
            >
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="form-control @error('email') is-invalid @enderror"
                placeholder="Masukkan alamat email"
                required
                autocomplete="email"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="phone_number" class="form-label">Nomor Telepon <span class="text-secondary">(Opsional)</span></label>
            <input
                id="phone_number"
                type="tel"
                name="phone_number"
                value="{{ old('phone_number') }}"
                class="form-control @error('phone_number') is-invalid @enderror"
                placeholder="Masukkan nomor telepon"
                autocomplete="tel"
            >
            @error('phone_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="login-field-wrapper login-field-wrapper-password">
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control pe-6 @error('password') is-invalid @enderror"
                    placeholder="Masukkan password"
                    required
                    autocomplete="new-password"
                >
                <button
                    type="button"
                    class="btn btn-icon btn-ghost-secondary login-password-toggle"
                    id="password-toggle"
                    aria-label="Tampilkan password"
                    aria-pressed="false"
                >
                    <i class="ti ti-eye" id="password-toggle-icon"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="form-control @error('password_confirmation') is-invalid @enderror"
                placeholder="Masukkan ulang password"
                required
                autocomplete="new-password"
            >
            @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-footer">
            <button type="submit" class="btn btn-primary btn-lg w-100 d-inline-flex align-items-center justify-content-center gap-2" id="register-submit-btn">
                <span class="spinner-border spinner-border-sm" id="register-submit-spinner" role="status" aria-hidden="true" hidden></span>
                <span id="register-submit-label">Daftar</span>
            </button>
        </div>
    </form>

    <div class="text-center mt-3">
        <p class="text-secondary mb-0">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-primary">Masuk di sini</a>
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const registerForm = document.getElementById('register-form');
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('password-toggle');
            const passwordToggleIcon = document.getElementById('password-toggle-icon');
            const submitButton = document.getElementById('register-submit-btn');
            const submitSpinner = document.getElementById('register-submit-spinner');
            const submitLabel = document.getElementById('register-submit-label');

            passwordToggle?.addEventListener('click', () => {
                const showing = passwordInput.type === 'text';

                passwordInput.type = showing ? 'password' : 'text';
                passwordToggle.setAttribute('aria-pressed', String(!showing));
                passwordToggle.setAttribute('aria-label', showing ? 'Tampilkan password' : 'Sembunyikan password');
                passwordToggleIcon.className = showing ? 'ti ti-eye' : 'ti ti-eye-off';
            });

            registerForm?.addEventListener('submit', () => {
                submitButton.disabled = true;
                submitSpinner.hidden = false;
                submitLabel.textContent = 'Mendaftarkan...';
            });
        });
    </script>
</x-guest-layout>
