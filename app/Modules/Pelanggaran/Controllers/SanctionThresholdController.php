<?php

namespace App\Modules\Pelanggaran\Controllers;

use App\Models\SanctionThreshold;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class SanctionThresholdController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $currentUser = $request->user();

        $thresholds = SanctionThreshold::query()
            ->visibleTo($currentUser)
            ->orderBy('min_points')
            ->get();

        return view('modules.pelanggaran.sanction-thresholds.index', [
            'thresholds' => $thresholds,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        $tenantId = $currentUser->effectiveTenantId();

        if (! $tenantId) {
            return back()->withErrors(['tenant' => 'Tidak ada tenant yang tersedia.']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sanction_type' => ['required', 'in:'.implode(',', array_keys(SanctionThreshold::sanctionTypes()))],
            'min_points' => ['required', 'integer', 'min:0'],
            'max_points' => ['nullable', 'integer', 'min:0', 'gte:min_points'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        SanctionThreshold::query()->create(array_merge($validated, [
            'tenant_id' => $tenantId,
        ]));

        return redirect()->route('pelanggaran.sanction-thresholds.index')
            ->with('success', 'Tingkat sanksi berhasil ditambahkan.');
    }

    public function update(Request $request, SanctionThreshold $sanctionThreshold): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sanction_type' => ['required', 'in:'.implode(',', array_keys(SanctionThreshold::sanctionTypes()))],
            'min_points' => ['required', 'integer', 'min:0'],
            'max_points' => ['nullable', 'integer', 'min:0', 'gte:min_points'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $sanctionThreshold->update($validated);

        return redirect()->route('pelanggaran.sanction-thresholds.index')
            ->with('success', 'Tingkat sanksi berhasil diperbarui.');
    }

    public function destroy(Request $request, SanctionThreshold $sanctionThreshold): RedirectResponse
    {
        $sanctionThreshold->delete();

        return redirect()->route('pelanggaran.sanction-thresholds.index')
            ->with('success', 'Tingkat sanksi berhasil dihapus.');
    }
}
