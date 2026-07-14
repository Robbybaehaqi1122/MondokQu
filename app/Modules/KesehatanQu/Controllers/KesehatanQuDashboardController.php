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

        $trenPemeriksaan = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = KesehatanPemeriksaan::query()
                ->visibleTo($currentUser)
                ->whereMonth('tanggal_pemeriksaan', $month->month)
                ->whereYear('tanggal_pemeriksaan', $month->year)
                ->count();
            $trenPemeriksaan->push([
                'label' => $month->translatedFormat('M Y'),
                'count' => $count,
            ]);
        }

        $topKeluhan = KesehatanPemeriksaan::query()
            ->visibleTo($currentUser)
            ->whereNotNull('keluhan')
            ->where('keluhan', '!=', '')
            ->selectRaw('keluhan, COUNT(*) as total')
            ->groupBy('keluhan')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topObat = \App\Models\KesehatanPemakaianObat::query()
            ->whereHas('pemeriksaan', fn ($q) => $q->visibleTo($currentUser))
            ->whereHas('obat')
            ->selectRaw('obat_id, COUNT(*) as total')
            ->groupBy('obat_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'label' => $item->obat?->nama_obat ?? '-',
                'count' => $item->total,
            ]);

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
            'trenPemeriksaan' => $trenPemeriksaan,
            'topKeluhan' => $topKeluhan,
            'topObat' => $topObat,
        ]);
    }
}
