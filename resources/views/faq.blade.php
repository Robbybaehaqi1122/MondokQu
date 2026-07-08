@extends('layouts-public.app')

@section('title', 'FAQ  MondokQu')
@section('meta_description', 'Pertanyaan yang sering diajukan seputar MondokQu  sistem manajemen pondok pesantren.')

@push('styles')
<style>
/* Accordion arrow rotation */
.faq-item[open] .faq-arrow { transform: rotate(180deg); }
.faq-arrow { transition: transform .25s ease; }
.faq-item summary::-webkit-details-marker { display: none; }
.faq-item summary { list-style: none; }
</style>
@endpush

@section('content')

{{-- ── PAGE HERO ── --}}
<section class="pt-20 pb-14 text-center" style="background:var(--c-bg);">
    <div class="max-w-[680px] mx-auto px-6">
        <div class="inline-flex items-center gap-1.5 text-[.71rem] font-bold tracking-[.12em] uppercase
                    px-[.9rem] py-[.3rem] rounded-full border mb-6"
             style="color:var(--c-accent);border-color:var(--c-accent-ring);background:var(--c-accent-l);">
            <i class="ti ti-help-circle"></i> FAQ
        </div>
        <h1 class="text-[clamp(1.9rem,4vw,2.8rem)] font-extrabold tracking-[-0.04em] leading-[1.1] mb-5"
            style="font-family:var(--f-display);color:var(--c-ink);">
            Pertanyaan yang sering diajukan
        </h1>
        <p class="text-[1rem] leading-[1.8]" style="color:var(--c-ink-3);">
            Tidak menemukan jawaban yang Anda cari? Hubungi kami langsung melalui WhatsApp.
        </p>
    </div>
</section>

