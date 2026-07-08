<?php

namespace App\Modules\InventarisQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\InventarisQu\Models\Aset;
use App\Modules\InventarisQu\Models\KategoriAset;
use App\Modules\InventarisQu\Models\LokasiAset;
use App\Modules\InventarisQu\Requests\StoreAsetRequest;
use App\Modules\InventarisQu\Requests\UpdateAsetRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AsetController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $asets = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $kategoris = collect();
            $lokasis = collect();
            $kondisiList = Aset::KONDISI;
            return view('modules.inventaris-qu.aset.index', compact('asets', 'kategoris', 'lokasis', 'kondisiList'));
        }

        $asets = Aset::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with(['kategori', 'lokasi'])
            ->search($request->get('search'))
            ->kategori($request->get('kategori'))
            ->lokasi($request->get('lokasi'))
            ->kondisi($request->get('kondisi'))
            ->orderByDesc('id')
            ->paginate(20);

        $kategoris = KategoriAset::withoutTenantScope()
            ->where('tenant_id', $tenantId)->orderBy('name')->get();

        $lokasis = LokasiAset::withoutTenantScope()
            ->where('tenant_id', $tenantId)->orderBy('name')->get();

        $kondisiList = Aset::KONDISI;

        return view('modules.inventaris-qu.aset.index', compact('asets', 'kategoris', 'lokasis', 'kondisiList'));
    }

    public function create()
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            return redirect()->route('inventaris.dashboard')
                ->with('error', 'Anda tidak memiliki akses tanpa terhubung ke pesantren.');
        }

        $kategoris = KategoriAset::withoutTenantScope()
            ->where('tenant_id', $tenantId)->orderBy('name')->get();

        $lokasis = LokasiAset::withoutTenantScope()
            ->where('tenant_id', $tenantId)->orderBy('name')->get();

        $kondisiList = Aset::KONDISI;

        return view('modules.inventaris-qu.aset.create', compact('kategoris', 'lokasis', 'kondisiList'));
    }

    public function store(StoreAsetRequest $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        if (! $tenantId) {
            return back()->with('error', 'Anda tidak memiliki akses tanpa terhubung ke pesantren.');
        }

        $aset = Aset::withoutTenantScope()->create(
            $request->validated() + [
                'tenant_id' => $tenantId,
                'kode_aset' => Aset::generateKodeAset($tenantId),
            ]
        );

        activity()->log('Menambah aset: ' . $aset->kode_aset . ' - ' . $aset->name);

        return redirect()->route('inventaris.aset.show', $aset)
            ->with('success', 'Aset berhasil ditambahkan.');
    }

    public function show(Aset $aset)
    {
        abort_if($aset->tenant_id !== auth()->user()->tenant_id, 403);

        $aset->loadMissing(['kategori', 'lokasi', 'peminjaman' => function ($q) {
            $q->orderByDesc('created_at')->limit(10);
        }]);

        return view('modules.inventaris-qu.aset.show', compact('aset'));
    }

    public function edit(Aset $aset)
    {
        abort_if($aset->tenant_id !== auth()->user()->tenant_id, 403);

        $tenantId = $aset->tenant_id;
        $kategoris = KategoriAset::withoutTenantScope()
            ->where('tenant_id', $tenantId)->orderBy('name')->get();
        $lokasis = LokasiAset::withoutTenantScope()
            ->where('tenant_id', $tenantId)->orderBy('name')->get();
        $kondisiList = Aset::KONDISI;

        return view('modules.inventaris-qu.aset.edit', compact('aset', 'kategoris', 'lokasis', 'kondisiList'));
    }

    public function update(UpdateAsetRequest $request, Aset $aset)
    {
        abort_if($aset->tenant_id !== auth()->user()->tenant_id, 403);

        $aset->update($request->validated());

        activity()->log('Mengupdate aset: ' . $aset->kode_aset);

        return redirect()->route('inventaris.aset.show', $aset)
            ->with('success', 'Aset berhasil diperbarui.');
    }

    public function destroy(Aset $aset)
    {
        abort_if($aset->tenant_id !== auth()->user()->tenant_id, 403);

        $aset->peminjaman()->delete();
        $aset->delete();

        activity()->log('Menghapus aset: ' . $aset->kode_aset);

        return redirect()->route('inventaris.aset.index')
            ->with('success', 'Aset berhasil dihapus.');
    }

    public function generateQr(Aset $aset)
    {
        abort_if($aset->tenant_id !== auth()->user()->tenant_id, 403);

        $qrCode = base64_encode(
            \QrCode::format('svg')
                ->size(200)
                ->generate(route('inventaris.aset.show', $aset))
        );

        $aset->update(['qr_code' => $qrCode]);

        return back()->with('success', 'QR Code berhasil dibuat.');
    }
}
