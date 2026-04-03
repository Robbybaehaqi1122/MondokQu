<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Dashboard Umum</h2>
            <div class="text-secondary mt-1">Akun ini sudah login, tetapi belum memiliki role operasional.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Status Akun</h3>
                    <p class="text-secondary mb-0">
                        Akun Anda berhasil login, tetapi belum diarahkan ke dashboard role tertentu.
                        Pastikan akun ini memiliki role seperti <strong>Admin</strong>, <strong>Pengurus</strong>, atau <strong>Bendahara</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
