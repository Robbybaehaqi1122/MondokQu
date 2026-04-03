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
    <body>
        <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>

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
                        @if (session('success'))
                            <div class="toast-container position-fixed top-0 end-0 p-3 auth-toast-container" style="z-index: 1080;">
                                <div class="toast auth-toast auth-toast-success show" role="alert" aria-live="assertive" aria-atomic="true" data-login-toast>
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

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const toastElement = document.querySelector('[data-login-toast]');

                if (toastElement) {
                    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
                    toast.show();
                }
            });
        </script>
    </body>
</html>
