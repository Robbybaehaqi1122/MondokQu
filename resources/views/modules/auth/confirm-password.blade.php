<x-guest-layout>
    <div class="text-center mb-4">
        <h1 class="h2 mb-2">Konfirmasi Password</h1>
        <p class="text-secondary mb-0">Konfirmasi password Anda sebelum melanjutkan.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-3" role="alert">
            <i class="ti ti-alert-circle fs-3"></i>
            <div>
                <div class="fw-semibold">Konfirmasi gagal</div>
                <div>{{ $errors->first() }}</div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-footer">
            <button type="submit" class="btn btn-primary w-100">Konfirmasi</button>
        </div>
    </form>
</x-guest-layout>