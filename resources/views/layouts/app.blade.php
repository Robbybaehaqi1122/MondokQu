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

        <title>{{ config('app.ponpes_name', config('app.name', 'Laravel')) }}</title>

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

        @php
            $themeColor = config('app.tenant_theme_color', '#0d9488');
        @endphp
        <style>
            :root {
                --tblr-primary: {{ $themeColor }};
            }
            .btn-primary, .bg-primary, .badge.bg-primary,
            .page-item.active .page-link,
            .nav-pills .nav-link.active,
            .progress-bar,
            .form-check-input:checked {
                background-color: {{ $themeColor }} !important;
                border-color: {{ $themeColor }} !important;
            }
            .btn-outline-primary {
                color: {{ $themeColor }};
                border-color: {{ $themeColor }};
            }
            .btn-outline-primary:hover {
                background-color: {{ $themeColor }};
                border-color: {{ $themeColor }};
                color: #fff;
            }
            a, .link-primary, .text-primary,
            .sidebar-link.active, .sidebar-sublink.active,
            .sidebar-dropdown[open] > .sidebar-link {
                color: {{ $themeColor }};
            }
            a:hover {
                color: {{ $themeColor }}cc;
            }
            .border-primary {
                border-color: {{ $themeColor }} !important;
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js" onerror="this.onerror=null;this.src='https://unpkg.com/@tabler/core@1.4.0/dist/js/tabler.min.js';"></script>

        <div class="page" id="app-page">
            @include('layouts.navigation')

            <div class="page-wrapper">
                @isset($header)
                    <div class="page-header d-print-none">
                        <div class="container-xl">
                            <div class="row g-2 align-items-center">
                                <div class="col">
                                    {{ $header }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endisset

                <div class="page-body">
                    <div class="container-xl">
                        @if (session('impersonation.impersonator_id'))
                            <div class="alert alert-warning d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3" role="alert">
                                <div>
                                    <div class="fw-semibold">Mode Impersonation Aktif</div>
                                    <div>
                                        Anda sedang login sebagai {{ Auth::user()->name }} untuk tenant {{ session('impersonation.tenant_name') }}.
                                        Sesi ini dimulai oleh {{ session('impersonation.impersonator_name') }}.
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('impersonation.stop') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-warning">
                                        <i class="ti ti-logout me-1"></i>
                                        Kembali ke Superadmin
                                    </button>
                                </form>
                            </div>
                        @endif

                        @if (session('success') || session('error'))
                            <div class="toast-container position-fixed top-0 end-0 p-3 auth-toast-container" style="z-index: 1080;">
                                <div class="toast auth-toast show {{ session('error') ? 'auth-toast-danger' : 'auth-toast-success' }}" role="alert" aria-live="assertive" aria-atomic="true" data-login-toast>
                                    <div class="toast-header border-0">
                                        <span class="auth-toast-icon {{ session('error') ? 'bg-danger-lt text-danger' : 'bg-success-lt text-success' }}">
                                            <i class="ti {{ session('error') ? 'ti-alert-circle' : 'ti-circle-check' }}"></i>
                                        </span>
                                        <strong class="me-auto">Mondok Qu</strong>
                                        <small>Baru saja</small>
                                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                                    </div>
                                    <div class="toast-body">
                                        {{ session('error') ?? session('success') }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        @stack('scripts')

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const toastElement = document.querySelector('[data-login-toast]');
                if (toastElement) {
                    try {
                        const toast = new bootstrap.Toast(toastElement, { delay: 7000 });
                        toast.show();
                    } catch (e) {
                        // Fallback: toast already visible via 'show' class
                    }
                }
            });
        </script>
    </body>
</html>
