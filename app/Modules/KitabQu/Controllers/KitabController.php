<?php

namespace App\Modules\KitabQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KitabQu\Models\Kitab;
use App\Modules\KitabQu\Models\KitabKategori;
use App\Modules\KitabQu\Requests\StoreKitabRequest;
use App\Modules\KitabQu\Requests\UpdateKitabRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitabController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $kitabs = Kitab::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with('kategori')
            ->withCount('setorans')
            ->search($request->get('search'))
            ->kategori($request->get('kategori'))
            ->orderByDesc('id')
            ->paginate(20);

        $kategoris = KitabKategori::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->orderBy('nama')
            ->get();

        return view('modules.kitab-qu.kitab.index', compact('kitabs', 'kategoris'));
    }

    public function create(): View|RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return redirect()->route('kitab.dashboard')->with('error', 'Akses ditolak.');
        }

        $kategoris = KitabKategori::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->orderBy('nama')
            ->get();

        return view('modules.kitab-qu.kitab.create', compact('kategoris'));
    }

    public function store(StoreKitabRequest $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return back()->with('error', 'Akses ditolak.');
        }

        Kitab::withoutTenantScope()->create([
            'tenant_id' => $tenantId,
            ...$request->validated(),
        ]);

        return redirect()->route('kitab.kitab.index')
            ->with('success', 'Kitab berhasil ditambahkan.');
    }

    public function show(Kitab $kitab): View
    {
        if ($kitab->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $kitab->load(['kategori', 'setorans' => function ($q) {
            $q->with('santri')->latest()->limit(20);
        }]);

        return view('modules.kitab-qu.kitab.show', compact('kitab'));
    }

    public function edit(Kitab $kitab): View|RedirectResponse
    {
        if ($kitab->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $kategoris = KitabKategori::withoutTenantScope()
            ->where('tenant_id', $kitab->tenant_id)
            ->orderBy('nama')
            ->get();

        return view('modules.kitab-qu.kitab.edit', compact('kitab', 'kategoris'));
    }

    public function update(UpdateKitabRequest $request, Kitab $kitab): RedirectResponse
    {
        if ($kitab->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $kitab->update($request->validated());

        return redirect()->route('kitab.kitab.show', $kitab)
            ->with('success', 'Kitab berhasil diperbarui.');
    }

    public function destroy(Kitab $kitab): RedirectResponse
    {
        if ($kitab->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $kitab->setorans()->delete();
        $kitab->delete();

        return redirect()->route('kitab.kitab.index')
            ->with('success', 'Kitab berhasil dihapus.');
    }
}
