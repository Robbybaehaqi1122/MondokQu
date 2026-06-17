<?php

namespace App\Modules\InventarisQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\InventarisQu\Models\LokasiAset;
use App\Modules\InventarisQu\Requests\StoreLokasiAsetRequest;
use App\Modules\InventarisQu\Requests\UpdateLokasiAsetRequest;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $lokasis = collect();
            return view('modules.inventaris-qu.lokasi.index', compact('lokasis'));
        }

        $lokasis = LokasiAset::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->withCount('asets')
            ->orderBy('building')
            ->orderBy('name')
            ->get();

        return view('modules.inventaris-qu.lokasi.index', compact('lokasis'));
    }

    public function store(StoreLokasiAsetRequest $request)
    {
        if (! auth()->user()->tenant_id) {
            return back()->with('error', 'Anda tidak memiliki akses tanpa terhubung ke pesantren.');
        }

        LokasiAset::withoutTenantScope()->create(
            $request->validated() + ['tenant_id' => auth()->user()->tenant_id]
        );

        return redirect()->route('inventaris.lokasi.index')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function update(UpdateLokasiAsetRequest $request, LokasiAset $lokasiAset)
    {
        abort_if($lokasiAset->tenant_id !== auth()->user()->tenant_id, 403);
        $lokasiAset->update($request->validated());

        return redirect()->route('inventaris.lokasi.index')
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(LokasiAset $lokasiAset)
    {
        abort_if($lokasiAset->tenant_id !== auth()->user()->tenant_id, 403);

        if ($lokasiAset->asets()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus lokasi yang masih memiliki aset.');
        }

        $lokasiAset->delete();

        return redirect()->route('inventaris.lokasi.index')
            ->with('success', 'Lokasi berhasil dihapus.');
    }
}
