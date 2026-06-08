<x-guest-layout>
    <div class="text-center mb-4">
        <h1 class="h2 mb-2">Verifikasi Email</h1>
        <p class="text-secondary mb-0">Sebelum melanjutkan, verifikasi email Anda dengan mengklik link yang telah kami kirim.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success d-flex align-items-start gap-3" role="alert">
            <i class="ti ti-circle-check fs-3"></i>
            <div>
                <div class="fw-semibold">Berhasil</div>
                <div>Link verifikasi baru telah dikirim ke email Anda.</div>
            </div>
        </div>
    @endif

    <div class="d-flex flex-column gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary w-100">Kirim Ulang Email Verifikasi</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100">Logout</button>
        </form>
    </div>
</x-guest-layout>