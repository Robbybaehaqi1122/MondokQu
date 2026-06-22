<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} — 419 Session Expired</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
        @vite(['resources/css/app.css'])
    </head>
    <body class="d-flex flex-column">
        <div class="page page-center min-vh-100 auth-shell">
            <div class="auth-panel">
                <div class="text-center">
                    <div class="auth-branding text-white justify-content-center mb-4">
                        <span class="auth-branding-mark">
                            <img src="{{ asset('images/mondok-qu-logo.png') }}" alt="Logo Mondok Qu" class="auth-brand-image" loading="lazy">
                        </span>
                        <span class="auth-branding-copy">
                            <span class="auth-brand">Mondok Qu</span>
                        </span>
                    </div>

                    <div class="card shadow-lg border-0">
                        <div class="card-body p-4 p-sm-5 text-center">
                            <div class="mb-3" style="font-size: 5rem; font-weight: 800; line-height: 1; color: var(--tblr-warning);">419</div>
                            <h2 class="mb-2">Sesi Berakhir</h2>
                            <p class="text-secondary mb-4">Sesi Anda telah kedaluwarsa. Silakan muat ulang halaman dan coba lagi.</p>
                            <a href="{{ url('/') }}" class="btn btn-primary">
                                <i class="ti ti-arrow-left me-2"></i>Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
