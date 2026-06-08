<x-guest-layout>
    <div class="text-center mb-4">
        <h1 class="h2 mb-2">Reset Password</h1>
        <p class="text-secondary mb-0">Masukkan password baru Anda.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-3" role="alert">
            <i class="ti ti-alert-circle fs-3"></i>
            <div>
                <div class="fw-semibold">Reset password gagal</div>
                <div>{{ $errors->first() }}</div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password Baru</label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
        </div>

        <div class="form-footer">
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </div>
    </form>
</x-guest-layout>