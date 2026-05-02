<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Status Akses Tenant</h2>
            <div class="text-secondary mt-1">Akses aplikasi sedang dibatasi berdasarkan status tenant pondok Anda.</div>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            @if (session('error'))
                <div class="alert alert-warning" role="alert">
                    <div class="fw-semibold mb-1">Perlu Perhatian</div>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3 mb-4">
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <h3 class="card-title mb-0">{{ $subscriptionContext['title'] }}</h3>
                                <span class="badge bg-{{ $subscriptionContext['badge_color'] }}-lt text-{{ $subscriptionContext['badge_color'] }}">{{ $subscriptionContext['badge'] }}</span>
                            </div>
                            <p class="text-secondary mb-0 mt-2">{{ $subscriptionContext['description'] }}</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="border rounded p-3 bg-body-secondary">
                                <div class="text-secondary small text-uppercase fw-bold mb-1">Kondisi Saat Ini</div>
                                <div class="fw-semibold">{{ $subscriptionContext['detail'] }}</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="border rounded p-3">
                                <div class="text-secondary small text-uppercase fw-bold mb-1">Tindakan Yang Disarankan</div>
                                <div class="fw-semibold">{{ $subscriptionContext['action'] }}</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="border rounded p-3 bg-success-lt">
                                <div class="text-secondary small text-uppercase fw-bold mb-1">Hubungi Admin</div>
                                <div class="fw-semibold mb-3">Jika Anda tertarik menggunakan MondokQu, silakan chat admin melalui WhatsApp.</div>
                                <a href="https://wa.me/6285117511220?text=Halo%20admin%20MondokQu,%20saya%20ingin%20mengaktifkan%20langganan." target="_blank" class="btn btn-success">
                                    Chat Admin: 085117511220
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
