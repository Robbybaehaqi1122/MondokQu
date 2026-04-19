<x-guest-layout>
    <div class="text-center mb-4">
        <h1 class="h2 mb-2">Masuk ke akun Anda</h1>
        <p class="text-secondary mb-0">
            Gunakan username atau email yang sudah dibuatkan oleh superadmin.
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
                <div class="fw-semibold">Login gagal</div>
                <div>{{ $errors->first() }}</div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" id="login-form">
        @csrf

        <div class="mb-3">
            <label for="login" class="form-label">Username / Email</label>
            <div class="input-icon login-field-wrapper">
                <input
                    id="login"
                    type="text"
                    name="login"
                    value="{{ old('login') }}"
                    class="form-control pe-5 @error('login') is-invalid @enderror"
                    placeholder="Masukkan username atau email"
                    required
                    autofocus
                    autocomplete="username"
                >
                <span class="input-icon-addon login-status-icon" id="login-status-icon" hidden>
                    <i class="ti ti-check text-success"></i>
                </span>
            </div>
            <div class="form-hint mt-1" id="login-status-text"></div>
            @error('login')
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
                    autocomplete="current-password"
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
                <span class="input-icon-addon login-status-icon" id="password-status-icon" hidden>
                    <i class="ti ti-check text-success"></i>
                </span>
            </div>
            <div class="form-hint mt-1" id="password-status-text"></div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4 d-flex justify-content-between align-items-center">
            <label class="form-check m-0">
                <input class="form-check-input" type="checkbox" name="remember" value="1">
                <span class="form-check-label">Ingat saya</span>
            </label>
        </div>

        <div class="form-footer">
            <button type="submit" class="btn btn-primary btn-lg w-100 d-inline-flex align-items-center justify-content-center gap-2" id="login-submit-btn">
                <span class="spinner-border spinner-border-sm" id="login-submit-spinner" role="status" aria-hidden="true" hidden></span>
                <span id="login-submit-label">Masuk</span>
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loginForm = document.getElementById('login-form');
            const loginInput = document.getElementById('login');
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('password-toggle');
            const passwordToggleIcon = document.getElementById('password-toggle-icon');
            const loginStatusIcon = document.getElementById('login-status-icon');
            const passwordStatusIcon = document.getElementById('password-status-icon');
            const loginStatusText = document.getElementById('login-status-text');
            const passwordStatusText = document.getElementById('password-status-text');
            const submitButton = document.getElementById('login-submit-btn');
            const submitSpinner = document.getElementById('login-submit-spinner');
            const submitLabel = document.getElementById('login-submit-label');
            let identityTimeout;

            const setLoginState = (state, text = '') => {
                loginInput.classList.remove('is-valid', 'is-invalid');
                loginStatusIcon.hidden = true;
                loginStatusText.textContent = text;
                loginStatusText.className = 'form-hint mt-1';

                if (state === 'valid') {
                    loginInput.classList.add('is-valid');
                    loginStatusIcon.hidden = false;
                    loginStatusText.classList.add('text-success');
                }

                if (state === 'invalid') {
                    loginInput.classList.add('is-invalid');
                    loginStatusText.classList.add('text-danger');
                }
            };

            const setPasswordState = (state, text = '') => {
                passwordInput.classList.remove('is-invalid');
                passwordStatusIcon.hidden = false;
                passwordStatusText.textContent = text;
                passwordStatusText.className = 'form-hint mt-1';

                if (state === 'valid') {
                    passwordStatusText.className = 'form-hint mt-1 text-success';
                }

                if (state === 'invalid') {
                    passwordInput.classList.add('is-invalid');
                    passwordStatusText.className = 'form-hint mt-1 text-danger';
                }
            };

            passwordToggle?.addEventListener('click', () => {
                const showing = passwordInput.type === 'text';

                passwordInput.type = showing ? 'password' : 'text';
                passwordToggle.setAttribute('aria-pressed', String(!showing));
                passwordToggle.setAttribute('aria-label', showing ? 'Tampilkan password' : 'Sembunyikan password');
                passwordToggleIcon.className = showing ? 'ti ti-eye' : 'ti ti-eye-off';
            });

            loginInput?.addEventListener('input', () => {
                clearTimeout(identityTimeout);
                const value = loginInput.value.trim();

                if (value.length < 3) {
                    setLoginState('', '');
                    setPasswordState('', '');
                    return;
                }

                setLoginState('', 'Memeriksa user...');
                setPasswordState('', '');

                identityTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch(`{{ route('login.check-identity') }}?login=${encodeURIComponent(value)}`);
                        const data = await response.json();

                        if (data.state === 'ready') {
                            setLoginState('valid', data.message || 'Lanjutkan dengan memasukkan password Anda.');
                        } else {
                            setLoginState('', data.message || '');
                        }
                    } catch (error) {
                        setLoginState('', '');
                    }
                }, 350);
            });

            passwordInput?.addEventListener('input', () => {
                const login = loginInput.value.trim();
                const password = passwordInput.value;

                if (login.length < 3 || password.trim().length === 0) {
                    setPasswordState('', '');
                    return;
                }

                if (!loginInput.classList.contains('is-valid')) {
                    setPasswordState('invalid', 'Masukkan user yang valid terlebih dahulu.');
                    return;
                }

                setPasswordState('valid', 'Password siap dikirim untuk proses login aman.');
            });

            loginForm?.addEventListener('submit', () => {
                submitButton.disabled = true;
                submitSpinner.hidden = false;
                submitLabel.textContent = 'Memproses...';
            });
        });
    </script>
</x-guest-layout>
