<?php

namespace App\Modules\InventarisQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\InventarisQu\Models\Aset;
use App\Modules\InventarisQu\Models\PeminjamanAset;
use App\Modules\InventarisQu\Requests\StorePeminjamanRequest;
use Illuminate\Http\Request;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $peminjaman = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            return view('modules.inventaris-qu.peminjaman.index', compact('peminjaman'));
        }

        $peminjaman = PeminjamanAset::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with('aset')
            ->search($request->get('search'))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('modules.inventaris-qu.peminjaman.index', compact('peminjaman'));
    }

    public function create()
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            return redirect()->route('inventaris.dashboard')
                ->with('error', 'Anda tidak memiliki akses tanpa terhubung ke pesantren.');
        }

        $asets = Aset::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('kondisi', 'baik')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'kode_aset', 'name']);

        return view('modules.inventaris-qu.peminjaman.create', compact('asets'));
    }

    public function store(StorePeminjamanRequest $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        if (! $tenantId) {
            return back()->with('error', 'Anda tidak memiliki akses tanpa terhubung ke pesantren.');
        }

        PeminjamanAset::withoutTenantScope()->create(
            $request->validated() + ['tenant_id' => $tenantId]
        );

        activity()->log('Peminjaman aset #' . $request->aset_id);

        return redirect()->route('inventaris.peminjaman.index')
            ->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function kembalikan(PeminjamanAset $peminjamanAset)
    {
        abort_if($peminjamanAset->tenant_id !== auth()->user()->tenant_id, 403);

        if ($peminjamanAset->status === 'dikembalikan') {
            return back()->with('error', 'Aset ini sudah dikembalikan sebelumnya.');
        }

        $peminjamanAset->update([
            'status' => 'dikembalikan',
            'tanggal_kembali' => now(),
        ]);

        activity()->log('Pengembalian aset #' . $peminjamanAset->aset_id);

        return back()->with('success', 'Aset berhasil dikembalikan.');
    }

    public function destroy(PeminjamanAset $peminjamanAset)
    {
        abort_if($peminjamanAset->tenant_id !== auth()->user()->tenant_id, 403);
        $peminjamanAset->delete();

        return redirect()->route('inventaris.peminjaman.index')
            ->with('success', 'Riwayat peminjaman berhasil dihapus.');
    }
}
