<?php

namespace App\Modules\Akademik\Controllers;

use App\Models\MataPelajaran;
use App\Models\NilaiSantri;
use App\Models\Santri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NilaiSantriController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = $request->user();

        $query = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->with(['santri', 'mataPelajaran', 'inputBy']);

        if ($search = $request->input('q')) {
            $query->whereHas('santri', fn ($q) => $q->where('full_name', 'like', "%{$search}%"));
        }
        if ($mapelId = $request->input('mata_pelajaran_id')) {
            $query->where('mata_pelajaran_id', $mapelId);
        }
        if ($semester = $request->input('semester')) {
            $query->where('semester', $semester);
        }
        if ($santriId = $request->input('santri_id')) {
            $query->where('santri_id', $santriId);
        }

        $nilais = $query->orderByDesc('created_at')->paginate(20);

        $mapels = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->active()
            ->orderBy('nama')
            ->get();

        $semesters = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->distinct()
            ->orderBy('semester', 'desc')
            ->pluck('semester');

        $santris = Santri::query()
            ->visibleTo($currentUser)
            ->select('id', 'full_name', 'nis')
            ->orderBy('full_name')
            ->get();

        return view('modules.akademik.nilai.index', [
            'nilais' => $nilais,
            'mapels' => $mapels,
            'semesters' => $semesters,
            'santris' => $santris,
        ]);
    }

    public function create(Request $request): View
    {
        $currentUser = $request->user();

        $mapels = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->active()
            ->orderBy('nama')
            ->get();

        $santris = Santri::query()
            ->visibleTo($currentUser)
            ->active()
            ->select('id', 'full_name', 'nis')
            ->orderBy('full_name')
            ->get();

        $semesters = $this->availableSemesters();

        return view('modules.akademik.nilai.create', [
            'mapels' => $mapels,
            'santris' => $santris,
            'semesters' => $semesters,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        if (! $currentUser?->tenant_id) abort(403);

        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajarans,id'],
            'semester' => ['required', 'string', 'max:50'],
            'nilai_pengetahuan' => ['required', 'integer', 'min:0', 'max:100'],
            'nilai_keterampilan' => ['required', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $exists = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $validated['santri_id'])
            ->where('mata_pelajaran_id', $validated['mata_pelajaran_id'])
            ->where('semester', $validated['semester'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['santri_id' => 'Nilai untuk santri, mata pelajaran, dan semester ini sudah ada.'])->withInput();
        }

        NilaiSantri::query()->create([
            'tenant_id' => $currentUser->tenant_id,
            'santri_id' => $validated['santri_id'],
            'mata_pelajaran_id' => $validated['mata_pelajaran_id'],
            'semester' => $validated['semester'],
            'nilai_pengetahuan' => $validated['nilai_pengetahuan'],
            'nilai_keterampilan' => $validated['nilai_keterampilan'],
            'notes' => $validated['notes'],
            'input_by' => $currentUser->id,
        ]);

        return redirect()->route('akademik.nilai.index')
            ->with('success', 'Nilai berhasil dicatat.');
    }

    public function edit(Request $request, NilaiSantri $nilaiSantri): View
    {
        $currentUser = $request->user();
        if (! $currentUser?->tenant_id) abort(403);
        if (! $currentUser->isSuperAdmin() && $nilaiSantri->tenant_id !== $currentUser->tenant_id) abort(403);

        $mapels = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->active()
            ->orderBy('nama')
            ->get();

        $santris = Santri::query()
            ->visibleTo($currentUser)
            ->active()
            ->select('id', 'full_name', 'nis')
            ->orderBy('full_name')
            ->get();

        $semesters = $this->availableSemesters();

        return view('modules.akademik.nilai.edit', [
            'nilai' => $nilaiSantri->load(['santri', 'mataPelajaran']),
            'mapels' => $mapels,
            'santris' => $santris,
            'semesters' => $semesters,
        ]);
    }

    public function update(Request $request, NilaiSantri $nilaiSantri): RedirectResponse
    {
        $currentUser = $request->user();
        if (! $currentUser?->tenant_id) abort(403);
        if (! $currentUser->isSuperAdmin() && $nilaiSantri->tenant_id !== $currentUser->tenant_id) abort(403);

        $validated = $request->validate([
            'nilai_pengetahuan' => ['required', 'integer', 'min:0', 'max:100'],
            'nilai_keterampilan' => ['required', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $nilaiSantri->update([
            'nilai_pengetahuan' => $validated['nilai_pengetahuan'],
            'nilai_keterampilan' => $validated['nilai_keterampilan'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('akademik.nilai.index')
            ->with('success', 'Nilai berhasil diperbarui.');
    }

    public function destroy(Request $request, NilaiSantri $nilaiSantri): RedirectResponse
    {
        $currentUser = $request->user();
        if (! $currentUser?->tenant_id) abort(403);
        if (! $currentUser->isSuperAdmin() && $nilaiSantri->tenant_id !== $currentUser->tenant_id) abort(403);

        $nilaiSantri->delete();

        return redirect()->route('akademik.nilai.index')
            ->with('success', 'Nilai berhasil dihapus.');
    }

    protected function availableSemesters(): array
    {
        $year = now()->year;
        $nextYear = $year + 1;

        return [
            "{$year}/{$nextYear} Ganjil",
            "{$year}/{$nextYear} Genap",
            (($year - 1) . "/{$year} Ganjil"),
            (($year - 1) . "/{$year} Genap"),
        ];
    }
}
