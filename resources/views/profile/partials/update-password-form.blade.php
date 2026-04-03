<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Ganti Password</h3>
            <p class="text-secondary mb-0">Gunakan password yang kuat untuk menjaga keamanan akun.</p>
        </div>
    </div>
    <div class="card-body">
    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="row g-3">
            <div class="col-md-4">
                <label for="update_password_current_password" class="form-label">Password Saat Ini</label>
                <input
                    id="update_password_current_password"
                    name="current_password"
                    type="password"
                    class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif"
                    autocomplete="current-password"
                >
                @if ($errors->updatePassword->has('current_password'))
                    <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
                @endif
            </div>

            <div class="col-md-4">
                <label for="update_password_password" class="form-label">Password Baru</label>
                <input
                    id="update_password_password"
                    name="password"
                    type="password"
                    class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif"
                    autocomplete="new-password"
                >
                @if ($errors->updatePassword->has('password'))
                    <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
                @endif
            </div>

            <div class="col-md-4">
                <label for="update_password_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                <input
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    type="password"
                    class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif"
                    autocomplete="new-password"
                >
                @if ($errors->updatePassword->has('password_confirmation'))
                    <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
                @endif
            </div>
        </div>

        <div class="mt-4 d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">Update Password</button>

            @if (session('status') === 'password-updated')
                <span class="text-success">Password berhasil diperbarui.</span>
            @endif
        </div>
    </form>
    </div>
</div>
