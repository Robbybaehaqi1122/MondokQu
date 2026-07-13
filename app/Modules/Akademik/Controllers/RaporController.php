<?php

namespace App\Modules\Akademik\Controllers;

use App\Models\MataPelajaran;
use App\Models\NilaiSantri;
use App\Models\Santri;
use App\Http\Controllers\Controller;
use App\Modules\Akademik\Controllers\Concerns\HasSemesterOptions;
use App\Services\RaporService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RaporController extends Controller
{
    use HasSemesterOptions;

    public function __construct(
        private readonly RaporService $raporService,
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();

        $santris = Santri::query()
            ->visibleTo($currentUser)
            ->select('id', 'full_name', 'nis')
            ->orderBy('full_name')
            ->get();

        $semesters = $this->availableSemesters();

        return view('modules.akademik.rapor.index', [
            'santris' => $santris,
            'semesters' => $semesters,
        ]);
    }

    public function show(Request $request): View
    {
        $this->authorize('viewAny', NilaiSantri::class);
        $currentUser = $request->user();

        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'semester' => ['required', 'string'],
        ]);

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->with(['room', 'guardians'])
            ->findOrFail($validated['santri_id']);

        $raporData = $this->raporService->getRaporData($santri, $validated['semester'], $currentUser);

        return view('modules.akademik.rapor.show', array_merge([
            'santri' => $santri,
            'semester' => $validated['semester'],
        ], $raporData));
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('viewAny', NilaiSantri::class);
        $currentUser = $request->user();

        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'semester' => ['required', 'string'],
        ]);

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->with(['room', 'guardians'])
            ->findOrFail($validated['santri_id']);

        $raporData = $this->raporService->getRaporData($santri, $validated['semester'], $currentUser);

        $pdf = Pdf::loadView('exports.pdf.rapor-santri', array_merge([
            'santri' => $santri,
            'semester' => $validated['semester'],
        ], $raporData));

        $filename = 'rapor-'.$santri->full_name.'-'.str_replace(['/', '\\'], '-', $validated['semester']).'.pdf';

        return $pdf->download($filename);
    }

    public function chartData(Request $request): JsonResponse
    {
        $this->authorize('viewAny', NilaiSantri::class);
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
}
