<?php

namespace App\Modules\Akademik\Controllers;

use App\Models\MataPelajaran;
use App\Models\NilaiSantri;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class AkademikDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $currentUser = $request->user();

        $totalMapel = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->active()
            ->count();

        $totalNilai = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->count();

        $totalSantriDinilai = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->distinct('santri_id')
            ->count('santri_id');

        $rataRata = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->selectRaw('COALESCE(ROUND(AVG((nilai_pengetahuan + nilai_keterampilan) / 2)), 0) as avg')
            ->value('avg');

        $mapelTerbanyak = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->active()
            ->withCount('nilaiSantris')
            ->orderByDesc('nilai_santris_count')
            ->limit(5)
            ->get();

        $nilaiPerMapel = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->active()
            ->select('id', 'nama', 'kkm')
            ->selectSub(
                NilaiSantri::query()
                    ->whereColumn('mata_pelajaran_id', 'mata_pelajarans.id')
                    ->selectRaw('COALESCE(ROUND(AVG((nilai_pengetahuan + nilai_keterampilan) / 2)), 0)'),
                'rata_rata'
            )
            ->selectSub(
                NilaiSantri::query()
                    ->whereColumn('mata_pelajaran_id', 'mata_pelajarans.id')
                    ->selectRaw('COUNT(*)'),
                'total_nilai'
            )
            ->get();

        $semesters = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->distinct()
            ->orderBy('semester')
            ->pluck('semester');

        return view('modules.akademik.dashboard', [
            'totalMapel' => $totalMapel,
            'totalNilai' => $totalNilai,
            'totalSantriDinilai' => $totalSantriDinilai,
            'rataRata' => $rataRata,
            'mapelTerbanyak' => $mapelTerbanyak,
            'nilaiPerMapel' => $nilaiPerMapel,
            'semesters' => $semesters,
        ]);
    }
}
