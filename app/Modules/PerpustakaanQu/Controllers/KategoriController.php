<?php

namespace App\Modules\PerpustakaanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PerpustakaanQu\Models\PerpustakaanKategori;
use App\Modules\PerpustakaanQu\Requests\StoreKategoriRequest;
use App\Modules\PerpustakaanQu\Requests\UpdateKategoriRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $kategoris = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            return view('modules.perpustakaan-qu.kategori.index', compact('kategoris'));
        }

        $kategoris = PerpustakaanKategori::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->withCount('kitabs')
            ->orderBy('nama')
            ->paginate(20);

        return view('modules.perpustakaan-qu.kategori.index', compact('kategoris'));
    }

    public function store(StoreKategoriRequest $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return back()->with('error', 'Akses ditolak.');
        }

        PerpustakaanKategori::withoutTenantScope()->create(
            $request->validated() + ['tenant_id' => $tenantId]
        );

        activity()->log('Menambah kategori perpustakaan: ' . $request->nama);

        return redirect()->route('perpustakaan.kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(UpdateKategoriRequest $request, PerpustakaanKategori $perpustakaanKategori): RedirectResponse
    {
        if ($perpustakaanKategori->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $perpustakaanKategori->update($request->validated());

        activity()->log('Mengupdate kategori perpustakaan: ' . $perpustakaanKategori->nama);

        return redirect()->route('perpustakaan.kategori.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(PerpustakaanKategori $perpustakaanKategori): RedirectResponse
    {
        if ($perpustakaanKategori->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($perpustakaanKategori->kitabs()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus kategori yang masih memiliki kitab.');
        }

        $perpustakaanKategori->delete();

        activity()->log('Menghapus kategori perpustakaan: ' . $perpustakaanKategori->nama);

        return redirect()->route('perpustakaan.kategori.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
