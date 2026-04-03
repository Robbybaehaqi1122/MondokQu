<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="d-flex flex-column">
        <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>

        <div class="page page-center min-vh-100 auth-shell">
            <div class="auth-panel">
                <div class="text-center mb-4">
                    <div class="auth-branding text-white">
                        <span class="auth-branding-mark">
                            <img src="{{ asset('images/mondok-qu-logo.png') }}" alt="Logo Mondok Qu" class="auth-brand-image">
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
                    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
                    toast.show();
                });
            });
        </script>
    </body>
</html>
