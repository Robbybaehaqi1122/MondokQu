<?php

namespace App\Modules\KitabQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KitabQu\Models\KitabKategori;
use App\Modules\KitabQu\Requests\StoreKategoriRequest;
use App\Modules\KitabQu\Requests\UpdateKategoriRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $kategoris = KitabKategori::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->withCount('kitabs')
            ->orderBy('nama')
            ->paginate(20);

        return view('modules.kitab-qu.kategori.index', compact('kategoris'));
    }

    public function store(StoreKategoriRequest $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return back()->with('error', 'Akses ditolak.');
        }

        KitabKategori::withoutTenantScope()->create([
            'tenant_id' => $tenantId,
            ...$request->validated(),
        ]);

        return redirect()->route('kitab.kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(UpdateKategoriRequest $request, KitabKategori $kitabKategori): RedirectResponse
    {
        if ($kitabKategori->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $kitabKategori->update($request->validated());

        return redirect()->route('kitab.kategori.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(KitabKategori $kitabKategori): RedirectResponse
    {
        if ($kitabKategori->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($kitabKategori->kitabs()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih memiliki kitab.');
        }

        $kitabKategori->delete();

        return redirect()->route('kitab.kategori.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
