<?php

namespace App\Modules\PerpustakaanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PerpustakaanQu\Models\PerpustakaanKategori;
use App\Modules\PerpustakaanQu\Models\PerpustakaanKitab;
use App\Modules\PerpustakaanQu\Requests\StoreKitabRequest;
use App\Modules\PerpustakaanQu\Requests\UpdateKitabRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitabController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $kitabs = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $kategoris = collect();
            return view('modules.perpustakaan-qu.kitab.index', compact('kitabs', 'kategoris'));
        }

        $kitabs = PerpustakaanKitab::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with('kategori')
            ->search($request->get('search'))
            ->kategori($request->get('kategori'))
            ->kondisi($request->get('kondisi'))
            ->orderByDesc('id')
            ->paginate(20);

        $kategoris = PerpustakaanKategori::withoutTenantScope()
            ->where('tenant_id', $tenantId)->orderBy('nama')->get();

        return view('modules.perpustakaan-qu.kitab.index', compact('kitabs', 'kategoris'));
    }

    public function create(): View
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return redirect()->route('perpustakaan.dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $kategoris = PerpustakaanKategori::withoutTenantScope()
            ->where('tenant_id', $tenantId)->orderBy('nama')->get();

        return view('modules.perpustakaan-qu.kitab.create', compact('kategoris'));
    }

    public function store(StoreKitabRequest $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return back()->with('error', 'Akses ditolak.');
        }

        $data = $request->validated();
        $data['tenant_id'] = $tenantId;
        $data['tersedia'] = $data['jumlah_eksemplar'];

        $kitab = PerpustakaanKitab::withoutTenantScope()->create($data);

        activity()->log('Menambah kitab: ' . $kitab->judul);

        return redirect()->route('perpustakaan.kitab.show', $kitab)
            ->with('success', 'Kitab berhasil ditambahkan.');
    }

    public function show(PerpustakaanKitab $perpustakaanKitab): View
    {
        if ($perpustakaanKitab->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $perpustakaanKitab->loadMissing(['kategori', 'peminjamans' => function ($q) {
            $q->with('santri')->orderByDesc('created_at')->limit(20);
        }]);

        return view('modules.perpustakaan-qu.kitab.show', [
            'kitab' => $perpustakaanKitab,
        ]);
    }

    public function edit(PerpustakaanKitab $perpustakaanKitab): View
    {
        if ($perpustakaanKitab->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $kategoris = PerpustakaanKategori::withoutTenantScope()
            ->where('tenant_id', $perpustakaanKitab->tenant_id)->orderBy('nama')->get();

        return view('modules.perpustakaan-qu.kitab.edit', [
            'kitab' => $perpustakaanKitab,
            'kategoris' => $kategoris,
        ]);
    }

    public function update(UpdateKitabRequest $request, PerpustakaanKitab $perpustakaanKitab): RedirectResponse
    {
        if ($perpustakaanKitab->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $perpustakaanKitab->update($request->validated());

        activity()->log('Mengupdate kitab: ' . $perpustakaanKitab->judul);

        return redirect()->route('perpustakaan.kitab.show', $perpustakaanKitab)
            ->with('success', 'Kitab berhasil diperbarui.');
    }

    public function destroy(PerpustakaanKitab $perpustakaanKitab): RedirectResponse
    {
        if ($perpustakaanKitab->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $perpustakaanKitab->peminjamans()->delete();
        $perpustakaanKitab->delete();

        activity()->log('Menghapus kitab: ' . $perpustakaanKitab->judul);

        return redirect()->route('perpustakaan.kitab.index')
            ->with('success', 'Kitab berhasil dihapus.');
    }
}