{{-- ── FAQ ACCORDION ── --}}
<section class="py-16 border-t" style="border-color:var(--c-line);">
    <div class="max-w-[760px] mx-auto px-6">

        @php
        $faqs = [
            'Umum' => [
                ['q' => 'Apa itu MondokQu?',
                 'a' => 'MondokQu adalah platform manajemen pondok pesantren berbasis SaaS (Software as a Service). Sistem ini memungkinkan pengelola pondok untuk mengelola data santri, absensi, keuangan, hafalan, pelanggaran, dan komunikasi dengan wali santri  semuanya dalam satu sistem berbasis web.'],
                ['q' => 'Apakah MondokQu bisa digunakan oleh banyak pondok sekaligus?',
                 'a' => 'Ya. MondokQu dirancang sebagai platform multi-tenant. Setiap pondok memiliki data yang sepenuhnya terpisah dan terisolasi dari pondok lain, namun tetap berjalan di atas infrastruktur yang sama.'],
                ['q' => 'Apakah perlu menginstal aplikasi khusus?',
                 'a' => 'Tidak. MondokQu sepenuhnya berbasis web dan dapat diakses melalui browser di komputer maupun ponsel. Tidak ada aplikasi yang perlu diinstal.'],
            ],
            'Akses & Pengguna' => [
                ['q' => 'Siapa saja yang bisa menggunakan MondokQu?',
                 'a' => 'MondokQu mendukung beberapa peran pengguna: Admin/Superadmin (pengelola utama), Pengurus (staff operasional), Bendahara (keuangan), Musyrif (pembimbing santri), dan Wali Santri (akses terbatas untuk memantau perkembangan anaknya).'],
                ['q' => 'Bagaimana cara mendaftarkan akun pondok?',
                 'a' => 'Hubungi tim kami melalui WhatsApp untuk proses aktivasi. Kami akan memandu Anda mulai dari pengaturan awal hingga pengisian data santri pertama.'],
                ['q' => 'Apakah wali santri bisa memantau kondisi anak mereka?',
                 'a' => 'Ya. Wali santri dapat mengakses portal khusus untuk melihat informasi kehadiran, tagihan, perkembangan hafalan, dan pesan dari pengurus  sesuai dengan hak akses yang diberikan pondok.'],
            ],
            'Fitur & Teknis' => [
                ['q' => 'Fitur apa saja yang tersedia?',
                 'a' => 'MondokQu mencakup lebih dari 20 fitur, di antaranya: manajemen data santri, absensi, hafalan & setoran, keuangan & tagihan, catatan pelanggaran, nilai & rapor, kesehatan santri, manajemen kamar, komunikasi wali, jadwal kegiatan, PPDB online, perpustakaan, cadangan data, dan laporan statistik.'],
                ['q' => 'Apakah ada fitur yang perlu dibeli terpisah?',
                 'a' => 'Tidak. Semua fitur sudah termasuk dalam satu paket berlangganan. Tidak ada add-on berbayar.'],
                ['q' => 'Apakah sistem bisa diakses dari ponsel?',
                 'a' => 'Ya. Antarmuka MondokQu responsif dan dirancang agar nyaman digunakan di layar ponsel maupun tablet.'],
            ],
            'Harga & Berlangganan' => [
                ['q' => 'Bagaimana skema harga MondokQu?',
                 'a' => 'Biaya terdiri dari dua komponen: biaya pendaftaran awal sebesar Rp 15.000 per santri (dibayar sekali saat pertama kali memasukkan data santri), dan biaya berlangganan bulanan sebesar Rp 8.000 per santri per bulan.'],
                ['q' => 'Apakah ada masa percobaan gratis?',
                 'a' => 'Hubungi tim kami untuk informasi terkait periode trial. Kami biasanya menyediakan onboarding terbimbing sebelum tagihan pertama berjalan.'],
                ['q' => 'Bagaimana cara pembayaran berlangganan?',
                 'a' => 'Detail metode pembayaran akan diinformasikan saat proses aktivasi akun. Tim kami akan memandu proses ini secara langsung.'],
            ],
            'Data & Keamanan' => [
                ['q' => 'Apakah data pondok kami aman?',
                 'a' => 'Ya. Setiap pondok memiliki database yang terisolasi (multi-tenant isolation). Data tidak pernah tercampur antar pondok. Seluruh koneksi menggunakan HTTPS dan data disimpan dengan standar keamanan yang ketat.'],
                ['q' => 'Apakah kami bisa mengekspor data kami?',
                 'a' => 'Ya. MondokQu menyediakan fitur ekspor data ke format Excel dan PDF, termasuk data santri, laporan keuangan, dan rekap absensi. Anda selalu memiliki kendali penuh atas data Anda.'],
                ['q' => 'Apa yang terjadi jika berlangganan berakhir?',
                 'a' => 'Akses akan ditangguhkan sementara. Data Anda tidak dihapus secara langsung  ada periode grace sebelum penghapusan permanen, dan Anda masih bisa mengekspor data selama periode tersebut.'],
            ],
        ];
        @endphp

        @foreach ($faqs as $category => $items)
            <div class="mb-8">
                <h2 class="text-[.75rem] font-bold tracking-[.12em] uppercase mb-3"
                    style="color:var(--c-accent);">{{ $category }}</h2>
                <div class="flex flex-col gap-2">
                    @foreach ($items as $idx => $item)
                        <details class="faq-item rounded-xl border overflow-hidden"
                                 style="background:var(--c-bg);border-color:var(--c-line);">
                            <summary class="flex items-center justify-between gap-4 px-5 py-4 cursor-pointer select-none
                                            text-[.9rem] font-semibold"
                                     style="color:var(--c-ink);">
                                <span>{{ $item['q'] }}</span>
                                <i class="ti ti-chevron-down faq-arrow text-[1rem] shrink-0" style="color:var(--c-ink-4);"></i>
                            </summary>
                            <div class="px-5 pb-5 pt-1 text-[.875rem] leading-[1.8] border-t"
                                 style="color:var(--c-ink-3);border-color:var(--c-line);">
                                {{ $item['a'] }}
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        @endforeach

    </div>
</section>

{{-- ── CTA ── --}}
<section class="py-14 border-t" style="border-color:var(--c-line);background:var(--c-bg-2);">
    <div class="max-w-[560px] mx-auto px-6 text-center">
        <h2 class="text-[1.3rem] font-extrabold tracking-[-0.03em] mb-3"
            style="font-family:var(--f-display);color:var(--c-ink);">Masih punya pertanyaan?</h2>
        <p class="text-[.875rem] leading-[1.8] mb-6" style="color:var(--c-ink-3);">
            Tim kami siap membantu. Hubungi kami langsung dan dapatkan jawaban yang sesuai dengan kebutuhan pondok Anda.
        </p>
        <a href="https://wa.me/{{ config('saas.admin_whatsapp') }}" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 px-5 py-2.5 text-[.9rem] font-semibold rounded-lg border btn-primary">
            <i class="ti ti-brand-whatsapp"></i> Chat via WhatsApp
        </a>
    </div>
</section>

@endsection
