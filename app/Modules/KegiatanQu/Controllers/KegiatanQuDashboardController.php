<?php

namespace App\Modules\KegiatanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Modules\KegiatanQu\Models\Kegiatan;
use App\Modules\KegiatanQu\Models\KegiatanPendaftaran;
use App\Modules\KegiatanQu\Models\KegiatanPertemuan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KegiatanQuDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            return view('modules.kegiatan-qu.dashboard', [
                'totalKegiatan' => 0,
                'kegiatanAktif' => 0,
                'totalPendaftar' => 0,
                'totalPertemuan' => 0,
                'totalPresensi' => 0,
                'kegiatanTerbaru' => collect(),
                'pendaftaranTerbaru' => collect(),
            ]);
        }

        $totalKegiatan = Kegiatan::withoutTenantScope()->where('tenant_id', $tenantId)->count();
        $kegiatanAktif = Kegiatan::withoutTenantScope()->where('tenant_id', $tenantId)->where('status', 'aktif')->count();
        $totalPendaftar = KegiatanPendaftaran::withoutTenantScope()->where('tenant_id', $tenantId)->count();
        $totalPertemuan = KegiatanPertemuan::withoutTenantScope()->where('tenant_id', $tenantId)->count();

        $totalPresensi = \App\Modules\KegiatanQu\Models\KegiatanPresensi::withoutTenantScope()
            ->where('tenant_id', $tenantId)->where('status', 'hadir')->count();

        $kegiatanTerbaru = Kegiatan::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with('pembina')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $pendaftaranTerbaru = KegiatanPendaftaran::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with(['kegiatan', 'santri'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('modules.kegiatan-qu.dashboard', compact(
            'totalKegiatan', 'kegiatanAktif', 'totalPendaftar', 'totalPertemuan',
            'totalPresensi', 'kegiatanTerbaru', 'pendaftaranTerbaru'
        ));
    }
}
