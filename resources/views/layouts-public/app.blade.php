<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <script>
        try {
            var _t = localStorage.getItem('mq-theme') || (matchMedia('(prefers-color-scheme:dark)').matches ? 'dark' : 'light');
            document.documentElement.className = _t === 'dark' ? 'dark' : '';
        } catch(e){}
    </script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Primary meta --}}
    <title>@yield('title', config('app.ponpes_name', 'MondokQu') . ' - Manajemen Pondok Pesantren')</title>
    <meta name="description" content="@yield('meta_description', 'Sistem aplikasi manajemen pondok pesantren untuk mengelola santri, keuangan, absensi, dan komunikasi wali.')">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="{{ url()->current() }}">
    <meta property="og:site_name"   content="{{ config('app.ponpes_name', 'MondokQu') }}">
    <meta property="og:title"       content="@yield('og_title', config('app.ponpes_name', 'MondokQu') . ' - Manajemen Pondok Pesantren')">
    <meta property="og:description" content="@yield('og_description', 'Sistem aplikasi manajemen pondok pesantren untuk mengelola santri, keuangan, absensi, dan komunikasi wali.')">
    <meta property="og:image"       content="@yield('og_image', asset('images/og-cover.png'))">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale"      content="id_ID">

    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="@yield('og_title', config('app.ponpes_name', 'MondokQu') . ' - Manajemen Pondok Pesantren')">
    <meta name="twitter:description" content="@yield('og_description', 'Sistem aplikasi manajemen pondok pesantren untuk mengelola santri, keuangan, absensi, dan komunikasi wali.')">
    <meta name="twitter:image"       content="@yield('og_image', asset('images/og-cover.png'))">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/mondok-qu-logo.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('images/mondok-qu-logo.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.44.0/dist/tabler-icons.min.css">

    {{-- Typed.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.1.0/dist/typed.umd.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php $accent = config('app.tenant_theme_color', '#0d9488'); @endphp

    <style>
    /* ── Design Tokens (runtime-dynamic, cannot be replaced by Tailwind) ── */
    :root {
        --c-accent:      {{ $accent }};
        --c-accent-d:    color-mix(in srgb, {{ $accent }} 80%, #000 20%);
        --c-accent-l:    color-mix(in srgb, {{ $accent }} 10%, #fff);
        --c-accent-ring: color-mix(in srgb, {{ $accent }} 25%, transparent);

        --c-ink:   #0c0e12;
        --c-ink-2: #374151;
        --c-ink-3: #6b7280;
        --c-ink-4: #9ca3af;

        --c-bg:   #ffffff;
        --c-bg-2: #f7f8fa;
        --c-bg-3: #f0f2f5;
        --c-line: #e4e7eb;

        --f-sans:    'Inter', system-ui, sans-serif;
        --f-display: 'Sora', 'Inter', system-ui, sans-serif;

        --nav-h:   60px;
        --r-sm:    8px;
        --r-md:    12px;
        --r-lg:    18px;
        --r-xl:    24px;

        --shadow-sm: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
        --shadow-md: 0 4px 12px rgba(0,0,0,.08), 0 2px 4px rgba(0,0,0,.04);
    }

    html.dark {
        --c-ink:   #f1f5f9;
        --c-ink-2: #cbd5e1;
        --c-ink-3: #94a3b8;
        --c-ink-4: #64748b;
        --c-bg:    #0d1117;
        --c-bg-2:  #161b22;
        --c-bg-3:  #1c2333;
        --c-line:  #21262d;
    }

    /* ── Base resets not in Tailwind preflight ── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; font-size: 16px; }

    body {
        font-family: var(--f-sans);
        color: var(--c-ink);
        background: var(--c-bg);
        -webkit-font-smoothing: antialiased;
        line-height: 1.6;
    }

    a { color: inherit; text-decoration: none; }
    img { display: block; max-width: 100%; }
    button { font-family: inherit; }

    /* ── Navbar: backdrop-filter + CSS-var colors cannot be done with Tailwind alone ── */
    .nav {
        background: rgba(255,255,255,.88);
        backdrop-filter: saturate(180%) blur(20px);
        -webkit-backdrop-filter: saturate(180%) blur(20px);
        transition: box-shadow .2s;
    }
    html.dark .nav { background: rgba(13,17,23,.88); }
    .nav.scrolled { box-shadow: var(--shadow-sm); }

    /* ── Shared button tokens (accent color is dynamic) ── */
    .btn-primary  { background: var(--c-accent); color: #fff; border-color: var(--c-accent); }
    .btn-primary:hover { background: var(--c-accent-d); border-color: var(--c-accent-d); }
    .btn-outline  { color: var(--c-accent); border-color: var(--c-accent-ring); }
    .btn-outline:hover { background: var(--c-accent-l); }

    /* ── Mobile nav drawer ── */
    .mobile-nav { background: var(--c-bg); border-top: 1px solid var(--c-line); }
    .mobile-nav a:hover { background: var(--c-bg-3); }

    /* ── Nav link active state ── */
    .nav-link.is-active {
        color: var(--c-accent) !important;
        background: var(--c-accent-l) !important;
        font-weight: 600;
    }
    .mob-link.is-active {
        color: var(--c-accent) !important;
        background: var(--c-accent-l) !important;
    }

    /* ── Nav dropdown ── */
    .nav-dropdown { position: relative; }
    .nav-dropdown-menu {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        min-width: 180px;
        background: var(--c-bg);
        border: 1px solid var(--c-line);
        border-radius: 12px;
        padding: 6px;
        box-shadow: 0 8px 24px rgba(0,0,0,.10), 0 2px 6px rgba(0,0,0,.06);
        z-index: 300;
    }
    .nav-dropdown:hover .nav-dropdown-menu,
    .nav-dropdown.is-open .nav-dropdown-menu { display: block; }
    .nav-dropdown-menu a {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .5rem .75rem;
        font-size: .84rem;
        font-weight: 500;
        color: var(--c-ink-3);
        border-radius: 8px;
        transition: background .15s, color .15s;
        white-space: nowrap;
        text-decoration: none;
    }
    .nav-dropdown-menu a:hover,
    .nav-dropdown-menu a.is-active {
        background: var(--c-accent-l);
        color: var(--c-accent);
    }

    /* ── Typed.js cursor ── */
    .typed-cursor { color: var(--c-accent); }
    </style>

    @stack('styles')
</head>
<body>

{{-- ── NAVBAR ── --}}
<nav class="nav sticky top-0 z-50 border-b"
     style="border-color:var(--c-line); height:var(--nav-h);"
     id="site-nav">
    <div class="max-w-[1280px] mx-auto px-6 h-full flex items-center gap-4">

        {{-- Brand --}}
        <a href="{{ url('/') }}"
           class="flex items-center gap-2 font-bold text-[.95rem] tracking-tight shrink-0"
           style="font-family:var(--f-display); color:var(--c-ink);">
            <img src="{{ asset('images/mondok-qu-logo.png') }}" alt="MondokQu"
                 width="30" height="30"
                 class="w-[30px] h-[30px] rounded-[7px]"
                 style="border:1px solid var(--c-line);">
            MondokQu
        </a>

        {{-- Nav links (desktop) — tengah --}}
        <ul class="hidden md:flex items-center justify-center gap-0.5 list-none flex-1">
            <li><a href="{{ url('/') }}" data-nav="beranda"
                   class="nav-link block px-3 py-1.5 text-[.84rem] font-medium rounded-lg transition-all duration-150"
                   style="color:var(--c-ink-3);">Beranda</a></li>
            <li><a href="{{ url('/') }}#fitur" data-nav="fitur"
                   class="nav-link block px-3 py-1.5 text-[.84rem] font-medium rounded-lg transition-all duration-150"
                   style="color:var(--c-ink-3);">Fitur</a></li>
            <li><a href="{{ url('/') }}#modul" data-nav="modul"
                   class="nav-link block px-3 py-1.5 text-[.84rem] font-medium rounded-lg transition-all duration-150"
                   style="color:var(--c-ink-3);">Modul</a></li>
            <li><a href="{{ url('/') }}#pengguna" data-nav="pengguna"
                   class="nav-link block px-3 py-1.5 text-[.84rem] font-medium rounded-lg transition-all duration-150"
                   style="color:var(--c-ink-3);">Pengguna</a></li>
            <li><a href="{{ url('/') }}#biaya" data-nav="biaya"
                   class="nav-link block px-3 py-1.5 text-[.84rem] font-medium rounded-lg transition-all duration-150"
                   style="color:var(--c-ink-3);">Biaya</a></li>

            {{-- Dropdown: Lainnya --}}
            <li class="nav-dropdown">
                <button class="nav-link flex items-center gap-1 px-3 py-1.5 text-[.84rem] font-medium rounded-lg transition-all duration-150 cursor-pointer border-none bg-transparent"
                        style="color:var(--c-ink-3);"
                        id="moreBtn" aria-haspopup="true" aria-expanded="false">
                    Lainnya <i class="ti ti-chevron-down text-[.75rem]"></i>
                </button>
                <div class="nav-dropdown-menu" id="moreMenu" role="menu">
                    <a href="{{ route('blog.index') }}"
                       class="{{ request()->routeIs('blog.*') ? 'is-active' : '' }}">
                        <i class="ti ti-article text-[.9rem]"></i> Blog
                    </a>
                    <a href="{{ route('about') }}"
                       class="{{ request()->routeIs('about') ? 'is-active' : '' }}">
                        <i class="ti ti-info-circle text-[.9rem]"></i> Tentang Kami
                    </a>
                    <a href="{{ route('faq') }}"
                       class="{{ request()->routeIs('faq') ? 'is-active' : '' }}">
                        <i class="ti ti-help-circle text-[.9rem]"></i> FAQ
                    </a>
                    <a href="{{ route('terms') }}"
                       class="{{ request()->routeIs('terms') ? 'is-active' : '' }}">
                        <i class="ti ti-file-description text-[.9rem]"></i> Syarat &amp; Ketentuan
                    </a>
                    <a href="{{ route('security-privacy') }}"
                       class="{{ request()->routeIs('security-privacy') ? 'is-active' : '' }}">
                        <i class="ti ti-shield-lock text-[.9rem]"></i> Keamanan &amp; Privasi
                    </a>
                </div>
            </li>
        </ul>

        {{-- Nav end (desktop: auth buttons) --}}
        <div class="hidden md:flex items-center gap-2">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="btn inline-flex items-center gap-1.5 px-3 py-1.5 text-[.8rem] font-semibold rounded-lg border transition-all duration-150 btn-primary">
                    <i class="ti ti-layout-dashboard"></i>
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[.8rem] font-semibold rounded-lg border-transparent transition-all duration-150"
                   style="color:var(--c-ink-2);"
                   onmouseover="this.style.background='var(--c-bg-3)';this.style.color='var(--c-ink)'"
                   onmouseout="this.style.background='';this.style.color='var(--c-ink-2)'">Masuk</a>
                @if (\Illuminate\Support\Facades\Route::has('register'))
                    <a href="{{ route('register') }}"
                       class="btn inline-flex items-center gap-1.5 px-3 py-1.5 text-[.8rem] font-semibold rounded-lg border transition-all duration-150 btn-primary">
                        Daftar
                    </a>
                @endif
            @endauth
        </div>

        {{-- Kanan: theme toggle (selalu tampil) + hamburger (mobile only) --}}
        <div class="flex items-center gap-2 ml-auto md:ml-0">
            <button class="w-8 h-8 rounded-lg border flex items-center justify-center text-[.95rem] cursor-pointer transition-all duration-150"
                    style="border-color:var(--c-line);background:transparent;color:var(--c-ink-3);"
                    onmouseover="this.style.background='var(--c-bg-3)';this.style.color='var(--c-ink)'"
                    onmouseout="this.style.background='transparent';this.style.color='var(--c-ink-3)'"
                    id="themeBtn" aria-label="Ganti tema">
                <i class="ti ti-sun" id="themeIcon"></i>
            </button>

            <button class="md:hidden w-[34px] h-[34px] rounded-lg border-none bg-transparent flex items-center justify-center text-lg cursor-pointer transition-colors duration-150"
                    style="color:var(--c-ink-3);"
                    id="hamBtn" aria-label="Buka menu" aria-expanded="false">
                <i class="ti ti-menu-2 block transition-transform duration-300 ease-in-out"></i>
            </button>
        </div>
    </div>
</nav>

{{-- Mobile drawer --}}
<div class="mobile-nav hidden fixed top-[var(--nav-h)] left-0 right-0 bottom-0 px-6 pb-8 z-[190] overflow-y-auto flex-col gap-0.5"
     id="mobileNav">
    <a href="{{ url('/') }}" data-nav="beranda"
       class="mob-link flex items-center gap-2 px-3 py-[.7rem] text-[.9rem] font-medium rounded-lg" style="color:var(--c-ink-2);"><i class="ti ti-home"></i> Beranda</a>
    <a href="{{ url('/') }}#fitur" data-nav="fitur"
       class="mob-link flex items-center gap-2 px-3 py-[.7rem] text-[.9rem] font-medium rounded-lg" style="color:var(--c-ink-2);"><i class="ti ti-layout-grid"></i> Fitur</a>
    <a href="{{ url('/') }}#modul" data-nav="modul"
       class="mob-link flex items-center gap-2 px-3 py-[.7rem] text-[.9rem] font-medium rounded-lg" style="color:var(--c-ink-2);"><i class="ti ti-apps"></i> Modul</a>
    <a href="{{ url('/') }}#pengguna" data-nav="pengguna"
       class="mob-link flex items-center gap-2 px-3 py-[.7rem] text-[.9rem] font-medium rounded-lg" style="color:var(--c-ink-2);"><i class="ti ti-users"></i> Pengguna</a>
    <a href="{{ url('/') }}#biaya" data-nav="biaya"
       class="mob-link flex items-center gap-2 px-3 py-[.7rem] text-[.9rem] font-medium rounded-lg" style="color:var(--c-ink-2);"><i class="ti ti-calculator"></i> Biaya</a>
    <hr class="my-1" style="border-top:1px solid var(--c-line);">
    <a href="{{ route('blog.index') }}"
       class="mob-link flex items-center gap-2 px-3 py-[.7rem] text-[.9rem] font-medium rounded-lg {{ request()->routeIs('blog.*') ? 'is-active' : '' }}"
       style="color:var(--c-ink-2);"><i class="ti ti-article"></i> Blog</a>
    <a href="{{ route('about') }}"
       class="mob-link flex items-center gap-2 px-3 py-[.7rem] text-[.9rem] font-medium rounded-lg {{ request()->routeIs('about') ? 'is-active' : '' }}"
       style="color:var(--c-ink-2);"><i class="ti ti-info-circle"></i> Tentang Kami</a>
    <a href="{{ route('faq') }}"
       class="mob-link flex items-center gap-2 px-3 py-[.7rem] text-[.9rem] font-medium rounded-lg {{ request()->routeIs('faq') ? 'is-active' : '' }}"
       style="color:var(--c-ink-2);"><i class="ti ti-help-circle"></i> FAQ</a>
    <a href="{{ route('terms') }}"
       class="mob-link flex items-center gap-2 px-3 py-[.7rem] text-[.9rem] font-medium rounded-lg {{ request()->routeIs('terms') ? 'is-active' : '' }}"
       style="color:var(--c-ink-2);"><i class="ti ti-file-description"></i> Syarat &amp; Ketentuan</a>
    <a href="{{ route('security-privacy') }}"
       class="mob-link flex items-center gap-2 px-3 py-[.7rem] text-[.9rem] font-medium rounded-lg {{ request()->routeIs('security-privacy') ? 'is-active' : '' }}"
       style="color:var(--c-ink-2);"><i class="ti ti-shield-lock"></i> Keamanan &amp; Privasi</a>
    <hr class="my-2" style="border-top:1px solid var(--c-line);">
    <div class="mt-3 flex flex-col gap-2">
        @auth
            <a href="{{ route('dashboard') }}" class="btn flex items-center justify-center gap-2 px-4 py-[.65rem] text-[.9rem] font-semibold rounded-lg border btn-primary">
                <i class="ti ti-layout-dashboard"></i> Dashboard
            </a>
        @else
            <a href="{{ route('login') }}"
               class="flex items-center justify-center px-4 py-[.65rem] text-[.9rem] font-semibold rounded-lg border transition-all duration-150"
               style="color:var(--c-ink-2);border-color:var(--c-line);background:var(--c-bg);">
                Masuk ke Sistem
            </a>
            @if (\Illuminate\Support\Facades\Route::has('register'))
                <a href="{{ route('register') }}" class="btn flex items-center justify-center px-4 py-[.65rem] text-[.9rem] font-semibold rounded-lg border btn-primary">
                    Buat Akun Pondok
                </a>
            @endif
        @endauth
    </div>
</div>

<main id="main">
    @yield('content')
</main>

{{-- ── FOOTER ── --}}
<footer class="pt-16 pb-8" style="background:var(--c-bg-2);border-top:1px solid var(--c-line);">
    <div class="max-w-[1280px] mx-auto px-6">

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-[1.6fr_1fr_1fr_1fr] gap-10 pb-10"
             style="border-bottom:1px solid var(--c-line);">

            {{-- Brand column --}}
            <div class="sm:col-span-2 md:col-span-1">
                <div class="flex items-center gap-[7px] font-bold text-[.95rem] mt-[.65rem]"
                     style="font-family:var(--f-display);color:var(--c-ink);">
                    <img src="{{ asset('images/mondok-qu-logo.png') }}" alt="MondokQu"
                         width="26" height="26"
                         class="w-[26px] h-[26px] rounded-[6px]"
                         style="border:1px solid var(--c-line);">
                    MondokQu
                </div>
                <p class="text-[.84rem] leading-[1.75] mt-[.6rem] max-w-[260px] md:max-w-full"
                   style="color:var(--c-ink-3);">
                    Sistem aplikasi manajemen pondok pesantren. Data santri, keuangan, absensi, dan komunikasi wali, semuanya dalam satu tempat.
                </p>
            </div>

            {{-- Jelajahi --}}
            <div>
                <h6 class="text-[.71rem] font-bold tracking-[.1em] uppercase mb-[.85rem]"
                    style="color:var(--c-ink-4);">Jelajahi</h6>
                <ul class="list-none flex flex-col gap-2">
                    <li><a href="{{ url('/') }}#fitur"    class="text-[.84rem] transition-colors duration-150" style="color:var(--c-ink-3);" onmouseover="this.style.color='var(--c-accent)'" onmouseout="this.style.color='var(--c-ink-3)'">Fitur utama</a></li>
                    <li><a href="{{ url('/') }}#modul"    class="text-[.84rem] transition-colors duration-150" style="color:var(--c-ink-3);" onmouseover="this.style.color='var(--c-accent)'" onmouseout="this.style.color='var(--c-ink-3)'">Daftar modul</a></li>
                    <li><a href="{{ url('/') }}#pengguna" class="text-[.84rem] transition-colors duration-150" style="color:var(--c-ink-3);" onmouseover="this.style.color='var(--c-accent)'" onmouseout="this.style.color='var(--c-ink-3)'">Untuk siapa?</a></li>
                    <li><a href="{{ url('/') }}#biaya"    class="text-[.84rem] transition-colors duration-150" style="color:var(--c-ink-3);" onmouseover="this.style.color='var(--c-accent)'" onmouseout="this.style.color='var(--c-ink-3)'">Informasi biaya</a></li>
                </ul>
            </div>

            {{-- Informasi --}}
            <div>
                <h6 class="text-[.71rem] font-bold tracking-[.1em] uppercase mb-[.85rem]"
                    style="color:var(--c-ink-4);">Informasi</h6>
                <ul class="list-none flex flex-col gap-2">
                    <li><a href="{{ route('about') }}"            class="text-[.84rem] transition-colors duration-150" style="color:var(--c-ink-3);" onmouseover="this.style.color='var(--c-accent)'" onmouseout="this.style.color='var(--c-ink-3)'">Tentang Kami</a></li>
                    <li><a href="{{ route('faq') }}"              class="text-[.84rem] transition-colors duration-150" style="color:var(--c-ink-3);" onmouseover="this.style.color='var(--c-accent)'" onmouseout="this.style.color='var(--c-ink-3)'">FAQ</a></li>
                    <li><a href="{{ route('terms') }}"            class="text-[.84rem] transition-colors duration-150" style="color:var(--c-ink-3);" onmouseover="this.style.color='var(--c-accent)'" onmouseout="this.style.color='var(--c-ink-3)'">Syarat &amp; Ketentuan</a></li>
                    <li><a href="{{ route('security-privacy') }}" class="text-[.84rem] transition-colors duration-150" style="color:var(--c-ink-3);" onmouseover="this.style.color='var(--c-accent)'" onmouseout="this.style.color='var(--c-ink-3)'">Keamanan &amp; Privasi</a></li>
                </ul>
            </div>

            {{-- Akses --}}
            <div>
                <h6 class="text-[.71rem] font-bold tracking-[.1em] uppercase mb-[.85rem]"
                    style="color:var(--c-ink-4);">Akses</h6>
                <ul class="list-none flex flex-col gap-2">
                    @auth
                        <li><a href="{{ route('dashboard') }}"    class="text-[.84rem] transition-colors duration-150" style="color:var(--c-ink-3);" onmouseover="this.style.color='var(--c-accent)'" onmouseout="this.style.color='var(--c-ink-3)'">Dashboard</a></li>
                        <li><a href="{{ route('profile.edit') }}" class="text-[.84rem] transition-colors duration-150" style="color:var(--c-ink-3);" onmouseover="this.style.color='var(--c-accent)'" onmouseout="this.style.color='var(--c-ink-3)'">Profil Saya</a></li>
                    @else
                        <li><a href="{{ route('login') }}"        class="text-[.84rem] transition-colors duration-150" style="color:var(--c-ink-3);" onmouseover="this.style.color='var(--c-accent)'" onmouseout="this.style.color='var(--c-ink-3)'">Masuk ke Sistem</a></li>
                        @if (\Illuminate\Support\Facades\Route::has('register'))
                            <li><a href="{{ route('register') }}" class="text-[.84rem] transition-colors duration-150" style="color:var(--c-ink-3);" onmouseover="this.style.color='var(--c-accent)'" onmouseout="this.style.color='var(--c-ink-3)'">Buat Akun Pondok</a></li>
                        @endif
                    @endauth
                    <li>
                        <a href="https://wa.me/6285117511220" target="_blank" rel="noopener"
                           class="text-[.84rem] transition-colors duration-150"
                           style="color:var(--c-ink-3);"
                           onmouseover="this.style.color='var(--c-accent)'" onmouseout="this.style.color='var(--c-ink-3)'">
                            Hubungi Kami
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-7 text-[.78rem]"
             style="color:var(--c-ink-4);">
            <span>&copy; {{ date('Y') }} MondokQu. Hak cipta dilindungi undang-undang.</span>
            <span class="inline-flex items-center gap-1.5 text-[.78rem]">
                <i class="ti ti-heart-filled text-[.75rem]" style="color:var(--c-accent);"></i>
                Dibuat untuk pondok pesantren Indonesia
            </span>
        </div>
    </div>
</footer>

<script>
(function(){
    var html = document.documentElement;
    var btn  = document.getElementById('themeBtn');
    var icon = document.getElementById('themeIcon');
    var nav  = document.getElementById('site-nav');
    var ham  = document.getElementById('hamBtn');
    var mob  = document.getElementById('mobileNav');

    // ── Theme ──
    function setTheme(t){
        html.className = t === 'dark' ? 'dark' : '';
        localStorage.setItem('mq-theme', t);
        if(icon) icon.className = t === 'dark' ? 'ti ti-moon' : 'ti ti-sun';
    }
    setTheme(localStorage.getItem('mq-theme') || (matchMedia('(prefers-color-scheme:dark)').matches ? 'dark' : 'light'));
    if(btn) btn.addEventListener('click', function(){
        setTheme(html.className === 'dark' ? 'light' : 'dark');
    });

    // ── Navbar scroll shadow ──
    if(nav){
        window.addEventListener('scroll', function(){
            nav.classList.toggle('scrolled', window.scrollY > 4);
        }, {passive: true});
    }

    // ── "Lainnya" dropdown toggle ──
    var moreBtn  = document.getElementById('moreBtn');
    var moreMenu = document.getElementById('moreMenu');
    if(moreBtn && moreMenu){
        moreBtn.addEventListener('click', function(e){
            e.stopPropagation();
            var open = moreBtn.closest('.nav-dropdown').classList.toggle('is-open');
            moreBtn.setAttribute('aria-expanded', open);
        });
        document.addEventListener('click', function(){
            moreBtn.closest('.nav-dropdown').classList.remove('is-open');
            moreBtn.setAttribute('aria-expanded', false);
        });
        moreMenu.addEventListener('click', function(e){ e.stopPropagation(); });
    }

    // ── Mobile drawer ──
    if(ham && mob){
        ham.addEventListener('click', function(){
            var open = mob.classList.toggle('open');
            mob.style.display = open ? 'flex' : '';
            ham.classList.toggle('is-open', open);
            ham.setAttribute('aria-expanded', open);
            ham.querySelector('i').className = open ? 'ti ti-x block' : 'ti ti-menu-2 block';
        });
        mob.querySelectorAll('a').forEach(function(a){
            a.addEventListener('click', function(){
                mob.style.display = '';
                ham.classList.remove('is-open');
                ham.setAttribute('aria-expanded','false');
                ham.querySelector('i').className = 'ti ti-menu-2 block';
            });
        });
    }

    // ── Scroll-spy active nav ──
    var navLinks = document.querySelectorAll('.nav-link[data-nav]');
    var mobLinks = document.querySelectorAll('.mob-link[data-nav]');

    // Section ids yang di-spy (tanpa "beranda" — itu fallback saat paling atas)
    var sectionIds = ['fitur', 'modul', 'pengguna', 'biaya'];

    function setActive(id) {
        navLinks.forEach(function(a) {
            a.classList.toggle('is-active', a.dataset.nav === id);
        });
        mobLinks.forEach(function(a) {
            a.classList.toggle('is-active', a.dataset.nav === id);
        });
    }

    function onScroll() {
        var navH   = nav ? nav.offsetHeight : 64;
        var offset = navH + 32; // sedikit padding supaya tidak terlalu sensitif
        var active = 'beranda'; // default

        sectionIds.forEach(function(id) {
            var el = document.getElementById(id);
            if (!el) return;
            if (el.getBoundingClientRect().top <= offset) {
                active = id;
            }
        });

        setActive(active);
    }

    // Init
    setActive('beranda');
    window.addEventListener('scroll', onScroll, {passive: true});
    onScroll(); // panggil sekali untuk halaman yang sudah di-scroll
})();
</script>

@stack('scripts')
</body>
</html>
