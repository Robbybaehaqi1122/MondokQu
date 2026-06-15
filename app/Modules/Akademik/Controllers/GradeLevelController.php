<?php

namespace App\Modules\Akademik\Controllers;

use App\Models\GradeLevel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class GradeLevelController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();

        $tenantId = $currentUser->effectiveTenantId();

        if (! $tenantId) {
            return back()->withErrors(['name' => 'Tidak ada tenant yang tersedia.'])->withInput();
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        GradeLevel::query()->create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'order' => (int) ($validated['order'] ?? 0),
        ]);

        return redirect()->route('akademik.mata-pelajaran.index')
            ->with('success', 'Tingkat '.$validated['name'].' berhasil ditambahkan.');
    }

    public function toggle(GradeLevel $gradeLevel): RedirectResponse
    {
        $this->authorize('update', $gradeLevel);

        $gradeLevel->update([
            'is_active' => ! $gradeLevel->is_active,
        ]);

        $status = $gradeLevel->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('akademik.mata-pelajaran.index')
            ->with('success', 'Tingkat '.$gradeLevel->name.' berhasil '.$status.'.');
    }

    public function destroy(GradeLevel $gradeLevel): RedirectResponse
    {
        $this->authorize('delete', $gradeLevel);

        $gradeLevel->subjects()->detach();
        $gradeLevel->delete();

        return redirect()->route('akademik.mata-pelajaran.index')
            ->with('success', 'Tingkat berhasil dihapus.');
    }
}
