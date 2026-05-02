<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Dashboard Trial</h2>
            <div class="text-secondary mt-1">Anda sedang berada di mode trial lihat saja. Semua data di halaman ini bersifat contoh.</div>
        </div>
    </x-slot>

    @php
        $tenant = auth()->user()?->tenant;
        $trialEndsAt = $tenant?->trial_ends_at?->translatedFormat('d M Y H:i');
    @endphp

    <div class="row row-cards">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Santri Aktif</div>
                    <div class="h1 mb-1">24</div>
                    <div class="text-secondary">Contoh data demo untuk tampilan daftar santri.</div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Pengurus</div>
                    <div class="h1 mb-1">8</div>
                    <div class="text-secondary">Contoh struktur organisasi pondok.</div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Kegiatan</div>
                    <div class="h1 mb-1">5</div>
                    <div class="text-secondary">Contoh jadwal kegiatan demo.</div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Trial Sisa</div>
                    <div class="h1 mb-1">15 Hari</div>
                    <div class="text-secondary">Trial aktif hingga {{ $trialEndsAt ?? '-' }}.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mt-4">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Tampilan Demo</h3>
                    <p class="text-secondary mb-3">
                        Anda sedang menggunakan versi trial lihat saja. Data yang tampil adalah contoh untuk membantu Anda memahami fitur MondokQu.
                    </p>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Lihat status santri, kegiatan, dan ringkasan operasional.</li>
                        <li class="list-group-item">Mode ini tidak dapat digunakan untuk input data riil.</li>
                        <li class="list-group-item">Jika Anda ingin berlangganan, silakan hubungi admin.</li>
                    </ul>

                    <div class="mt-4">
                        <a href="https://wa.me/6285117511220?text=Halo%20admin%20MondokQu,%20saya%20tertarik%20menggunakan%20aplikasi%20MondokQu.%20Mohon%20informasi%20paket%20langganan." target="_blank" class="btn btn-success">
                            Hubungi Admin via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
