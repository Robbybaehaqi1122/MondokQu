@extends('layouts-public.app')

@section('title', 'Tentang Kami  MondokQu')
@section('meta_description', 'MondokQu adalah sistem manajemen pondok pesantren berbasis SaaS yang dibangun untuk menyederhanakan operasional pondok di seluruh Indonesia.')

@section('content')

{{-- ── PAGE HERO ── --}}
<section class="pt-20 pb-14 text-center" style="background:var(--c-bg);">
    <div class="max-w-[720px] mx-auto px-6">
        <div class="inline-flex items-center gap-1.5 text-[.71rem] font-bold tracking-[.12em] uppercase
                    px-[.9rem] py-[.3rem] rounded-full border mb-6"
             style="color:var(--c-accent);border-color:var(--c-accent-ring);background:var(--c-accent-l);">
            <i class="ti ti-info-circle"></i> Tentang Kami
        </div>
        <h1 class="text-[clamp(1.9rem,4vw,2.8rem)] font-extrabold tracking-[-0.04em] leading-[1.1] mb-5"
            style="font-family:var(--f-display);color:var(--c-ink);">
            Dibangun untuk pondok pesantren Indonesia
        </h1>
        <p class="text-[1rem] leading-[1.8]" style="color:var(--c-ink-3);">
            MondokQu hadir karena kami percaya setiap pondok pesantren berhak mendapat sistem manajemen yang baik  tanpa harus punya tim IT besar atau anggaran yang besar.
        </p>
    </div>
</section>

{{-- ── MISI & VISI ── --}}
<section class="py-16 border-t" style="border-color:var(--c-line);background:var(--c-bg-2);">
    <div class="max-w-[1000px] mx-auto px-6 grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="rounded-2xl border p-8" style="background:var(--c-bg);border-color:var(--c-line);">
            <div class="w-11 h-11 rounded-[12px] border flex items-center justify-center text-xl mb-5"
                 style="background:var(--c-accent-l);border-color:var(--c-accent-ring);color:var(--c-accent);">
                <i class="ti ti-eye"></i>
            </div>
            <h2 class="text-[1.15rem] font-bold mb-3" style="font-family:var(--f-display);color:var(--c-ink);">Visi</h2>
            <p class="text-[.9rem] leading-[1.8]" style="color:var(--c-ink-3);">
                Menjadi platform manajemen pondok pesantren terpercaya yang membantu ribuan pondok di Indonesia mengelola operasional mereka secara digital, efisien, dan transparan.
            </p>
        </div>
        <div class="rounded-2xl border p-8" style="background:var(--c-bg);border-color:var(--c-line);">
            <div class="w-11 h-11 rounded-[12px] border flex items-center justify-center text-xl mb-5"
                 style="background:var(--c-accent-l);border-color:var(--c-accent-ring);color:var(--c-accent);">
                <i class="ti ti-target"></i>
            </div>
            <h2 class="text-[1.15rem] font-bold mb-3" style="font-family:var(--f-display);color:var(--c-ink);">Misi</h2>
            <p class="text-[.9rem] leading-[1.8]" style="color:var(--c-ink-3);">
                Menyediakan sistem yang mudah dipakai, terjangkau, dan lengkap  mulai dari data santri, keuangan, absensi, hingga komunikasi wali  dalam satu platform yang bisa diakses dari mana saja.
            </p>
        </div>
    </div>
</section>

{{-- ── NILAI-NILAI ── --}}
<section class="py-16 border-t" style="border-color:var(--c-line);">
    <div class="max-w-[1000px] mx-auto px-6">
        <div class="text-center mb-10">
            <p class="text-[.69rem] font-bold tracking-[.13em] uppercase mb-2" style="color:var(--c-accent);">Prinsip kami</p>
            <h2 class="text-[clamp(1.4rem,3vw,2rem)] font-extrabold tracking-[-0.04em]"
                style="font-family:var(--f-display);color:var(--c-ink);">Nilai yang memandu setiap keputusan</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ([
                ['ti ti-device-mobile-check', 'Mudah Digunakan',   'Antarmuka yang sederhana dan intuitif  tidak butuh pelatihan berhari-hari untuk memulai.'],
                ['ti ti-lock',                'Keamanan Data',      'Data santri dan keuangan pondok disimpan dengan enkripsi dan isolasi per-tenant.'],
                ['ti ti-refresh',             'Selalu Berkembang',  'Fitur terus diperbarui berdasarkan masukan langsung dari pengelola pondok yang menggunakan sistem ini.'],
                ['ti ti-headset',             'Dukungan Nyata',     'Tim kami bisa dihubungi langsung. Bukan hanya chatbot, tapi manusia yang memahami konteks pondok.'],
                ['ti ti-coins',               'Harga Proporsional', 'Biaya berdasarkan jumlah santri  pondok kecil tidak membayar seperti pondok besar.'],
                ['ti ti-building-community',  'Fokus Pesantren',    'Bukan solusi generik. Setiap fitur dirancang khusus untuk ekosistem pondok pesantren.'],
            ] as [$icon, $title, $desc])
                <div class="rounded-xl border p-6 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                     style="background:var(--c-bg);border-color:var(--c-line);">
                    <div class="w-10 h-10 rounded-[10px] border flex items-center justify-center text-[1.1rem] mb-4"
                         style="background:var(--c-accent-l);border-color:var(--c-accent-ring);color:var(--c-accent);">
                        <i class="{{ $icon }}"></i>
                    </div>
                    <p class="text-[.95rem] font-bold mb-2" style="font-family:var(--f-display);color:var(--c-ink);">{{ $title }}</p>
                    <p class="text-[.84rem] leading-[1.7]" style="color:var(--c-ink-3);">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── CTA ── --}}
<section class="py-16 border-t" style="border-color:var(--c-line);background:var(--c-bg-2);">
    <div class="max-w-[600px] mx-auto px-6 text-center">
        <h2 class="text-[clamp(1.4rem,3vw,2rem)] font-extrabold tracking-[-0.04em] mb-4"
            style="font-family:var(--f-display);color:var(--c-ink);">Ada pertanyaan tentang MondokQu?</h2>
        <p class="text-[.9rem] leading-[1.8] mb-7" style="color:var(--c-ink-3);">
            Kami senang mendengar dari Anda  baik tentang fitur, harga, maupun proses aktivasi akun pondok.
        </p>
        <div class="flex flex-wrap gap-3 justify-center">
            <a href="https://wa.me/{{ config('saas.admin_whatsapp') }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-5 py-2.5 text-[.9rem] font-semibold rounded-lg border btn-primary">
                <i class="ti ti-brand-whatsapp"></i> Hubungi Kami
            </a>
            <a href="{{ route('faq') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 text-[.9rem] font-semibold rounded-lg border transition-all duration-150"
               style="background:var(--c-bg);color:var(--c-ink-2);border-color:var(--c-line);"
               onmouseover="this.style.background='var(--c-bg-3)';this.style.color='var(--c-ink)'"
               onmouseout="this.style.background='var(--c-bg)';this.style.color='var(--c-ink-2)'">
                <i class="ti ti-help-circle"></i> Lihat FAQ
            </a>
        </div>
    </div>
</section>

@endsection
