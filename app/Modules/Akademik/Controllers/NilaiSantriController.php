<?php

namespace App\Modules\Akademik\Controllers;

use App\Models\MataPelajaran;
use App\Models\NilaiSantri;
use App\Models\Santri;
use App\Http\Controllers\Controller;
use App\Modules\Akademik\Controllers\Concerns\HasSemesterOptions;
use App\Modules\Akademik\Requests\StoreNilaiSantriRequest;
use App\Modules\Akademik\Requests\UpdateNilaiSantriRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NilaiSantriController extends Controller
{
    use HasSemesterOptions;

    public function index(Request $request): View
    {
        $currentUser = $request->user();

        $query = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->with(['santri', 'mataPelajaran', 'inputBy']);

        if ($search = $request->input('q')) {
            $sanitized = '%' . addcslashes($search, '%_') . '%';
            $query->whereHas('santri', fn ($q) => $q->where('full_name', 'like', $sanitized));
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

        $santris = Santri::query()
            ->visibleTo($currentUser)
            ->active()
            ->with('room.gradeLevel')
            ->select('id', 'full_name', 'nis', 'room_id')
            ->orderBy('full_name')
            ->get();

        $mapels = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->active()
            ->with('gradeLevels')
            ->orderBy('nama')
            ->get();

        $semesters = $this->availableSemesters();

        return view('modules.akademik.nilai.create', [
            'mapels' => $mapels,
            'santris' => $santris,
            'semesters' => $semesters,
        ]);
    }

    public function store(StoreNilaiSantriRequest $request): RedirectResponse
    {
        $this->authorize('create', NilaiSantri::class);
        $currentUser = $request->user();
        $validated = $request->validated();

        $tenantId = $currentUser->effectiveTenantId();

        if (! $tenantId) {
            return back()->withErrors(['santri_id' => 'Tidak ada tenant yang tersedia. Hubungi administrator.'])->withInput();
        }

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
            'tenant_id' => $tenantId,
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
        $this->authorize('update', $nilaiSantri);
        $currentUser = $request->user();

        $mapels = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->active()
            ->with('gradeLevels')
            ->orderBy('nama')
            ->get();

        $santris = Santri::query()
            ->visibleTo($currentUser)
            ->active()
            ->with('room.gradeLevel')
            ->select('id', 'full_name', 'nis', 'room_id')
            ->orderBy('full_name')
            ->get();

        $semesters = $this->availableSemesters();

        return view('modules.akademik.nilai.edit', [
            'nilai' => $nilaiSantri->loadMissing(['santri', 'mataPelajaran']),
            'mapels' => $mapels,
            'santris' => $santris,
            'semesters' => $semesters,
        ]);
    }

    public function update(UpdateNilaiSantriRequest $request, NilaiSantri $nilaiSantri): RedirectResponse
    {
        $this->authorize('update', $nilaiSantri);
        $validated = $request->validated();

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
        $this->authorize('delete', $nilaiSantri);

        $nilaiSantri->delete();

        return redirect()->route('akademik.nilai.index')
            ->with('success', 'Nilai berhasil dihapus.');
    }
}
