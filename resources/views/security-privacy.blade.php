@extends('layouts-public.app')

@section('title', 'Keamanan & Privasi  MondokQu')
@section('meta_description', 'Kebijakan keamanan dan privasi data MondokQu  bagaimana kami melindungi data santri dan operasional pondok pesantren Anda.')

@section('content')

{{-- ── PAGE HERO ── --}}
<section class="pt-20 pb-12 text-center" style="background:var(--c-bg);">
    <div class="max-w-[680px] mx-auto px-6">
        <div class="inline-flex items-center gap-1.5 text-[.71rem] font-bold tracking-[.12em] uppercase
                    px-[.9rem] py-[.3rem] rounded-full border mb-6"
             style="color:var(--c-accent);border-color:var(--c-accent-ring);background:var(--c-accent-l);">
            <i class="ti ti-shield-lock"></i> Keamanan &amp; Privasi
        </div>
        <h1 class="text-[clamp(1.9rem,4vw,2.8rem)] font-extrabold tracking-[-0.04em] leading-[1.1] mb-4"
            style="font-family:var(--f-display);color:var(--c-ink);">
            Kebijakan Keamanan &amp; Privasi
        </h1>
        <p class="text-[.875rem]" style="color:var(--c-ink-4);">
            Berlaku efektif sejak: <strong style="color:var(--c-ink-3);">1 Januari 2025</strong>
        </p>
    </div>
</section>

