<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionStatusController extends Controller
{
    /**
     * Display the tenant subscription blocked page with actionable guidance.
     */
    public function showExpired(Request $request): View
    {
        $tenant = $request->user()?->tenant;

        return view('subscription.expired', [
            'subscriptionContext' => $this->buildSubscriptionContext($tenant),
        ]);
    }

    /**
     * Build a presentation-friendly subscription status payload.
     *
     * @return array<string, mixed>
     */
    protected function buildSubscriptionContext(?Tenant $tenant): array
    {
        if (! $tenant) {
            return [
                'title' => 'Tenant Belum Terhubung',
                'badge' => 'Perlu Tindak Lanjut',
                'badge_color' => 'warning',
                'description' => 'Akun Anda belum terhubung ke tenant pondok yang valid.',
                'detail' => 'Hubungi admin platform agar akun Anda ditautkan ke tenant yang benar sebelum melanjutkan operasional.',
                'action' => 'Minta admin platform mengecek relasi akun dan tenant Anda.',
            ];
        }

        return match ($tenant->subscription_status) {
            Tenant::SUBSCRIPTION_TRIAL => [
                'title' => $tenant->name,
                'badge' => 'Masa Trial Berakhir',
                'badge_color' => 'warning',
                'description' => 'Masa trial tenant ini sudah habis.',
                'detail' => 'Trial terakhir tercatat sampai '.optional($tenant->trial_ends_at)->translatedFormat('d M Y H:i').'.',
                'action' => 'Hubungi admin platform untuk aktivasi subscription berbayar atau perpanjangan trial jika diperlukan.',
            ],
            Tenant::SUBSCRIPTION_GRACE => [
                'title' => $tenant->name,
                'badge' => 'Grace Period Berakhir',
                'badge_color' => 'warning',
                'description' => 'Masa toleransi akses tenant ini sudah habis.',
                'detail' => 'Grace period terakhir tercatat sampai '.optional($tenant->grace_ends_at)->translatedFormat('d M Y H:i').'.',
                'action' => 'Segera konfirmasi pembayaran atau minta aktivasi ulang ke admin platform agar akses operasional dibuka kembali.',
            ],
            Tenant::SUBSCRIPTION_EXPIRED => [
                'title' => $tenant->name,
                'badge' => 'Subscription Expired',
                'badge_color' => 'danger',
                'description' => 'Langganan tenant saat ini sudah berakhir sehingga akses operasional dibatasi.',
                'detail' => $this->expiredTenantDetail($tenant),
                'action' => 'Hubungi admin platform untuk perpanjangan paket atau aktivasi ulang tenant.',
            ],
            Tenant::SUBSCRIPTION_ACTIVE => [
                'title' => $tenant->name,
                'badge' => 'Subscription Aktif',
                'badge_color' => 'success',
                'description' => 'Tenant ini sebenarnya masih aktif.',
                'detail' => 'Jika Anda tetap melihat halaman ini, minta admin platform memeriksa ulang status akun atau tenant Anda.',
                'action' => 'Laporkan kondisi ini ke admin platform karena akses Anda seharusnya masih terbuka.',
            ],
            Tenant::SUBSCRIPTION_DELETING => [
                'title' => $tenant->name,
                'badge' => 'Dalam Penghapusan',
                'badge_color' => 'danger',
                'description' => 'Tenant ini sedang masuk antrean penghapusan permanen.',
                'detail' => 'Akses operasional diblokir selama proses penghapusan data berjalan di background queue.',
                'action' => 'Hubungi admin platform jika penghapusan tenant ini perlu diverifikasi.',
            ],
            default => [
                'title' => $tenant->name,
                'badge' => 'Status Tidak Diketahui',
                'badge_color' => 'secondary',
                'description' => 'Status langganan tenant belum dapat dipastikan.',
                'detail' => 'Periksa kembali data tenant dan status subscription di panel SaaS.',
                'action' => 'Hubungi admin platform untuk verifikasi status tenant Anda.',
            ],
        };
    }

    protected function expiredTenantDetail(Tenant $tenant): string
    {
        if ($tenant->grace_ends_at?->isPast()) {
            return 'Grace period terakhir tercatat sampai '.$tenant->grace_ends_at->translatedFormat('d M Y H:i').'.';
        }

        if ($tenant->subscription_ends_at?->isPast()) {
            return 'Subscription terakhir tercatat aktif sampai '.$tenant->subscription_ends_at->translatedFormat('d M Y H:i').'.';
        }

        if ($tenant->trial_ends_at?->isPast()) {
            return 'Trial terakhir tercatat sampai '.$tenant->trial_ends_at->translatedFormat('d M Y H:i').'.';
        }

        return 'Status tenant tercatat expired. Hubungi admin platform untuk memeriksa periode akses terakhir.';
    }
}
