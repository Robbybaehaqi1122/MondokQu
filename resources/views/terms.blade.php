@extends('layouts-public.app')

@section('title', 'Syarat & Ketentuan  MondokQu')
@section('meta_description', 'Syarat dan ketentuan penggunaan layanan MondokQu  sistem manajemen pondok pesantren.')

@section('content')

{{-- ── PAGE HERO ── --}}
<section class="pt-20 pb-12 text-center" style="background:var(--c-bg);">
    <div class="max-w-[680px] mx-auto px-6">
        <div class="inline-flex items-center gap-1.5 text-[.71rem] font-bold tracking-[.12em] uppercase
                    px-[.9rem] py-[.3rem] rounded-full border mb-6"
             style="color:var(--c-accent);border-color:var(--c-accent-ring);background:var(--c-accent-l);">
            <i class="ti ti-file-description"></i> Syarat &amp; Ketentuan
        </div>
        <h1 class="text-[clamp(1.9rem,4vw,2.8rem)] font-extrabold tracking-[-0.04em] leading-[1.1] mb-4"
            style="font-family:var(--f-display);color:var(--c-ink);">
            Syarat &amp; Ketentuan Layanan
        </h1>
        <p class="text-[.875rem]" style="color:var(--c-ink-4);">
            Berlaku efektif sejak: <strong style="color:var(--c-ink-3);">1 Januari 2025</strong>
        </p>
    </div>
</section>

