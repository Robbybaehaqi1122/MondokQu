<x-guest-layout>
    <div class="text-center mb-4">
        <h1 class="h2 mb-2">Lupa Password</h1>
        <p class="text-secondary mb-0">Masukkan email Anda dan kami akan kirim link reset password.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-start gap-3" role="alert">
            <i class="ti ti-circle-check fs-3"></i>
            <div>
                <div class="fw-semibold">Berhasil</div>
                <div>{{ session('status') }}</div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-3" role="alert">
            <i class="ti ti-alert-circle fs-3"></i>
            <div>
                <div class="fw-semibold">Gagal</div>
                <div>{{ $errors->first() }}</div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-footer">
            <button type="submit" class="btn btn-primary w-100">Kirim Link Reset Password</button>
        </div>
    </form>
</x-guest-layout>