{{-- ── RINGKASAN KEAMANAN ── --}}
<section class="py-14 border-t" style="border-color:var(--c-line);">
    <div class="max-w-[1000px] mx-auto px-6">
        <div class="text-center mb-10">
            <p class="text-[.69rem] font-bold tracking-[.13em] uppercase mb-2" style="color:var(--c-accent);">Komitmen kami</p>
            <h2 class="text-[clamp(1.3rem,3vw,1.85rem)] font-extrabold tracking-[-0.04em]"
                style="font-family:var(--f-display);color:var(--c-ink);">Data Anda dilindungi dengan standar ketat</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ([
                ['ti ti-building-skyscraper', 'Isolasi Data Per Pondok',  'Setiap pondok memiliki database tersendiri. Data antar pondok tidak pernah tercampur dalam kondisi apapun.'],
                ['ti ti-lock',                'Enkripsi End-to-End',       'Semua koneksi menggunakan HTTPS/TLS. Data sensitif dienkripsi sebelum disimpan ke database.'],
                ['ti ti-key',                 'Kontrol Akses Berbasis Peran', 'Setiap pengguna hanya bisa mengakses data dan fitur yang sesuai dengan perannya.'],
                ['ti ti-database-backup',     'Backup Otomatis',           'Data dicadangkan secara otomatis secara berkala. Anda juga bisa memicu backup manual kapan saja.'],
                ['ti ti-eye-off',             'Tidak Dijual ke Pihak Ketiga', 'Data Anda tidak pernah dijual, dibagikan, atau digunakan untuk kepentingan iklan oleh pihak manapun.'],
                ['ti ti-activity',            'Audit Log',                 'Setiap aksi penting dalam sistem dicatat secara otomatis untuk keperluan audit dan keamanan.'],
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

{{-- ── KEBIJAKAN PRIVASI DETAIL ── --}}
<section class="py-14 border-t" style="border-color:var(--c-line);background:var(--c-bg-2);">
    <div class="max-w-[760px] mx-auto px-6">

        <div class="text-center mb-10">
            <h2 class="text-[clamp(1.3rem,3vw,1.85rem)] font-extrabold tracking-[-0.04em]"
                style="font-family:var(--f-display);color:var(--c-ink);">Kebijakan Privasi</h2>
        </div>

        @php
        $sections = [
            ['num' => '1', 'title' => 'Data yang Kami Kumpulkan', 'content' => '
                <p class="mb-3">Kami mengumpulkan data berikut untuk keperluan operasional layanan:</p>
                <ul class="list-disc pl-5 flex flex-col gap-2">
                    <li><strong>Data identitas pengguna:</strong> nama, alamat email, nomor telepon yang digunakan saat pendaftaran akun.</li>
                    <li><strong>Data operasional pondok:</strong> informasi santri, absensi, keuangan, dan data lain yang Anda masukkan ke dalam sistem.</li>
                    <li><strong>Data teknis:</strong> alamat IP, jenis browser, dan log aktivitas sistem untuk keperluan keamanan dan pemeliharaan.</li>
                </ul>
                <p class="mt-3">Kami tidak mengumpulkan data di luar yang diperlukan untuk operasional layanan.</p>
            '],
            ['num' => '2', 'title' => 'Cara Kami Menggunakan Data', 'content' => '
                <ul class="list-disc pl-5 flex flex-col gap-2">
                    <li>Menjalankan dan meningkatkan layanan MondokQu.</li>
                    <li>Memproses pembayaran berlangganan.</li>
                    <li>Mengirim notifikasi sistem yang relevan (bukan spam promosi).</li>
                    <li>Mendeteksi dan mencegah akses tidak sah serta penipuan.</li>
                    <li>Memenuhi kewajiban hukum yang berlaku di Indonesia.</li>
                </ul>
            '],
            ['num' => '3', 'title' => 'Penyimpanan dan Retensi Data', 'content' => '
                <p class="mb-3">Data Anda disimpan di server yang berlokasi di Indonesia atau wilayah yang memiliki perlindungan data setara. Kami menerapkan:</p>
                <ul class="list-disc pl-5 flex flex-col gap-2">
                    <li>Enkripsi data saat istirahat (<em>at rest</em>) dan saat transmisi (<em>in transit</em>).</li>
                    <li>Retensi data aktif selama masa berlangganan aktif.</li>
                    <li>Retensi data pasif selama periode grace (30 hari) setelah berlangganan berakhir, sebelum dihapus permanen.</li>
                </ul>
            '],
            ['num' => '4', 'title' => 'Berbagi Data dengan Pihak Ketiga', 'content' => '
                <p class="mb-3">Kami <strong>tidak menjual</strong> data Anda. Kami hanya berbagi data dengan pihak ketiga yang diperlukan untuk operasional layanan, seperti:</p>
                <ul class="list-disc pl-5 flex flex-col gap-2">
                    <li>Penyedia infrastruktur cloud (server hosting).</li>
                    <li>Layanan pemrosesan pembayaran.</li>
                </ul>
                <p class="mt-3">Semua pihak ketiga terikat perjanjian kerahasiaan dan dilarang menggunakan data Anda untuk kepentingan apapun di luar lingkup layanan yang disepakati.</p>
            '],
            ['num' => '5', 'title' => 'Hak-Hak Anda', 'content' => '
                <p class="mb-3">Sebagai pengguna, Anda memiliki hak untuk:</p>
                <ul class="list-disc pl-5 flex flex-col gap-2">
                    <li><strong>Mengakses</strong> data yang kami simpan tentang akun Anda.</li>
                    <li><strong>Mengoreksi</strong> data yang tidak akurat.</li>
                    <li><strong>Mengekspor</strong> data operasional Anda dalam format yang dapat dibaca.</li>
                    <li><strong>Menghapus</strong> akun dan data Anda dengan mengajukan permintaan ke tim kami.</li>
                </ul>
            '],
            ['num' => '6', 'title' => 'Cookie dan Penyimpanan Lokal', 'content' => '
                <p>MondokQu menggunakan cookie sesi untuk menjaga status login Anda dan penyimpanan lokal browser untuk preferensi tampilan (seperti tema terang/gelap). Tidak ada cookie pelacak iklan yang digunakan.</p>
            '],
            ['num' => '7', 'title' => 'Insiden Keamanan', 'content' => '
                <p>Jika terjadi insiden keamanan yang berdampak pada data Anda, kami berkomitmen untuk memberitahu Anda dalam waktu 72 jam setelah insiden diketahui, beserta langkah-langkah mitigasi yang telah dan akan dilakukan.</p>
            '],
            ['num' => '8', 'title' => 'Perubahan Kebijakan', 'content' => '
                <p>Kami dapat memperbarui kebijakan ini dari waktu ke waktu. Perubahan material akan diberitahukan melalui email atau notifikasi dalam sistem minimal 14 hari sebelum berlaku.</p>
            '],
            ['num' => '9', 'title' => 'Hubungi Kami', 'content' => '
                <p>Untuk pertanyaan, permintaan akses data, atau laporan insiden keamanan:</p>
                <ul class="list-disc pl-5 mt-3 flex flex-col gap-1">
                    <li>WhatsApp: <a href="https://wa.me/6285117511220" target="_blank" rel="noopener" style="color:var(--c-accent);">+62 851-1751-1220</a></li>
                </ul>
            '],
        ];
        @endphp

        <div class="flex flex-col gap-8">
            @foreach ($sections as $sec)
                <div>
                    <h3 class="flex items-center gap-3 text-[1.05rem] font-bold mb-4"
                        style="font-family:var(--f-display);color:var(--c-ink);">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-[.75rem] font-extrabold shrink-0"
                              style="background:var(--c-accent);color:#fff;">{{ $sec['num'] }}</span>
                        {{ $sec['title'] }}
                    </h3>
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
<section class="py-12 border-t" style="border-color:var(--c-line);">
    <div class="max-w-[760px] mx-auto px-6 flex flex-wrap gap-4 items-center justify-between">
        <p class="text-[.875rem]" style="color:var(--c-ink-3);">Dokumen terkait:</p>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('terms') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-[.84rem] font-medium rounded-lg border transition-all duration-150"
               style="background:var(--c-bg);color:var(--c-ink-2);border-color:var(--c-line);"
               onmouseover="this.style.background='var(--c-bg-3)'" onmouseout="this.style.background='var(--c-bg)'">
                <i class="ti ti-file-description"></i> Syarat &amp; Ketentuan
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
