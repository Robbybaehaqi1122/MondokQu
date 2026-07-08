@extends('layouts-public.app')

@section('title',            config('app.ponpes_name', 'MondokQu') . ' -  Manajemen Pondok Pesantren')
@section('meta_description', 'MondokQu adalah sistem aplikasi manajemen pondok pesantren — kelola data santri, keuangan, absensi, hafalan, dan komunikasi wali dalam satu sistem terpadu.')
@section('og_title',         config('app.ponpes_name', 'MondokQu') . ' - Manajemen Pondok Pesantren')
@section('og_description',   'Kelola data santri, keuangan, absensi, hafalan, dan komunikasi wali dalam satu sistem yang mudah digunakan.')
@section('og_image',         asset('images/og-cover.png'))

@push('styles')
<style>
/* ── CSS-var dependent styles that cannot be expressed with Tailwind utilities ── */

/* Hero radial glow */
.hero-glow::before {
    content: '';
    position: absolute;
    inset: -8% 5% auto;
    height: 440px;
    border-radius: 50%;
    background: radial-gradient(ellipse at 50% 0%,
        color-mix(in srgb, var(--c-accent) 12%, transparent) 0%,
        transparent 68%);
    pointer-events: none;
    z-index: 0;
}

/* Gradient text for hero heading */
.grad {
    background: linear-gradient(135deg, var(--c-accent) 0%, color-mix(in srgb, var(--c-accent) 55%, #6366f1) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Bento card hover overlay */
.bento-card::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: inherit;
    background: linear-gradient(135deg, color-mix(in srgb, var(--c-accent) 4%, transparent), transparent 60%);
    opacity: 0;
    transition: opacity .25s;
    pointer-events: none;
}
.bento-card:hover::after { opacity: 1; }
.bento-card:hover { border-color: var(--c-accent-ring) !important; }

/* Role card hover */
.role-card:hover { border-color: var(--c-accent-ring) !important; }

/* KPI card accent top bar */
.kpi-card-hi::before {
    content: '';
    position: absolute;
    inset: 0 0 auto 0;
    height: 3px;
    background: linear-gradient(90deg, var(--c-accent), color-mix(in srgb, var(--c-accent) 60%, #6366f1));
}

/* Revenue bar fill */
.rb-fill, .mp-fill {
    height: 100%;
    background: var(--c-accent);
    border-radius: 99px;
    opacity: .85;
}
.rb-fill {
    background: linear-gradient(90deg, var(--c-accent), color-mix(in srgb, var(--c-accent) 70%, #6366f1));
    opacity: 1;
    transition: width .5s ease;
}

/* Calc slider accent */
.calc-slider {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 5px;
    border-radius: 99px;
    background: var(--c-bg-3);
    outline: none;
    cursor: pointer;
    margin: .5rem 0 1.5rem;
    accent-color: var(--c-accent);
}
.calc-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--c-accent);
    cursor: pointer;
    border: 3px solid var(--c-bg);
    box-shadow: 0 0 0 2px var(--c-accent-ring);
}

/* CTA box radial glow */
.cta-box::before {
    content: '';
    position: absolute;
    inset: -20% 20% auto;
    height: 380px;
    border-radius: 50%;
    background: radial-gradient(ellipse, color-mix(in srgb, var(--c-accent) 10%, transparent) 0%, transparent 65%);
    pointer-events: none;
}

/* Stat bar ghost text numbers */
.ghost-num {
    background: linear-gradient(180deg, var(--c-line) 0%, transparent 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* ps-num gradient */
.ps-num-grad {
    background: linear-gradient(135deg, var(--c-ink) 30%, var(--c-ink-3) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
</style>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════
     HERO
════════════════════════════════════════════ --}}
<section class="hero-glow relative overflow-hidden pt-24 pb-[4.5rem] text-center sm:pt-16 sm:pb-12"
         aria-labelledby="hero-h1">
    <div class="max-w-[1280px] mx-auto px-6 relative z-[1]">

        {{-- Eyebrow --}}
        <div class="inline-flex items-center gap-1.5 text-[.71rem] font-bold tracking-[.12em] uppercase
                    px-[.9rem] py-[.3rem] rounded-full border mb-7"
             style="color:var(--c-accent);border-color:var(--c-accent-ring);background:var(--c-accent-l);">
            <i class="ti ti-building-community"></i>
            Sistem Manajemen Pondok Pesantren
        </div>

        {{-- H1 --}}
        <h1 class="text-[clamp(2rem,4.5vw,3.4rem)] font-extrabold tracking-[-0.04em] leading-[1.08]
                   mx-auto mb-6 max-w-[760px]"
            style="font-family:var(--f-display);color:var(--c-ink);"
            id="hero-h1">
            Kelola <span class="grad"><span id="typed-text"></span></span><br>
            pondok Anda lebih mudah.
        </h1>

        {{-- Subtitle --}}
        <p class="text-[1.025rem] leading-[1.8] mx-auto mb-9 max-w-[500px]"
           style="color:var(--c-ink-3);">
            MondokQu menyatukan semua kebutuhan operasional pondok dalam satu sistem yang mudah dipakai, bisa diakses kapan saja, dari perangkat apa pun.
        </p>

        {{-- CTA buttons --}}
        <div class="flex flex-wrap gap-2.5 justify-center mb-9">
            <a href="{{ $primaryAction }}"
               class="btn inline-flex items-center gap-1.5 px-[1.4rem] py-[.65rem] text-[.925rem] font-semibold rounded-lg border transition-all duration-150 btn-primary">
                {{ $currentUser ? 'Buka Dashboard' : 'Coba Sekarang' }}
                <i class="ti ti-arrow-right"></i>
            </a>
            <a href="#fitur"
               class="inline-flex items-center gap-1.5 px-[1.4rem] py-[.65rem] text-[.925rem] font-semibold rounded-lg border transition-all duration-150"
               style="background:var(--c-bg);color:var(--c-ink-2);border-color:var(--c-line);"
               onmouseover="this.style.background='var(--c-bg-2)';this.style.color='var(--c-ink)'"
               onmouseout="this.style.background='var(--c-bg)';this.style.color='var(--c-ink-2)'">
                <i class="ti ti-layout-grid"></i>
                Lihat Fitur
            </a>
            @if ($registerEnabled && ! $currentUser)
                <a href="{{ route('register') }}"
                   class="btn inline-flex items-center gap-1.5 px-[1.4rem] py-[.65rem] text-[.925rem] font-semibold rounded-lg border transition-all duration-150 btn-outline">
                    Buat Akun Pondok
                </a>
            @endif
        </div>

        {{-- Feature chips --}}
        <div class="flex flex-wrap gap-1.5 justify-center mb-14">
            @foreach ([
                'Satu sistem untuk semua',
                'Akses khusus pengurus &amp; orang tua',
                'Responsif di HP &amp; laptop',
                'Data tersimpan aman',
                '20+ fitur siap pakai',
            ] as $chip)
                <span class="inline-flex items-center gap-1 text-[.74rem] font-medium px-[.7rem] py-[.28rem] rounded-full border"
                      style="color:var(--c-ink-3);border-color:var(--c-line);background:var(--c-bg);">
                    <i class="ti ti-check text-[.72rem]" style="color:var(--c-accent);"></i>
                    {!! $chip !!}
                </span>
            @endforeach
        </div>


        {{-- Hero image --}}
        <div class="max-w-[840px] mx-auto rounded-[18px] overflow-hidden border"
             style="border-color:var(--c-line);
                    box-shadow:0 0 0 1px rgba(255,255,255,.05) inset, 0 8px 40px rgba(0,0,0,.09), 0 2px 8px rgba(0,0,0,.05);">
            <img src="{{ asset('images/hero.png') }}"
                 alt="Tampilan dashboard MondokQu"
                 class="w-full block"
                 width="840"
                 loading="eager">
        </div>

    </div>
</section>


{{-- ═══════════════════════════════════════════
     PROOF STRIP
════════════════════════════════════════════ --}}
<div class="border-t border-b" style="border-color:var(--c-line);background:var(--c-bg-2);"
     aria-label="Keunggulan platform">
    <div class="max-w-[1280px] mx-auto px-6 grid grid-cols-2 sm:grid-cols-4">
        @foreach ([
            ['20+',   'Fitur siap pakai'],
            ['Multi', 'Pondok dalam satu aplikasi'],
            ['5',     'Jenis hak akses pengguna'],
            ['100%',  'Data terpusat &amp; terorganisir'],
        ] as $idx => [$num, $label])
            <div class="flex flex-col gap-[.2rem] px-5 py-6
                        {{ $idx < 3 ? 'border-r' : '' }}
                        {{ $idx === 1 ? 'sm:border-r' : '' }}
                        {{ $idx >= 2 ? 'border-t sm:border-t-0' : '' }}"
                 style="border-color:var(--c-line);">
                <span class="ps-num-grad text-[1.85rem] font-extrabold tracking-[-0.05em] leading-none">{{ $num }}</span>
                <span class="text-[.8rem]" style="color:var(--c-ink-3);">{!! $label !!}</span>
            </div>
        @endforeach
    </div>
</div>


{{-- ═══════════════════════════════════════════
     BENTO FEATURES
════════════════════════════════════════════ --}}
<section class="py-24 sm:py-16" id="fitur" aria-labelledby="fitur-h2">
    <div class="max-w-[1280px] mx-auto px-6">

        <div class="mb-12">
            <p class="text-[.69rem] font-bold tracking-[.13em] uppercase mb-2" style="color:var(--c-accent);">Apa yang bisa dilakukan</p>
            <h2 class="text-[clamp(1.55rem,3vw,2.25rem)] font-extrabold tracking-[-0.04em] leading-[1.1] mb-[.65rem]"
                style="font-family:var(--f-display);color:var(--c-ink);"
                id="fitur-h2">Dirancang untuk kebutuhan pondok yang sesungguhnya.</h2>
            <p class="text-[.94rem] leading-[1.8] max-w-[540px]" style="color:var(--c-ink-3);">Setiap fitur saling terhubung. Data santri yang diinput sekali langsung bisa digunakan di absensi, keuangan, dan laporan untuk wali tanpa entri ulang.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-[.7rem]">
            @foreach ($featureCards as $i => $f)
                <div class="bento-card relative overflow-hidden rounded-2xl border p-[1.85rem_1.65rem]
                             transition-all duration-200 hover:-translate-y-0.5"
                     style="background:var(--c-bg);border-color:var(--c-line);">
                    <span class="ghost-num block text-[3rem] font-extrabold tracking-[-0.06em] leading-none mb-5"
                          style="font-family:var(--f-display);"
                          aria-hidden="true">0{{ $i + 1 }}</span>
                    <div class="w-[42px] h-[42px] flex items-center justify-center rounded-[11px] border text-[1.15rem] mb-[1.1rem]"
                         style="background:var(--c-accent-l);border-color:var(--c-accent-ring);color:var(--c-accent);">
                        <i class="{{ $f['icon'] }}"></i>
                    </div>
                    <p class="text-[1.05rem] font-bold tracking-[-0.02em] mb-[.45rem]"
                       style="font-family:var(--f-display);color:var(--c-ink);">{{ $f['title'] }}</p>
                    <p class="text-[.875rem] leading-[1.72]" style="color:var(--c-ink-3);">{{ $f['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     MODULES
════════════════════════════════════════════ --}}
<section class="py-24 sm:py-16 border-t border-b" id="modul"
         style="background:var(--c-bg-2);border-color:var(--c-line);"
         aria-labelledby="modul-h2">
    <div class="max-w-[1280px] mx-auto px-6">

        <div class="mb-12">
            <p class="text-[.69rem] font-bold tracking-[.13em] uppercase mb-2" style="color:var(--c-accent);">Fitur lengkap</p>
            <h2 class="text-[clamp(1.55rem,3vw,2.25rem)] font-extrabold tracking-[-0.04em] leading-[1.1] mb-[.65rem]"
                style="font-family:var(--f-display);color:var(--c-ink);"
                id="modul-h2">Semua sudah tersedia, tanpa biaya tambahan.</h2>
            <p class="text-[.94rem] leading-[1.8] max-w-[540px]" style="color:var(--c-ink-3);">Dari pendaftaran santri baru hingga laporan keuangan akhir tahun, tidak ada fitur yang perlu dibeli terpisah.</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-[.45rem]">
            @foreach ([
                ['ti ti-users',              'Data Santri'],
                ['ti ti-calendar-check',     'Absensi & Kehadiran'],
                ['ti ti-book',               'Hafalan & Setoran'],
                ['ti ti-receipt-2',          'Keuangan & Tagihan'],
                ['ti ti-message-circle',     'Pesan ke Orang Tua'],
                ['ti ti-door',               'Manajemen Kamar'],
                ['ti ti-alert-triangle',     'Catatan Pelanggaran'],
                ['ti ti-school',             'Nilai & Rapor'],
                ['ti ti-heart-rate-monitor', 'Kesehatan Santri'],
                ['ti ti-package',            'Inventaris'],
                ['ti ti-calendar-event',     'Jadwal Kegiatan'],
                ['ti ti-file-text',          'Pendaftaran Online'],
                ['ti ti-building',           'Data Pengurus'],
                ['ti ti-books',              'Perpustakaan'],
                ['ti ti-database',           'Cadangan & Ekspor Data'],
                ['ti ti-chart-bar',          'Laporan & Statistik'],
            ] as [$ico, $lbl])
                <div class="flex items-center gap-[.6rem] px-4 py-[.8rem] border rounded-[9px] text-[.84rem] font-medium
                             transition-all duration-[.18s] cursor-default"
                     style="background:var(--c-bg);border-color:var(--c-line);color:var(--c-ink-2);"
                     onmouseover="this.style.borderColor='var(--c-accent-ring)';this.style.background='var(--c-accent-l)';this.style.color='var(--c-ink)';this.style.transform='translateY(-1px)'"
                     onmouseout="this.style.borderColor='var(--c-line)';this.style.background='var(--c-bg)';this.style.color='var(--c-ink-2)';this.style.transform=''">
                    <i class="{{ $ico }} text-[.9rem] shrink-0" style="color:var(--c-accent);" aria-hidden="true"></i>
                    <span>{{ $lbl }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     CONDITIONAL: Admin stats atau Roles
════════════════════════════════════════════ --}}
@if ($canSeeAdminStats)

<section class="py-24 sm:py-16" aria-labelledby="admin-h2">
    <div class="max-w-[1280px] mx-auto px-6">

        <div class="mb-12">
            <p class="text-[.69rem] font-bold tracking-[.13em] uppercase mb-2" style="color:var(--c-accent);">Ringkasan pondok Anda</p>
            <h2 class="text-[clamp(1.55rem,3vw,2.25rem)] font-extrabold tracking-[-0.04em] leading-[1.1] mb-[.65rem]"
                style="font-family:var(--f-display);color:var(--c-ink);"
                id="admin-h2">Kondisi pondok hari ini.</h2>
            <p class="text-[.94rem] leading-[1.8] max-w-[540px]" style="color:var(--c-ink-3);">Data ditarik langsung dari akun pondok Anda — sama persis dengan yang ada di halaman utama.</p>
        </div>

        {{-- KPI cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <div class="kpi-card-hi relative rounded-[13px] border p-[1.3rem_1.2rem] overflow-hidden transition-all duration-[.18s] hover:-translate-y-0.5 hover:shadow-lg"
                 style="background:var(--c-bg);border-color:var(--c-line);">
                <p class="text-[.67rem] font-bold uppercase tracking-[.1em] mb-[.4rem]" style="color:var(--c-ink-4);">Total Pengguna</p>
                <p class="text-[1.75rem] font-extrabold tracking-[-0.05em] leading-none mb-[.2rem]" style="font-family:var(--f-display);color:var(--c-ink);">{{ data_get($dashboardData, 'stats.total_users', 0) }}</p>
                <p class="text-[.72rem]" style="color:var(--c-ink-4);">Akun terdaftar di pondok ini</p>
            </div>
            @foreach ([
                ['Santri Aktif',          data_get($dashboardData, 'santriStats.active_santri', 0),   'text-[1.75rem]', 'Masih aktif mondok'],
                ['Pemasukan Bulan Ini',   'Rp&thinsp;' . number_format((int) data_get($dashboardData, 'financeStats.paid_this_month', 0), 0, ',', '.'), 'text-[1.1rem]', 'Pembayaran yang sudah tercatat'],
                ['Tagihan Menunggak',     data_get($dashboardData, 'financeStats.overdue_invoices', 0), 'text-[1.75rem]', 'Sudah lewat jatuh tempo'],
            ] as [$label, $val, $size, $hint])
                <div class="relative rounded-[13px] border p-[1.3rem_1.2rem] overflow-hidden transition-all duration-[.18s] hover:-translate-y-0.5 hover:shadow-lg"
                     style="background:var(--c-bg);border-color:var(--c-line);">
                    <p class="text-[.67rem] font-bold uppercase tracking-[.1em] mb-[.4rem]" style="color:var(--c-ink-4);">{!! $label !!}</p>
                    <p class="{{ $size }} font-extrabold tracking-[-0.05em] leading-none mb-[.2rem]" style="font-family:var(--f-display);color:var(--c-ink);">{!! $val !!}</p>
                    <p class="text-[.72rem]" style="color:var(--c-ink-4);">{{ $hint }}</p>
                </div>
            @endforeach
        </div>

        {{-- Data panels --}}
        <div class="grid grid-cols-1 lg:grid-cols-[1.3fr_1fr] gap-3">
            {{-- Monthly revenue --}}
            <div class="border rounded-[13px] overflow-hidden" style="background:var(--c-bg);border-color:var(--c-line);">
                <div class="px-[1.1rem] py-[.8rem] border-b" style="background:var(--c-bg-2);border-color:var(--c-line);">
                    <p class="text-[.84rem] font-bold" style="font-family:var(--f-display);color:var(--c-ink);">Pemasukan 6 Bulan Terakhir</p>
                    <p class="text-[.73rem] mt-[.08rem]" style="color:var(--c-ink-4);">Pembayaran santri yang masuk per bulan</p>
                </div>
                <div class="px-[1.1rem] py-4">
                    @foreach (collect(data_get($dashboardData, 'monthlyRevenue', [])) as $month)
                        <div class="flex items-center gap-[.7rem] py-[.28rem]">
                            <span class="text-[.73rem] font-semibold w-[52px] shrink-0" style="color:var(--c-ink-3);">{{ $month['label'] }}</span>
                            <div class="flex-1 h-[5px] rounded-full overflow-hidden" style="background:var(--c-bg-3);">
                                <div class="rb-fill" style="width:{{ max(0, (int)($month['percentage'] ?? 0)) }}%"
                                     role="progressbar"
                                     aria-valuenow="{{ (int)($month['percentage'] ?? 0) }}"
                                     aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="text-[.71rem] w-[78px] text-right shrink-0" style="color:var(--c-ink-4);">Rp {{ number_format((int)($month['total'] ?? 0), 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            {{-- Overdue invoices --}}
            <div class="border rounded-[13px] overflow-hidden" style="background:var(--c-bg);border-color:var(--c-line);">
                <div class="px-[1.1rem] py-[.8rem] border-b" style="background:var(--c-bg-2);border-color:var(--c-line);">
                    <p class="text-[.84rem] font-bold" style="font-family:var(--f-display);color:var(--c-ink);">Tagihan Paling Menunggak</p>
                    <p class="text-[.73rem] mt-[.08rem]" style="color:var(--c-ink-4);">Prioritas berdasarkan nominal tertunggak</p>
                </div>
                <div class="px-[1.1rem] py-4">
                    @forelse (collect(data_get($dashboardData, 'topOverdueInvoices', [])) as $invoice)
                        <div class="flex items-start justify-between gap-[.7rem] py-[.65rem] border-b last:border-0 first:pt-0 last:pb-0"
                             style="border-color:var(--c-line);">
                            <div>
                                <p class="text-[.85rem] font-semibold" style="color:var(--c-ink);">{{ $invoice['santri_name'] ?? '-' }}</p>
                                <p class="text-[.72rem] mt-[.15rem]" style="color:var(--c-ink-4);">{{ $invoice['invoice_number'] ?? '-' }} &middot; jatuh tempo {{ $invoice['due_date'] ?? '-' }}</p>
                            </div>
                            <span class="text-[.85rem] font-bold text-red-500 whitespace-nowrap shrink-0">Rp {{ number_format((int)($invoice['outstanding_amount'] ?? 0), 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-[.82rem] py-2" style="color:var(--c-ink-4);">Tidak ada tagihan yang menunggak.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>

@else


<section class="py-24 sm:py-16" id="pengguna" aria-labelledby="pengguna-h2">
    <div class="max-w-[1280px] mx-auto px-6">

        <div class="mb-12">
            <p class="text-[.69rem] font-bold tracking-[.13em] uppercase mb-2" style="color:var(--c-accent);">Untuk siapa?</p>
            <h2 class="text-[clamp(1.55rem,3vw,2.25rem)] font-extrabold tracking-[-0.04em] leading-[1.1] mb-[.65rem]"
                style="font-family:var(--f-display);color:var(--c-ink);"
                id="pengguna-h2">Satu sistem, akses yang sesuai peran.</h2>
            <p class="text-[.94rem] leading-[1.8] max-w-[540px]" style="color:var(--c-ink-3);">Setiap pengguna mendapat tampilan dan fitur yang sesuai dengan tugasnya pengurus pondok, wali santri, hingga santri itu sendiri.</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach ($targetRoles as $i => $role)
                <div class="role-card relative border rounded-[14px] p-[1.65rem_1.4rem] overflow-hidden
                             transition-all duration-200 hover:-translate-y-[3px] hover:shadow-lg"
                     style="background:var(--c-bg);border-color:var(--c-line);">
                    <span class="ghost-num absolute top-4 right-5 text-[2.75rem] font-extrabold tracking-[-0.06em] leading-none pointer-events-none"
                          style="font-family:var(--f-display);"
                          aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="inline-block text-[.63rem] font-bold tracking-[.08em] uppercase px-2 py-[.15rem] rounded-full border mb-[.7rem]"
                          style="color:var(--c-accent);background:var(--c-accent-l);border-color:var(--c-accent-ring);">Pengguna {{ $i + 1 }}</span>
                    <p class="text-[1rem] font-bold mb-[.4rem] relative" style="font-family:var(--f-display);color:var(--c-ink);">{{ $role['name'] }}</p>
                    <p class="text-[.84rem] leading-[1.65] relative" style="color:var(--c-ink-3);">{{ $role['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

@endif


{{-- ═══════════════════════════════════════════
     KALKULATOR BIAYA
════════════════════════════════════════════ --}}
<section class="py-24 sm:py-16 border-t border-b" id="biaya"
         style="background:var(--c-bg-2);border-color:var(--c-line);"
         aria-labelledby="biaya-h2">
    <div class="max-w-[1280px] mx-auto px-6">

        <div class="mb-12">
            <p class="text-[.69rem] font-bold tracking-[.13em] uppercase mb-2" style="color:var(--c-accent);">Informasi biaya</p>
            <h2 class="text-[clamp(1.55rem,3vw,2.25rem)] font-extrabold tracking-[-0.04em] leading-[1.1] mb-[.65rem]"
                style="font-family:var(--f-display);color:var(--c-ink);"
                id="biaya-h2">Harga yang jelas, tanpa kejutan.</h2>
            <p class="text-[.94rem] leading-[1.8] max-w-[540px]" style="color:var(--c-ink-3);">Biaya dihitung berdasarkan jumlah santri di pondok Anda. Tidak ada biaya tersembunyi, tidak ada paket per fitur.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            {{-- Kiri: info skema harga --}}
            <div class="flex flex-col gap-6">
                @foreach ([
                    ['ti ti-id-badge-2',      'Biaya Pendaftaran Awal',
                     'Dibayar sekali saat pertama kali mendaftarkan santri ke sistem. Tidak perlu dibayar lagi setelah itu.',
                     'Rp 15.000 / santri', '(sekali bayar)'],
                    ['ti ti-calendar-repeat', 'Biaya Berlangganan Bulanan',
                     'Dibayar setiap bulan untuk menggunakan seluruh fitur sistem. Makin besar pondok, tetap proporsional.',
                     'Rp 8.000 / santri / bulan', ''],
                    ['ti ti-apps',            'Semua Fitur Termasuk',
                     'Absensi, keuangan, hafalan, laporan, komunikasi wali, semuanya sudah termasuk. Tidak ada biaya per fitur.',
                     '20+ fitur dalam satu paket', ''],
                ] as [$ico, $title, $desc, $price, $note])
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 shrink-0 rounded-[12px] border flex items-center justify-center text-[1.2rem]"
                             style="background:var(--c-accent-l);border-color:var(--c-accent-ring);color:var(--c-accent);">
                            <i class="{{ $ico }}"></i>
                        </div>
                        <div>
                            <p class="text-[.95rem] font-bold mb-[.2rem]" style="font-family:var(--f-display);color:var(--c-ink);">{{ $title }}</p>
                            <p class="text-[.85rem] leading-[1.65]" style="color:var(--c-ink-3);">{{ $desc }}</p>
                            <p class="text-[1rem] font-extrabold mt-[.25rem]" style="font-family:var(--f-display);color:var(--c-accent);">
                                {{ $price }}
                                @if($note) <span class="text-[.82rem] font-normal" style="color:var(--c-ink-3);">{{ $note }}</span> @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Kanan: kalkulator interaktif --}}
            <div>
                <div class="rounded-[20px] border p-[2.25rem_2rem]"
                     style="background:var(--c-bg);border-color:var(--c-line);box-shadow:var(--shadow-md);">
                    <div class="flex items-center justify-between text-[.8rem] font-semibold mb-[.45rem]" style="color:var(--c-ink-2);">
                        <span>Jumlah Santri di Pondok Anda</span>
                        <span class="text-[1.4rem] font-extrabold tracking-[-0.03em]"
                              style="font-family:var(--f-display);color:var(--c-accent);"
                              id="calc-count-display">100 santri</span>
                    </div>
                    <input type="range" class="calc-slider" id="calc-slider"
                           min="10" max="2000" step="10" value="100"
                           aria-label="Jumlah santri">
                    <div class="flex items-center gap-2 mb-6">
                        <input type="number"
                               class="flex-1 px-[.85rem] py-[.6rem] rounded-lg border outline-none transition-all duration-150
                                      text-[1.1rem] font-bold"
                               style="font-family:var(--f-display);border-color:var(--c-line);background:var(--c-bg-2);color:var(--c-ink);"
                               onfocus="this.style.borderColor='var(--c-accent)'"
                               onblur="this.style.borderColor='var(--c-line)'"
                               id="calc-input" min="1" max="99999" value="100"
                               aria-label="Masukkan jumlah santri secara manual">
                        <span class="text-[.82rem] whitespace-nowrap" style="color:var(--c-ink-4);">santri</span>
                    </div>

                    <div class="flex flex-col gap-[.65rem] mt-2">
                        <div class="flex justify-between items-center px-[1.1rem] py-4 rounded-[11px] border"
                             style="border-color:var(--c-line);background:var(--c-bg-2);">
                            <div>
                                <p class="text-[.8rem] font-semibold" style="color:var(--c-ink-3);">Biaya Pendaftaran Awal</p>
                                <p class="text-[.7rem] font-normal mt-[.1rem]" style="color:var(--c-ink-4);">Dibayar sekali saat mulai</p>
                            </div>
                            <div class="text-[1.15rem] font-extrabold tracking-[-0.03em]"
                                 style="font-family:var(--f-display);color:var(--c-ink);"
                                 id="calc-reg">Rp 150.000</div>
                        </div>
                        <div class="flex justify-between items-center px-[1.1rem] py-4 rounded-[11px] border"
                             style="border-color:var(--c-accent-ring);background:var(--c-accent-l);">
                            <div>
                                <p class="text-[.8rem] font-semibold" style="color:var(--c-ink-3);">Biaya Berlangganan</p>
                                <p class="text-[.7rem] font-normal mt-[.1rem]" style="color:var(--c-ink-4);">Dibayar setiap bulan</p>
                            </div>
                            <div class="text-[1.15rem] font-extrabold tracking-[-0.03em]"
                                 style="font-family:var(--f-display);color:var(--c-accent);"
                                 id="calc-monthly">Rp 800.000 / bln</div>
                        </div>
                    </div>

                    <p class="text-[.76rem] leading-[1.65] mt-4" style="color:var(--c-ink-4);">
                        * Estimasi ini bersifat indikatif. Hubungi kami untuk informasi harga resmi dan proses aktivasi akun pondok Anda.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     Blog
════════════════════════════════════════════ --}}
@if ($latestBlogs->isNotEmpty())
    <section class="py-24 sm:py-16" aria-label="Blog" id="blog">
        <div class="max-w-[1280px] mx-auto px-6">
            <div class="text-center mb-12">
                <span class="inline-block text-[.78rem] font-semibold tracking-[.06em] uppercase px-3 py-1 rounded-full border mb-4"
                      style="color:var(--c-accent);border-color:color-mix(in srgb, var(--c-accent) 25%, transparent);">
                    Blog
                </span>
                <h2 class="text-[clamp(1.55rem,3vw,2.1rem)] font-extrabold tracking-[-0.04em] leading-[1.1] mb-3"
                    style="font-family:var(--f-display);color:var(--c-ink);">
                    Artikel & Berita Terbaru
                </h2>
                <p class="text-[.94rem] leading-[1.8] max-w-[540px] mx-auto" style="color:var(--c-ink-3);">
                    Informasi dan wawasan seputar pengelolaan pondok pesantren modern.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($latestBlogs as $blog)
                    <a href="{{ route('blog.show', $blog->slug) }}" class="text-decoration-none">
                        <div class="rounded-[16px] border overflow-hidden transition-all duration-200 h-100"
                             style="border-color:var(--c-line);background:var(--c-bg);"
                             onmouseover="this.style.boxShadow='0 8px 30px rgba(0,0,0,.08)';this.style.transform='translateY(-2px)'"
                             onmouseout="this.style.boxShadow='none';this.style.transform='none'">
                            <div style="height: 180px; overflow: hidden; background: var(--c-bg-2);">
                                @if ($blog->featured_image)
                                    <img src="{{ asset('storage/'.$blog->featured_image) }}" alt="{{ $blog->title }}" class="w-100 h-100" style="object-fit: cover;">
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-article" style="font-size: 2.5rem; color: var(--c-ink-3);"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <small style="color:var(--c-ink-3);">{{ $blog->published_at?->translatedFormat('d M Y') }}</small>
                                    <span style="color:var(--c-ink-3);">&middot;</span>
                                    <small style="color:var(--c-ink-3);">{{ $blog->getReadingTime() }}</small>
                                </div>
                                <h5 class="fw-bold mb-1" style="color:var(--c-ink);">{{ $blog->title }}</h5>
                                <p class="small mb-0" style="color:var(--c-ink-3);">
                                    {{ $blog->getExcerptHtml() }}
                                </p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="text-center mt-8">
                <a href="{{ route('blog.index') }}"
                   class="inline-flex items-center gap-1.5 px-[1.2rem] py-[.5rem] text-[.85rem] font-semibold rounded-lg border transition-all duration-150"
                   style="color:var(--c-ink-2);border-color:var(--c-line);background:var(--c-bg);"
                   onmouseover="this.style.background='var(--c-bg-2)';this.style.color='var(--c-ink)'"
                   onmouseout="this.style.background='var(--c-bg)';this.style.color='var(--c-ink-2)'">
                    Lihat Semua Artikel <i class="ti ti-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>
@endif

{{-- ═══════════════════════════════════════════
     CTA
════════════════════════════════════════════ --}}
<section class="py-24 sm:py-16" aria-label="Langkah selanjutnya">
    <div class="max-w-[1280px] mx-auto px-6">
        <div class="cta-box relative overflow-hidden rounded-[22px] px-12 py-16 sm:px-5 sm:py-10 text-center border"
             style="background:linear-gradient(135deg, color-mix(in srgb, var(--c-accent) 7%, var(--c-bg)) 0%, var(--c-bg) 60%);
                    border-color:var(--c-accent-ring);">
            <h2 class="relative text-[clamp(1.55rem,3vw,2.35rem)] font-extrabold tracking-[-0.04em] leading-[1.1] mb-[.7rem]"
                style="font-family:var(--f-display);color:var(--c-ink);">
                @if ($currentUser)
                    Dashboard Anda sudah siap.
                @else
                    Siap kelola pondok dengan cara yang lebih mudah?
                @endif
            </h2>
            <p class="relative text-[.95rem] leading-[1.78] max-w-[460px] mx-auto mb-8" style="color:var(--c-ink-3);">
                @if ($currentUser)
                    Semua data dan aktivitas operasional pondok tersedia di dashboard sesuai hak akses Anda.
                @else
                    Masuk ke sistem atau hubungi kami untuk memulai. Tim kami siap membantu proses aktivasi akun pondok Anda.
                @endif
            </p>
            <div class="relative flex flex-wrap gap-[.65rem] justify-center">
                <a href="{{ $primaryAction }}"
                   class="btn inline-flex items-center gap-1.5 px-[1.4rem] py-[.65rem] text-[.925rem] font-semibold rounded-lg border transition-all duration-150 btn-primary">
                    {{ $currentUser ? 'Buka Dashboard' : 'Masuk ke Sistem' }}
                    <i class="ti ti-arrow-right"></i>
                </a>
                @if ($registerEnabled && ! $currentUser)
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center gap-1.5 px-[1.4rem] py-[.65rem] text-[.925rem] font-semibold rounded-lg border transition-all duration-150"
                       style="background:var(--c-bg);color:var(--c-ink-2);border-color:var(--c-line);"
                       onmouseover="this.style.background='var(--c-bg-2)';this.style.color='var(--c-ink)'"
                       onmouseout="this.style.background='var(--c-bg)';this.style.color='var(--c-ink-2)'">
                        Buat Akun Pondok
                    </a>
                @endif
                @if (! $currentUser)
                    <a href="https://wa.me/{{ config('saas.admin_whatsapp') }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 px-[1.4rem] py-[.65rem] text-[.925rem] font-semibold rounded-lg border-transparent transition-all duration-150"
                       style="color:var(--c-ink-2);"
                       onmouseover="this.style.background='var(--c-bg-3)';this.style.color='var(--c-ink)'"
                       onmouseout="this.style.background='';this.style.color='var(--c-ink-2)'">
                        <i class="ti ti-brand-whatsapp" style="color:#25d366;"></i>
                        Hubungi Admin
                    </a>
                @endif
                @if ($currentUser)
                    <a href="{{ $secondaryAction }}"
                       class="inline-flex items-center gap-1.5 px-[1.4rem] py-[.65rem] text-[.925rem] font-semibold rounded-lg border transition-all duration-150"
                       style="background:var(--c-bg);color:var(--c-ink-2);border-color:var(--c-line);"
                       onmouseover="this.style.background='var(--c-bg-2)';this.style.color='var(--c-ink)'"
                       onmouseout="this.style.background='var(--c-bg)';this.style.color='var(--c-ink-2)'">
                        <i class="ti ti-user-circle"></i>
                        Profil Saya
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
// ── Typed.js — Typewriter Hero ──
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Typed !== 'undefined') {
        new Typed('#typed-text', {
            strings: [
                'Data Santri',
                'Keuangan',
                'Absensi',
                'Setoran Hafalan',
                'Laporan Wali',
            ],
            typeSpeed: 60,
            backSpeed: 35,
            backDelay: 1800,
            startDelay: 400,
            loop: true,
            smartBackspace: true,
        });
    }
});

// ── Kalkulator Biaya ──
(function () {
    var BIAYA_REG     = 15000;
    var BIAYA_BULANAN = 8000;

    var slider  = document.getElementById('calc-slider');
    var input   = document.getElementById('calc-input');
    var display = document.getElementById('calc-count-display');
    var regEl   = document.getElementById('calc-reg');
    var monthEl = document.getElementById('calc-monthly');

    function formatRupiah(n) {
        return 'Rp ' + n.toLocaleString('id-ID');
    }

    function update(val) {
        var n = parseInt(val, 10);
        if (isNaN(n) || n < 1) n = 1;
        if (n > 99999) n = 99999;

        display.textContent = n.toLocaleString('id-ID') + ' santri';
        regEl.textContent   = formatRupiah(n * BIAYA_REG);
        monthEl.textContent = formatRupiah(n * BIAYA_BULANAN) + ' / bln';

        if (slider && parseInt(slider.value, 10) !== n) slider.value = Math.min(n, 2000);
        if (input  && parseInt(input.value,  10) !== n) input.value  = n;
    }

    if (slider) slider.addEventListener('input', function () { update(this.value); });

    if (input) {
        input.addEventListener('input', function () { update(this.value); });
        input.addEventListener('blur',  function () {
            var n = parseInt(this.value, 10);
            if (isNaN(n) || n < 1) { this.value = 1; update(1); }
        });
    }

    update(100);
})();
</script>
@endpush
