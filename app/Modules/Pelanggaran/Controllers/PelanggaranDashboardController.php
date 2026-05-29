<?php

namespace App\Modules\Pelanggaran\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pelanggaran;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelanggaranDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $currentUser = $request->user();
        $today = now()->toDateString();

        $santriQuery = Santri::query()->visibleTo($currentUser)->where('status', Santri::STATUS_ACTIVE);
        $activeSantriCount = (clone $santriQuery)->count();

        $pelanggaranQuery = Pelanggaran::query()->visibleTo($currentUser);

        $totalPelanggaran = (clone $pelanggaranQuery)->count();
        $pelanggaranHariIni = (clone $pelanggaranQuery)->where('tanggal', $today)->count();
        $totalSantriDenganPelanggaran = (clone $pelanggaranQuery)->distinct('santri_id')->count('santri_id');
        $totalPoin = (clone $pelanggaranQuery)->sum('poin');

        $santriTertinggi = (clone $pelanggaranQuery)
            ->selectRaw('santri_id, SUM(poin) as total_poin, COUNT(*) as jumlah')
            ->with('santri')
            ->groupBy('santri_id')
            ->orderBy('total_poin', 'desc')
            ->limit(5)
            ->get();

        $recentPelanggaran = (clone $pelanggaranQuery)
            ->with(['santri', 'kategori', 'pencatat'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('modules.pelanggaran.dashboard', [
            'stats' => [
                'total_pelanggaran' => $totalPelanggaran,
                'hari_ini' => $pelanggaranHariIni,
                'santri_tercatat' => $totalSantriDenganPelanggaran,
                'total_poin' => $totalPoin,
                'santri_aktif' => $activeSantriCount,
            ],
            'santriTertinggi' => $santriTertinggi,
            'recentPelanggaran' => $recentPelanggaran,
            'today' => now(),
        ]);
    }
}
