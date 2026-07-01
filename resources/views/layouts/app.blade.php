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
        @if (app()->bound('sentry') && $dsn = config('sentry.dsn'))
            <meta name="sentry-dsn" content="{{ $dsn }}">
            <meta name="sentry-environment" content="{{ app()->environment() }}">
            <meta name="sentry-release" content="{{ config('sentry.release') ?? '' }}">
        @endif

        <title>{{ config('app.ponpes_name', config('app.name', 'Laravel')) }}</title>

        @if ($faviconUrl = config('app.tenant_favicon'))
            <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
            <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
        @else
            <link rel="icon" type="image/svg+xml" href="{{ asset('images/mondok-qu-logo.png') }}">
            <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/mondok-qu-logo.png') }}">
            <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
            <link rel="apple-touch-icon" href="{{ asset('images/mondok-qu-logo.png') }}">
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

                        @if (session('success'))
                            @php
                                $msg = session('success');
                                \Illuminate\Support\Facades\Log::info('flash.view.success', ['message' => substr($msg, 0, 200)]);
                            @endphp
                            <div style="position:fixed;top:1rem;right:1rem;padding:0.75rem 1rem;border-radius:0.5rem;z-index:9999;background:#059669;color:#fff;font-family:sans-serif;font-size:0.875rem;box-shadow:0 4px 12px rgba(0,0,0,0.15);max-width:24rem;display:flex;align-items:center;gap:0.5rem;border:0;">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:1.5rem;height:1.5rem;border-radius:999px;background:rgba(255,255,255,0.2);flex-shrink:0;">&#10003;</span>
                                <span>{{ $msg }}</span>
                                <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;color:#fff;cursor:pointer;font-size:1.25rem;padding:0;line-height:1;">&times;</button>
                            </div>
                        @endif
                        @if (session('error'))
                            @php
                                $msg = session('error');
                                \Illuminate\Support\Facades\Log::warning('flash.view.error', ['message' => substr($msg, 0, 200)]);
                            @endphp
                            <div style="position:fixed;top:1rem;right:1rem;padding:0.75rem 1rem;border-radius:0.5rem;z-index:9999;background:#dc2626;color:#fff;font-family:sans-serif;font-size:0.875rem;box-shadow:0 4px 12px rgba(0,0,0,0.15);max-width:24rem;display:flex;align-items:center;gap:0.5rem;border:0;">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:1.5rem;height:1.5rem;border-radius:999px;background:rgba(255,255,255,0.2);flex-shrink:0;">&#9888;</span>
                                <span>{{ $msg }}</span>
                                <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;color:#fff;cursor:pointer;font-size:1.25rem;padding:0;line-height:1;">&times;</button>
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
