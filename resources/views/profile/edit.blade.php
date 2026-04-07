<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Profil</h2>
            <div class="text-secondary mt-1">Kelola informasi akun, password, dan keamanan akun Anda.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        @if (auth()->user()?->password_change_required)
            <div class="col-12">
                <div class="alert alert-warning" role="alert">
                    <div class="fw-semibold mb-1">Ganti password wajib</div>
                    <div class="text-secondary">
                        Akun Anda baru saja direset oleh admin. Sebelum melanjutkan menggunakan aplikasi, silakan ganti password default Anda.
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="col-12">
            @include('profile.partials.update-password-form')
        </div>

        <div class="col-12">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
