<?php

namespace App\Modules\KitabQu\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Modules\KitabQu\Models\Kitab;
use App\Modules\KitabQu\Models\KitabSetoran;
use App\Modules\KitabQu\Requests\StoreSetoranRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SetoranController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $setorans = KitabSetoran::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with(['santri', 'kitab', 'approver'])
            ->santri($request->get('santri'))
            ->kitab($request->get('kitab'))
            ->status($request->get('status'))
            ->latest()
            ->paginate(20);

        $kitabs = Kitab::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->orderBy('nama')
            ->get();

        $santris = Santri::query()
            ->visibleTo(auth()->user())
            ->orderBy('full_name')
            ->limit(500)
            ->get();

        return view('modules.kitab-qu.setoran.index', compact('setorans', 'kitabs', 'santris'));
    }

    public function create(): View|RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return redirect()->route('kitab.dashboard')->with('error', 'Akses ditolak.');
        }

        $kitabs = Kitab::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->orderBy('nama')
            ->get();

        $santris = Santri::query()
            ->visibleTo(auth()->user())
            ->orderBy('full_name')
            ->limit(500)
            ->get();

        return view('modules.kitab-qu.setoran.create', compact('kitabs', 'santris'));
    }

    public function store(StoreSetoranRequest $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return back()->with('error', 'Akses ditolak.');
        }

        KitabSetoran::withoutTenantScope()->create([
            'tenant_id' => $tenantId,
            ...$request->validated(),
        ]);

        return redirect()->route('kitab.setoran.index')
            ->with('success', 'Setoran hafalan berhasil dicatat.');
    }

    public function approve(Request $request, KitabSetoran $kitabSetoran): RedirectResponse
    {
        if ($kitabSetoran->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $kitabSetoran->update([
            'status' => KitabSetoran::STATUS_DISETUJUI,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Setoran hafalan disetujui.');
    }

    public function reject(Request $request, KitabSetoran $kitabSetoran): RedirectResponse
    {
        if ($kitabSetoran->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);

        $kitabSetoran->update([
            'status' => KitabSetoran::STATUS_DITOLAK,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'catatan' => $data['catatan'] ?? $kitabSetoran->catatan,
        ]);

        return back()->with('success', 'Setoran hafalan ditolak.');
    }

    public function rapor(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $query = KitabSetoran::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with(['santri', 'kitab']);

        if ($request->filled('santri')) {
            $query->where('santri_id', $request->get('santri'));
        }
        if ($request->filled('kitab')) {
            $query->where('kitab_id', $request->get('kitab'));
        }

        $setorans = $query->latest('tanggal_setoran')->paginate(20);

        $santris = Santri::query()
            ->visibleTo(auth()->user())
            ->orderBy('full_name')
            ->limit(500)
            ->get();

        $kitabs = Kitab::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->orderBy('nama')
            ->get();

        $rekap = KitabSetoran::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->selectRaw("santri_id, kitab_id, COUNT(*) as total_setoran, SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as disetujui")
            ->groupBy('santri_id', 'kitab_id')
            ->with(['santri', 'kitab'])
            ->get();

        return view('modules.kitab-qu.setoran.rapor', compact('setorans', 'santris', 'kitabs', 'rekap'));
    }
}
