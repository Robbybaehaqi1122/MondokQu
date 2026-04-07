<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Dashboard Admin</h2>
            <div class="text-secondary mt-1">Ringkasan kontrol sistem, user, dan hak akses.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary">Role Aktif</div>
                    <div class="display-6">{{ Auth::user()->getRoleNames()->implode(', ') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary">Navigasi Modul</div>
                    <p class="mb-0 mt-2">
                        Gunakan sidebar di sebelah kiri untuk membuka modul
                        <strong>Autentikasi</strong>, lalu pilih submenu <strong>Manajemen User</strong>.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary">Catatan</div>
                    <p class="mb-0 mt-2">Superadmin mengelola user, role, permission, dan pengaturan sistem. Admin fokus pada data operasional harian.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
