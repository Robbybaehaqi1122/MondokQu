<?php

namespace App\Modules\Akademik\Controllers;

use App\Models\AttitudeGrade;
use App\Models\Santri;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class AttitudeGradeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $currentUser = $request->user();

        $santris = Santri::query()
            ->visibleTo($currentUser)
            ->select('id', 'full_name', 'nis')
            ->orderBy('full_name')
            ->get();

        $semesters = AttitudeGrade::query()
            ->visibleTo($currentUser)
            ->distinct()
            ->orderBy('semester', 'desc')
            ->pluck('semester');

        return view('modules.akademik.attitude.index', [
            'santris' => $santris,
            'semesters' => $semesters,
        ]);
    }

    public function create(Request $request): View
    {
        $currentUser = $request->user();

        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'semester' => ['required', 'string'],
        ]);

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->findOrFail($validated['santri_id']);

        $existing = AttitudeGrade::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->where('semester', $validated['semester'])
            ->get()
            ->keyBy(fn ($g) => $g->aspect.'::'.$g->aspect_name);

        $allAspects = AttitudeGrade::allAspects();

        return view('modules.akademik.attitude.create', [
            'santri' => $santri,
            'semester' => $validated['semester'],
            'allAspects' => $allAspects,
            'existing' => $existing,
            'predicates' => AttitudeGrade::availablePredicates(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();

        $tenantId = $currentUser->effectiveTenantId();

        if (! $tenantId) {
            return back()->withErrors(['santri_id' => 'Tidak ada tenant yang tersedia.']);
        }

        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'semester' => ['required', 'string', 'max:50'],
            'grades' => ['required', 'array'],
            'grades.*.aspect' => ['required', 'in:spiritual,sosial'],
            'grades.*.aspect_name' => ['required', 'string', 'max:255'],
            'grades.*.predicate' => ['nullable', 'in:SB,B,C,K'],
            'grades.*.description' => ['nullable', 'string', 'max:1000'],
        ]);

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->findOrFail($validated['santri_id']);

        $saved = 0;
        foreach ($validated['grades'] as $grade) {
            AttitudeGrade::query()->updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'santri_id' => $santri->id,
                    'semester' => $validated['semester'],
                    'aspect' => $grade['aspect'],
                    'aspect_name' => $grade['aspect_name'],
                ],
                [
                    'predicate' => $grade['predicate'],
                    'description' => $grade['description'],
                    'created_by' => $currentUser->id,
                ]
            );
            $saved++;
        }

        return redirect()->route('akademik.attitude.index')
            ->with('success', "Nilai sikap untuk {$saved} aspek berhasil disimpan.");
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
            ->findOrFail($validated['santri_id']);

        $grades = AttitudeGrade::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->where('semester', $validated['semester'])
            ->get()
            ->groupBy('aspect');

        return view('modules.akademik.attitude.show', [
            'santri' => $santri,
            'semester' => $validated['semester'],
            'grades' => $grades,
        ]);
    }
}
