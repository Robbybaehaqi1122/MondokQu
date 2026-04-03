<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Informasi Profil</h3>
            <p class="text-secondary mb-0">Perbarui nama, username, dan email akun Anda.</p>
        </div>
    </div>
    <div class="card-body">
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Nama</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name) }}"
                    required
                    autofocus
                    autocomplete="name"
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="username" class="form-label">Username</label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    class="form-control @error('username') is-invalid @enderror"
                    value="{{ old('username', $user->username) }}"
                    required
                    autocomplete="username"
                >
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-3">
            <label for="email" class="form-label">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $user->email) }}"
                required
                autocomplete="email"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning mt-3 mb-0">
                    <div class="mb-2">Alamat email Anda belum diverifikasi.</div>

                    <button form="send-verification" class="btn btn-warning btn-sm" type="submit">
                        Kirim ulang email verifikasi
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <div class="text-success small mt-2">
                            Link verifikasi baru sudah dikirim ke email Anda.
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="mt-4 d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>

            @if (session('status') === 'profile-updated')
                <span class="text-success">Tersimpan.</span>
            @endif
        </div>
    </form>
    </div>
</div>
