<div class="card border-danger">
    <div class="card-header">
        <div>
            <h3 class="card-title text-danger">Hapus Akun</h3>
            <p class="text-secondary mb-0">Tindakan ini permanen dan tidak bisa dibatalkan.</p>
        </div>
    </div>
    <div class="card-body">
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <p class="text-secondary">
                Semua resource dan data yang terkait dengan akun ini akan dihapus permanen.
                Masukkan password untuk mengonfirmasi penghapusan akun.
            </p>

            <div class="mb-3">
                <label for="delete_password" class="form-label">Password</label>
                <input
                    id="delete_password"
                    name="password"
                    type="password"
                    class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif"
                    placeholder="Masukkan password Anda"
                >
                @if ($errors->userDeletion->has('password'))
                    <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                @endif
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger">Hapus Akun</button>
            </div>
        </form>
    </div>
</div>
