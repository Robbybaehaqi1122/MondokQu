<?php

namespace App\Modules\Akademik\Controllers;

use App\Models\AttitudeGrade;
use App\Models\MataPelajaran;
use App\Models\NilaiSantri;
use App\Models\NilaiSikap;
use App\Models\Pelanggaran;
use App\Models\Santri;
use App\Models\TahfidzRecord;
use App\Models\TahfidzSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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

        $nilaiSikap = NilaiSikap::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->where('semester', $validated['semester'])
            ->first();

        $attitudeGrades = AttitudeGrade::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->where('semester', $validated['semester'])
            ->get()
            ->groupBy('aspect');

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

        $peringkatKelas = $this->hitungPeringkatKelas($validated['semester'], $santri->id, $currentUser);

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
            'nilaiSikap' => $nilaiSikap,
            'attitudeGrades' => $attitudeGrades,
            'tahfidzStats' => $tahfidzStats,
            'totalPoinPelanggaran' => $totalPoinPelanggaran,
            'rataRataKelas' => $rataRataKelas,
            'peringkatKelas' => $peringkatKelas,
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

        $nilaiSikap = NilaiSikap::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->where('semester', $validated['semester'])
            ->first();

        $attitudeGrades = AttitudeGrade::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->where('semester', $validated['semester'])
            ->get()
            ->groupBy('aspect');

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

        $peringkatKelas = $this->hitungPeringkatKelas($validated['semester'], $santri->id, $currentUser);

        $pdf = Pdf::loadView('exports.pdf.rapor-santri', [
            'santri' => $santri,
            'semester' => $validated['semester'],
            'nilais' => $nilais,
            'nilaiSikap' => $nilaiSikap,
            'attitudeGrades' => $attitudeGrades,
            'tahfidzStats' => $tahfidzStats,
            'totalPoinPelanggaran' => $totalPoinPelanggaran,
            'rataRataKelas' => $rataRataKelas,
            'peringkatKelas' => $peringkatKelas,
        ]);

        $filename = 'rapor-'.$santri->full_name.'-'.str_replace(['/', '\\'], '-', $validated['semester']).'.pdf';

        return $pdf->download($filename);
    }

    public function chartData(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'mata_pelajaran_id' => ['nullable', 'array'],
            'mata_pelajaran_id.*' => ['exists:mata_pelajarans,id'],
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
            ->when(! empty($validated['mata_pelajaran_id']), fn ($q) => $q->whereIn('id', $validated['mata_pelajaran_id']))
            ->orderBy('nama')
            ->pluck('nama', 'id');

        $classAverages = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->whereIn('mata_pelajaran_id', $mapels->keys())
            ->selectRaw('mata_pelajaran_id, semester, COALESCE(ROUND(AVG((nilai_pengetahuan + nilai_keterampilan) / 2)), 0) as avg')
            ->groupBy('mata_pelajaran_id', 'semester')
            ->get()
            ->groupBy('mata_pelajaran_id');

        $series = [];
        foreach ($mapels as $id => $nama) {
            $data = [];
            $rataKelas = [];
            $classAvgMap = $classAverages->get($id, collect())->keyBy('semester');
            foreach ($semesters as $semester) {
                $nilai = $nilais[$semester]->firstWhere('mata_pelajaran_id', $id);
                $data[] = $nilai?->nilai_akhir ?? null;
                $rataKelas[] = (int) ($classAvgMap->get($semester)?->avg ?? 0);
            }
            $series[] = [
                'name' => $nama,
                'data' => $data,
                'rata_rata_kelas' => $rataKelas,
            ];
        }

        return response()->json([
            'semesters' => $semesters,
            'series' => $series,
        ]);
    }

    private function hitungPeringkatKelas(string $semester, int $santriId, $user): array
    {
        $rataRataSantris = NilaiSantri::query()
            ->visibleTo($user)
            ->where('semester', $semester)
            ->selectRaw('santri_id, COALESCE(ROUND(AVG((nilai_pengetahuan + nilai_keterampilan) / 2)), 0) as rata_rata')
            ->groupBy('santri_id')
            ->orderByDesc('rata_rata')
            ->get()
            ->values();

        $totalSantri = $rataRataSantris->count();
        $peringkat = null;
        $rataSaya = null;

        foreach ($rataRataSantris as $i => $item) {
            if ((int) $item->santri_id === $santriId) {
                $peringkat = $i + 1;
                $rataSaya = (int) $item->rata_rata;
                break;
            }
        }

        return [
            'peringkat' => $peringkat,
            'total' => $totalSantri,
            'rata_rata' => $rataSaya,
        ];
    }
}
