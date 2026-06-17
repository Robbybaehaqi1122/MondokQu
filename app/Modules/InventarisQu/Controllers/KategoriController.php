<?php

namespace App\Modules\InventarisQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\InventarisQu\Models\KategoriAset;
use App\Modules\InventarisQu\Requests\StoreKategoriAsetRequest;
use App\Modules\InventarisQu\Requests\UpdateKategoriAsetRequest;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $kategoris = collect();
            return view('modules.inventaris-qu.kategori.index', compact('kategoris'));
        }

        $kategoris = KategoriAset::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->withCount('asets')
            ->orderBy('name')
            ->get();

        return view('modules.inventaris-qu.kategori.index', compact('kategoris'));
    }

    public function store(StoreKategoriAsetRequest $request)
    {
        if (! auth()->user()->tenant_id) {
            return back()->with('error', 'Anda tidak memiliki akses tanpa terhubung ke pesantren.');
        }

        KategoriAset::withoutTenantScope()->create(
            $request->validated() + ['tenant_id' => auth()->user()->tenant_id]
        );

        return redirect()->route('inventaris.kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(UpdateKategoriAsetRequest $request, KategoriAset $kategoriAset)
    {
        abort_if($kategoriAset->tenant_id !== auth()->user()->tenant_id, 403);
        $kategoriAset->update($request->validated());

        return redirect()->route('inventaris.kategori.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(KategoriAset $kategoriAset)
    {
        abort_if($kategoriAset->tenant_id !== auth()->user()->tenant_id, 403);

        if ($kategoriAset->asets()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus kategori yang masih memiliki aset.');
        }

        $kategoriAset->delete();

        return redirect()->route('inventaris.kategori.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
