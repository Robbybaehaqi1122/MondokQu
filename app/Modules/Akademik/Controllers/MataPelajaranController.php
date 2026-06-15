<?php

namespace App\Modules\Akademik\Controllers;

use App\Models\GradeLevel;
use App\Models\MataPelajaran;
use App\Models\SubjectGradeLevel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class MataPelajaranController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $currentUser = $request->user();

        $gradeLevelId = $request->input('grade_level_id');

        $mapels = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->withCount('nilaiSantris')
            ->when($gradeLevelId, fn ($q) => $q->forGradeLevel($gradeLevelId))
            ->orderBy('is_active', 'desc')
            ->orderBy('nama')
            ->paginate(20);

        $gradeLevels = GradeLevel::query()
            ->visibleTo($currentUser)
            ->ordered()
            ->get();

        $selectedGrade = $gradeLevelId ? GradeLevel::find($gradeLevelId) : null;

        return view('modules.akademik.mata-pelajaran.index', [
            'mapels' => $mapels,
            'gradeLevels' => $gradeLevels,
            'selectedGrade' => $selectedGrade,
            'selectedGradeId' => $gradeLevelId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MataPelajaran::class);
        $currentUser = $request->user();

        $tenantId = $currentUser->effectiveTenantId();

        if (! $tenantId) {
            return back()->withErrors(['nama' => 'Tidak ada tenant yang tersedia. Hubungi administrator.'])->withInput();
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'kkm' => ['required', 'integer', 'min:0', 'max:100'],
            'grade_level_ids' => ['nullable', 'array'],
            'grade_level_ids.*' => ['exists:grade_levels,id'],
        ]);

        $exists = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->where('nama', $validated['nama'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['nama' => 'Mata pelajaran dengan nama tersebut sudah ada.'])->withInput();
        }

        $mapel = MataPelajaran::query()->create([
            'tenant_id' => $tenantId,
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'],
            'kkm' => $validated['kkm'],
        ]);

        if (! empty($validated['grade_level_ids'])) {
            $pivotData = [];
            foreach ($validated['grade_level_ids'] as $glId) {
                $pivotData[$glId] = ['tenant_id' => $tenantId, 'kkm' => $validated['kkm']];
            }
            $mapel->gradeLevels()->sync($pivotData);
        }

        return redirect()->route('akademik.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, MataPelajaran $mataPelajaran): RedirectResponse
    {
        $this->authorize('update', $mataPelajaran);
        $currentUser = $request->user();

        $tenantId = $currentUser->effectiveTenantId();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'kkm' => ['required', 'integer', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
            'grade_level_ids' => ['nullable', 'array'],
            'grade_level_ids.*' => ['exists:grade_levels,id'],
        ]);

        $exists = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->where('nama', $validated['nama'])
            ->where('id', '!=', $mataPelajaran->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['nama' => 'Mata pelajaran dengan nama tersebut sudah ada.'])->withInput();
        }

        $mataPelajaran->update($validated);

        if (array_key_exists('grade_level_ids', $validated)) {
            $pivotData = [];
            if (! empty($validated['grade_level_ids'])) {
                foreach ($validated['grade_level_ids'] as $glId) {
                    $pivotData[$glId] = ['tenant_id' => $tenantId, 'kkm' => $mataPelajaran->kkm];
                }
            }
            $mataPelajaran->gradeLevels()->sync($pivotData);
        }

        return redirect()->route('akademik.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Request $request, MataPelajaran $mataPelajaran): RedirectResponse
    {
        $this->authorize('delete', $mataPelajaran);

        if ($mataPelajaran->nilaiSantris()->exists()) {
            return back()->withErrors(['delete' => 'Tidak dapat menghapus mata pelajaran yang sudah memiliki nilai.']);
        }

        $mataPelajaran->gradeLevels()->detach();
        $mataPelajaran->delete();

        return redirect()->route('akademik.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    public function clone(Request $request): RedirectResponse
    {
        $this->authorize('create', MataPelajaran::class);
        $currentUser = $request->user();

        $tenantId = $currentUser->effectiveTenantId();

        if (! $tenantId) {
            return back()->withErrors(['grade_level_id' => 'Tidak ada tenant yang tersedia.'])->withInput();
        }

        $validated = $request->validate([
            'from_grade_level_id' => ['required', 'exists:grade_levels,id'],
            'to_grade_level_id' => ['required', 'exists:grade_levels,id', 'different:from_grade_level_id'],
        ]);

        SubjectGradeLevel::query()
            ->where('grade_level_id', $validated['from_grade_level_id'])
            ->where('tenant_id', $tenantId)
            ->each(function (SubjectGradeLevel $pivot) use ($validated, $tenantId) {
                $exists = SubjectGradeLevel::query()
                    ->where('grade_level_id', $validated['to_grade_level_id'])
                    ->where('mata_pelajaran_id', $pivot->mata_pelajaran_id)
                    ->where('tenant_id', $tenantId)
                    ->exists();

                if (! $exists) {
                    SubjectGradeLevel::query()->create([
                        'tenant_id' => $tenantId,
                        'grade_level_id' => $validated['to_grade_level_id'],
                        'mata_pelajaran_id' => $pivot->mata_pelajaran_id,
                        'kkm' => $pivot->kkm,
                        'order' => $pivot->order,
                        'is_active' => $pivot->is_active,
                    ]);
                }
            });

        return redirect()->route('akademik.mata-pelajaran.index', ['grade_level_id' => $validated['to_grade_level_id']])
            ->with('success', 'Template mata pelajaran berhasil disalin.');
    }
}
