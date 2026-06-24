<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {}

    public function index(): View
    {
        $currentUser      = auth()->user();
        $canSeeAdminStats = (bool) ($currentUser?->hasAnyRole(['Superadmin', 'Admin']) ?? false);
        $dashboardData    = $canSeeAdminStats
            ? $this->dashboardService->buildCachedDashboardData($currentUser)
            : null;

        $themeColor      = config('app.tenant_theme_color', '#0d9488');
        $primaryAction   = $currentUser ? route('dashboard') : route('login');
        $secondaryAction = $currentUser ? route('profile.edit') : route('login');
        $registerEnabled = \Illuminate\Support\Facades\Route::has('register');

        $heroMetrics = $canSeeAdminStats
            ? [
                ['label' => 'Total user',           'value' => data_get($dashboardData, 'stats.total_users', 0),                                                                       'hint' => 'akun pengelola aktif dan tidak aktif'],
                ['label' => 'Santri aktif',          'value' => data_get($dashboardData, 'santriStats.active_santri', 0),                                                              'hint' => 'data pondok yang sedang berjalan'],
                ['label' => 'Pembayaran bulan ini',  'value' => 'Rp '.number_format((int) data_get($dashboardData, 'financeStats.paid_this_month', 0), 0, ',', '.'),                   'hint' => 'nominal yang sudah tercatat'],
                ['label' => 'Tagihan menunggak',     'value' => data_get($dashboardData, 'financeStats.overdue_invoices', 0),                                                          'hint' => 'invoice yang perlu ditindaklanjuti'],
            ]
            : [
                ['label' => 'Mode',      'value' => 'SaaS',        'hint' => 'dirancang untuk banyak pondok dalam satu sistem'],
                ['label' => 'Akses',     'value' => 'Role-based',  'hint' => 'pengurus, bendahara, musyrif, dan wali santri'],
                ['label' => 'Data',      'value' => 'Terpusat',    'hint' => 'semua informasi santri berada di satu alur'],
                ['label' => 'Tampilan',  'value' => 'Responsif',   'hint' => 'nyaman dipakai di desktop maupun ponsel'],
            ];

        $featureCards = [
            ['icon' => 'ti ti-school',       'title' => 'Manajemen santri',    'description' => 'Kelola identitas, status, kamar, dan riwayat santri secara rapi tanpa spreadsheet berlapis.'],
            ['icon' => 'ti ti-clipboard-list','title' => 'Operasional harian', 'description' => 'Absensi, tahfidz, pelanggaran, dan kegiatan pondok bergerak di alur yang sama.'],
            ['icon' => 'ti ti-receipt-2',    'title' => 'Keuangan pondok',     'description' => 'Pantau tagihan, pembayaran, serta status tunggakan agar keputusan lebih cepat.'],
            ['icon' => 'ti ti-users-group',  'title' => 'Portal wali santri',  'description' => 'Informasi penting tetap terkoneksi ke wali santri tanpa membuat admin bekerja dua kali.'],
        ];

        $targetRoles = [
            ['name' => 'Admin',       'description' => 'Melihat ringkasan operasional dan kontrol akses platform.'],
            ['name' => 'Pengurus',    'description' => 'Mengelola data santri, kamar, dan kebutuhan harian.'],
            ['name' => 'Bendahara',   'description' => 'Mengecek pemasukan, tagihan, dan status pembayaran.'],
            ['name' => 'Wali Santri', 'description' => 'Memonitor perkembangan santri dari sisi keluarga.'],
        ];

        return view('index', compact(
            'currentUser', 'canSeeAdminStats', 'dashboardData',
            'themeColor', 'primaryAction', 'secondaryAction', 'registerEnabled',
            'heroMetrics', 'featureCards', 'targetRoles',
        ));
    }

    public function about(): View
    {
        return view('about', [
            'currentUser' => auth()->user(),
        ]);
    }

    public function faq(): View
    {
        return view('faq', [
            'currentUser' => auth()->user(),
        ]);
    }

    public function terms(): View
    {
        return view('terms', [
            'currentUser' => auth()->user(),
        ]);
    }

    public function securityPrivacy(): View
    {
        return view('security-privacy', [
            'currentUser' => auth()->user(),
        ]);
    }
}
