<?php

namespace App\Modules\KesehatanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KesehatanImunisasi;
use App\Models\KesehatanObat;
use App\Models\KesehatanPemeriksaan;
use App\Models\KesehatanRekamMedis;
use App\Models\Santri;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KesehatanQuDashboardController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function __invoke(Request $request): View
    {
        $currentUser = $request->user();

        $totalSantri = Santri::query()
            ->visibleTo($currentUser)
            ->count();

        $rekamMedisTerisi = KesehatanRekamMedis::query()
            ->visibleTo($currentUser)
            ->count();

        $pemeriksaanBulanIni = KesehatanPemeriksaan::query()
            ->visibleTo($currentUser)
            ->whereMonth('tanggal_pemeriksaan', now()->month)
            ->whereYear('tanggal_pemeriksaan', now()->year)
            ->count();

        $pemeriksaanHariIni = KesehatanPemeriksaan::query()
            ->visibleTo($currentUser)
            ->whereDate('tanggal_pemeriksaan', now())
            ->count();

        $obatStokHabis = KesehatanObat::query()
            ->visibleTo($currentUser)
            ->where('stok', '<=', 0)
            ->count();

        $obatExpired = KesehatanObat::query()
            ->visibleTo($currentUser)
            ->where('expired_date', '<=', now())
            ->whereNotNull('expired_date')
            ->count();

        $imunisasiTertunda = KesehatanImunisasi::query()
            ->visibleTo($currentUser)
            ->where('status', KesehatanImunisasi::STATUS_BELUM)
            ->count();

        $obatStokHabisList = KesehatanObat::query()
            ->visibleTo($currentUser)
            ->where('stok', '<=', 0)
            ->limit(5)
            ->get();

        $obatExpiredList = KesehatanObat::query()
            ->visibleTo($currentUser)
            ->where('expired_date', '<=', now())
            ->whereNotNull('expired_date')
            ->limit(5)
            ->get();

        $imunisasiTertundaList = KesehatanImunisasi::query()
            ->visibleTo($currentUser)
            ->with('santri')
            ->where('status', KesehatanImunisasi::STATUS_BELUM)
            ->limit(5)
            ->get();

        $pemeriksaanTerbaru = KesehatanPemeriksaan::query()
            ->visibleTo($currentUser)
            ->with(['santri', 'pencatat'])
            ->latest()
            ->limit(10)
            ->get();

        return view('modules.kesehatan-qu.dashboard', [
            'totalSantri' => $totalSantri,
            'rekamMedisTerisi' => $rekamMedisTerisi,
            'pemeriksaanBulanIni' => $pemeriksaanBulanIni,
            'pemeriksaanHariIni' => $pemeriksaanHariIni,
            'obatStokHabis' => $obatStokHabis,
            'obatExpired' => $obatExpired,
            'imunisasiTertunda' => $imunisasiTertunda,
            'obatStokHabisList' => $obatStokHabisList,
            'obatExpiredList' => $obatExpiredList,
            'imunisasiTertundaList' => $imunisasiTertundaList,
            'pemeriksaanTerbaru' => $pemeriksaanTerbaru,
        ]);
    }
}
