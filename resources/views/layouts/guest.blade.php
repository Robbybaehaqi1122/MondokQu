<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <script>
            try {
                var theme = localStorage.getItem('mondok-qu.theme');
                if (!theme) {
                    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                document.documentElement.setAttribute('data-bs-theme', theme);
            } catch (e) {}
        </script>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @if ($faviconUrl = config('app.tenant_favicon'))
            <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
        @else
            <link rel="icon" href="{{ asset('favicon.ico') }}">
        @endif

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.44.0/dist/tabler-icons.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="d-flex flex-column">
        <div class="page page-center min-vh-100 auth-shell">
            <div class="auth-panel">
                <div class="text-center mb-4">
                    <div class="auth-branding text-white">
                        <span class="auth-branding-mark">
                            <img src="{{ asset('images/mondok-qu-logo.png') }}" alt="Logo Mondok Qu" class="auth-brand-image" loading="lazy">
                        </span>
                        <span class="auth-branding-copy">
                            <span class="auth-brand">Mondok Qu</span>
                        </span>
                    </div>
                </div>

                <div class="card shadow-lg border-0 auth-card">
                    <div class="card-body p-4 p-sm-5">
                        {{ $slot }}
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <small class="text-white">powerby @2026 Erby Lintas Inovasi</small>
            </div>
        </div>

        @if (session('success'))
            <div class="toast-container position-fixed top-0 end-0 p-3 auth-toast-container" style="z-index: 1080;">
                <div class="toast auth-toast auth-toast-success show" role="alert" aria-live="assertive" aria-atomic="true" data-auth-toast>
                    <div class="toast-header border-0">
                        <span class="auth-toast-icon bg-success-lt text-success">
                            <i class="ti ti-circle-check"></i>
                        </span>
                        <strong class="me-auto">Mondok Qu</strong>
                        <small>Baru saja</small>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        {{ session('success') }}
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="toast-container position-fixed top-0 end-0 p-3 auth-toast-container" style="z-index: 1080;">
                <div class="toast auth-toast auth-toast-danger show" role="alert" aria-live="assertive" aria-atomic="true" data-auth-toast>
                    <div class="toast-header border-0">
                        <span class="auth-toast-icon bg-danger-lt text-danger">
                            <i class="ti ti-alert-circle"></i>
                        </span>
                        <strong class="me-auto">Login Gagal</strong>
                        <small>Baru saja</small>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        {{ $errors->first() }}
                    </div>
                </div>
            </div>
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-auth-toast]').forEach((toastElement) => {
                    try {
                        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
                        toast.show();
                    } catch (e) {
                        // Toast already visible via 'show' class
                    }
                });
            });
        </script>
    </body>
</html>