{{-- ── KONTEN ── --}}
<section class="py-14 border-t" style="border-color:var(--c-line);">
    <div class="max-w-[760px] mx-auto px-6">

        {{-- Intro --}}
        <div class="rounded-xl border p-6 mb-10"
             style="background:var(--c-bg-2);border-color:var(--c-line);">
            <div class="flex gap-3">
                <i class="ti ti-info-circle text-[1.1rem] mt-[2px] shrink-0" style="color:var(--c-accent);"></i>
                <p class="text-[.875rem] leading-[1.8]" style="color:var(--c-ink-2);">
                    Dengan mendaftar dan menggunakan layanan MondokQu, Anda menyatakan telah membaca, memahami, dan menyetujui seluruh syarat dan ketentuan yang tercantum di halaman ini. Jika Anda tidak menyetujui, harap tidak menggunakan layanan ini.
                </p>
            </div>
        </div>

        @php
        $sections = [
            ['num' => '1', 'title' => 'Definisi', 'content' => '
                <ul class="list-disc pl-5 flex flex-col gap-2">
                    <li><strong>"Layanan"</strong> mengacu pada platform MondokQu yang dapat diakses melalui situs web mondokqu.app dan subdomain terkait.</li>
                    <li><strong>"Pengguna"</strong> adalah individu atau institusi yang mendaftar dan menggunakan layanan MondokQu.</li>
                    <li><strong>"Pondok"</strong> atau <strong>"Tenant"</strong> adalah lembaga pondok pesantren yang memiliki akun aktif di platform ini.</li>
                    <li><strong>"Data Santri"</strong> adalah semua informasi yang berkaitan dengan peserta didik yang dikelola melalui sistem ini.</li>
                    <li><strong>"Kami"</strong> atau <strong>"MondokQu"</strong> merujuk pada pengelola dan pengembang platform ini.</li>
                </ul>
            '],
            ['num' => '2', 'title' => 'Penggunaan Layanan', 'content' => '
                <p class="mb-3">Pengguna diizinkan menggunakan layanan MondokQu untuk keperluan manajemen operasional pondok pesantren yang sah. Pengguna dilarang:</p>
                <ul class="list-disc pl-5 flex flex-col gap-2">
                    <li>Menggunakan layanan untuk tujuan ilegal atau melanggar hukum yang berlaku di Indonesia.</li>
                    <li>Mengakses, merusak, atau mengganggu sistem, server, atau jaringan yang terhubung dengan layanan.</li>
                    <li>Menyebarkan data pribadi santri kepada pihak yang tidak berwenang.</li>
                    <li>Mencoba mengakses data tenant lain tanpa izin.</li>
                    <li>Melakukan reverse engineering atau menyalin kode sumber platform.</li>
                </ul>
            '],
            ['num' => '3', 'title' => 'Pendaftaran Akun', 'content' => '
                <p class="mb-3">Untuk menggunakan layanan, Anda wajib melengkapi proses pendaftaran dengan informasi yang akurat dan terkini. Anda bertanggung jawab penuh atas:</p>
                <ul class="list-disc pl-5 flex flex-col gap-2">
                    <li>Kerahasiaan kredensial akun (username dan password).</li>
                    <li>Semua aktivitas yang terjadi di bawah akun Anda.</li>
                    <li>Memberitahu kami segera jika ada penggunaan akun yang tidak sah.</li>
                </ul>
            '],
            ['num' => '4', 'title' => 'Harga dan Pembayaran', 'content' => '
                <p class="mb-3">Layanan MondokQu menggunakan skema berlangganan berbasis jumlah santri:</p>
                <ul class="list-disc pl-5 flex flex-col gap-2">
                    <li><strong>Biaya pendaftaran</strong> sebesar Rp 15.000 per santri, dibayar sekali saat pertama kali menginput data santri.</li>
                    <li><strong>Biaya berlangganan</strong> sebesar Rp 8.000 per santri per bulan.</li>
                    <li>Harga dapat berubah dengan pemberitahuan minimal 30 hari sebelum berlaku.</li>
                    <li>Tidak ada pengembalian dana untuk pembayaran yang sudah dilakukan, kecuali dalam kondisi tertentu yang disetujui secara tertulis.</li>
                </ul>
            '],
            ['num' => '5', 'title' => 'Penangguhan dan Penghentian', 'content' => '
                <p class="mb-3">Kami berhak menangguhkan atau mengakhiri akses Anda ke layanan jika:</p>
                <ul class="list-disc pl-5 flex flex-col gap-2">
                    <li>Terdapat keterlambatan pembayaran berlangganan lebih dari periode grace yang ditentukan.</li>
                    <li>Anda melanggar syarat dan ketentuan ini.</li>
                    <li>Terdapat aktivitas yang mencurigakan atau berpotensi merugikan sistem dan pengguna lain.</li>
                </ul>
                <p class="mt-3">Setelah penangguhan, data Anda akan dipertahankan selama periode grace sebelum dihapus permanen. Anda dapat mengekspor data selama periode tersebut.</p>
            '],
            ['num' => '6', 'title' => 'Kepemilikan Data', 'content' => '
                <p>Data yang Anda masukkan ke dalam sistem  termasuk data santri, keuangan, dan operasional pondok  tetap sepenuhnya milik Anda. MondokQu tidak mengklaim kepemilikan atas data tersebut dan tidak akan menggunakannya untuk kepentingan komersial tanpa izin Anda. Kami hanya menggunakan data untuk keperluan operasional sistem (backup, pemulihan, keamanan).</p>
            '],
            ['num' => '7', 'title' => 'Batasan Tanggung Jawab', 'content' => '
                <p class="mb-3">MondokQu tidak bertanggung jawab atas:</p>
                <ul class="list-disc pl-5 flex flex-col gap-2">
                    <li>Kerugian tidak langsung yang timbul dari gangguan layanan akibat force majeure (bencana alam, gangguan infrastruktur pihak ketiga, dll).</li>
                    <li>Kesalahan data yang dimasukkan oleh pengguna.</li>
                    <li>Keputusan operasional pondok yang diambil berdasarkan laporan dari sistem.</li>
                </ul>
                <p class="mt-3">Kami berkomitmen menjaga uptime sistem dengan standar terbaik, namun tidak dapat menjamin layanan bebas gangguan 100% setiap saat.</p>
            '],
            ['num' => '8', 'title' => 'Perubahan Syarat & Ketentuan', 'content' => '
                <p>Kami dapat memperbarui syarat dan ketentuan ini dari waktu ke waktu. Perubahan signifikan akan diberitahukan melalui email atau notifikasi dalam sistem minimal 14 hari sebelum berlaku. Penggunaan layanan setelah pemberitahuan tersebut dianggap sebagai persetujuan terhadap perubahan.</p>
            '],
            ['num' => '9', 'title' => 'Hukum yang Berlaku', 'content' => '
                <p>Syarat dan ketentuan ini tunduk pada hukum Republik Indonesia. Setiap sengketa yang timbul akan diselesaikan secara musyawarah, dan jika tidak tercapai kesepakatan, akan diselesaikan melalui pengadilan yang berwenang di Indonesia.</p>
            '],
            ['num' => '10', 'title' => 'Hubungi Kami', 'content' => '
                <p>Jika Anda memiliki pertanyaan terkait syarat dan ketentuan ini, silakan hubungi kami melalui:</p>
                <ul class="list-disc pl-5 mt-3 flex flex-col gap-1">
                    <li>WhatsApp: <a href="https://wa.me/6285117511220" target="_blank" rel="noopener" style="color:var(--c-accent);">+62 851-1751-1220</a></li>
                </ul>
            '],
        ];
        @endphp

        <div class="flex flex-col gap-8">
            @foreach ($sections as $sec)
                <div id="section-{{ $sec['num'] }}">
                    <h2 class="flex items-center gap-3 text-[1.05rem] font-bold mb-4"
                        style="font-family:var(--f-display);color:var(--c-ink);">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-[.75rem] font-extrabold shrink-0"
                              style="background:var(--c-accent);color:#fff;">{{ $sec['num'] }}</span>
                        {{ $sec['title'] }}
                    </h2>
                    <div class="text-[.875rem] leading-[1.8]" style="color:var(--c-ink-3);">
                        {!! $sec['content'] !!}
                    </div>
                    @if (!$loop->last)
                        <hr class="mt-8" style="border-color:var(--c-line);">
                    @endif
                </div>
            @endforeach
        </div>

    </div>
</section>

{{-- ── LINK KE HALAMAN TERKAIT ── --}}
<section class="py-12 border-t" style="border-color:var(--c-line);background:var(--c-bg-2);">
    <div class="max-w-[760px] mx-auto px-6 flex flex-wrap gap-4 items-center justify-between">
        <p class="text-[.875rem]" style="color:var(--c-ink-3);">Dokumen terkait:</p>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('security-privacy') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-[.84rem] font-medium rounded-lg border transition-all duration-150"
               style="background:var(--c-bg);color:var(--c-ink-2);border-color:var(--c-line);"
               onmouseover="this.style.background='var(--c-bg-3)'" onmouseout="this.style.background='var(--c-bg)'">
                <i class="ti ti-shield-lock"></i> Keamanan &amp; Privasi
            </a>
            <a href="{{ route('faq') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-[.84rem] font-medium rounded-lg border transition-all duration-150"
               style="background:var(--c-bg);color:var(--c-ink-2);border-color:var(--c-line);"
               onmouseover="this.style.background='var(--c-bg-3)'" onmouseout="this.style.background='var(--c-bg)'">
                <i class="ti ti-help-circle"></i> FAQ
            </a>
        </div>
    </div>
</section>

@endsection
