<?php

namespace App\Modules\Akademik\Controllers;

use App\Models\MataPelajaran;
use App\Models\NilaiSantri;
use App\Models\Pelanggaran;
use App\Models\Santri;
use App\Models\TahfidzRecord;
use App\Models\TahfidzSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RaporController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = $request->user();

        $santris = Santri::query()
            ->visibleTo($currentUser)
            ->select('id', 'full_name', 'nis')
            ->orderBy('full_name')
            ->get();

        $semesters = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->distinct()
            ->orderBy('semester', 'desc')
            ->pluck('semester');

        return view('modules.akademik.rapor.index', [
            'santris' => $santris,
            'semesters' => $semesters,
        ]);
    }

    public function show(Request $request): View
    {
        $currentUser = $request->user();

        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'semester' => ['required', 'string'],
        ]);

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->with(['room', 'guardians'])
            ->findOrFail($validated['santri_id']);

        $nilais = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->where('semester', $validated['semester'])
            ->with(['mataPelajaran', 'inputBy'])
            ->get();

        $tahfidzStats = TahfidzRecord::query()
            ->visibleTo($currentUser)
            ->whereIn('tahfidz_session_id', TahfidzSession::query()
                ->where('santri_id', $santri->id)
                ->select('id')
            )
            ->selectRaw('COALESCE(SUM(verse_end - verse_start + 1), 0) as total_ayat')
            ->selectRaw('COUNT(*) as total_record')
            ->selectRaw("COALESCE(SUM(CASE WHEN evaluation = 'lancar' THEN 1 ELSE 0 END), 0) as lancar_count")
            ->first();

        $totalPoinPelanggaran = Pelanggaran::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->sum('poin');

        $rataRataKelas = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->where('semester', $validated['semester'])
            ->selectRaw('mata_pelajaran_id, COALESCE(ROUND(AVG((nilai_pengetahuan + nilai_keterampilan) / 2)), 0) as avg')
            ->groupBy('mata_pelajaran_id')
            ->pluck('avg', 'mata_pelajaran_id');

        $semesters = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->distinct()
            ->orderBy('semester', 'desc')
            ->pluck('semester');

        return view('modules.akademik.rapor.show', [
            'santri' => $santri,
            'semester' => $validated['semester'],
            'semesters' => $semesters,
            'nilais' => $nilais,
            'tahfidzStats' => $tahfidzStats,
            'totalPoinPelanggaran' => $totalPoinPelanggaran,
            'rataRataKelas' => $rataRataKelas,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $currentUser = $request->user();

        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'semester' => ['required', 'string'],
        ]);

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->with(['room', 'guardians'])
            ->findOrFail($validated['santri_id']);

        $nilais = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->where('semester', $validated['semester'])
            ->with(['mataPelajaran', 'inputBy'])
            ->get();

        $tahfidzStats = TahfidzRecord::query()
            ->visibleTo($currentUser)
            ->whereIn('tahfidz_session_id', TahfidzSession::query()
                ->where('santri_id', $santri->id)
                ->select('id')
            )
            ->selectRaw('COALESCE(SUM(verse_end - verse_start + 1), 0) as total_ayat')
            ->selectRaw('COUNT(*) as total_record')
            ->first();

        $totalPoinPelanggaran = Pelanggaran::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->sum('poin');

        $rataRataKelas = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->where('semester', $validated['semester'])
            ->selectRaw('mata_pelajaran_id, COALESCE(ROUND(AVG((nilai_pengetahuan + nilai_keterampilan) / 2)), 0) as avg')
            ->groupBy('mata_pelajaran_id')
            ->pluck('avg', 'mata_pelajaran_id');

        $pdf = Pdf::loadView('exports.pdf.rapor-santri', [
            'santri' => $santri,
            'semester' => $validated['semester'],
            'nilais' => $nilais,
            'tahfidzStats' => $tahfidzStats,
            'totalPoinPelanggaran' => $totalPoinPelanggaran,
            'rataRataKelas' => $rataRataKelas,
        ]);

        $filename = 'rapor-'.$santri->full_name.'-'.$validated['semester'].'.pdf';

        return $pdf->download($filename);
    }

    public function chartData(Request $request): \Illuminate\Http\JsonResponse
    {
        $currentUser = $request->user();

        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
        ]);

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->findOrFail($validated['santri_id']);

        $nilais = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->with('mataPelajaran')
            ->get()
            ->groupBy('semester');

        $semesters = $nilais->keys()->sort()->values();

        $mapels = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->active()
            ->orderBy('nama')
            ->pluck('nama', 'id');

        $series = [];
        foreach ($mapels as $id => $nama) {
            $data = [];
            foreach ($semesters as $semester) {
                $nilai = $nilais[$semester]->firstWhere('mata_pelajaran_id', $id);
                $data[] = $nilai?->nilai_akhir ?? null;
            }
            $series[] = [
                'name' => $nama,
                'data' => $data,
            ];
        }

        return response()->json([
            'semesters' => $semesters,
            'series' => $series,
        ]);
    }
}
