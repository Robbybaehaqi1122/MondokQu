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

        $imunisasiTertunda = KesehatanImunisasi::query()
            ->visibleTo($currentUser)
            ->where('status', KesehatanImunisasi::STATUS_BELUM)
            ->count();

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
            'imunisasiTertunda' => $imunisasiTertunda,
            'pemeriksaanTerbaru' => $pemeriksaanTerbaru,
        ]);
    }
}
