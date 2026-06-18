<?php

namespace App\Modules\PerpustakaanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Modules\PerpustakaanQu\Models\PerpustakaanKitab;
use App\Modules\PerpustakaanQu\Models\PerpustakaanPeminjaman;
use App\Modules\PerpustakaanQu\Requests\StorePeminjamanRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $peminjamans = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            return view('modules.perpustakaan-qu.peminjaman.index', compact('peminjamans'));
        }

        $peminjamans = PerpustakaanPeminjaman::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with(['kitab', 'santri'])
            ->status($request->get('status'))
            ->santri($request->get('santri'))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('modules.perpustakaan-qu.peminjaman.index', compact('peminjamans'));
    }

    public function create(): View
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return redirect()->route('perpustakaan.dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $kitabs = PerpustakaanKitab::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('tersedia', '>', 0)
            ->orderBy('judul')
            ->get();

        $santris = Santri::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->orderBy('full_name')
            ->get();

        return view('modules.perpustakaan-qu.peminjaman.create', compact('kitabs', 'santris'));
    }

    public function store(StorePeminjamanRequest $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return back()->with('error', 'Akses ditolak.');
        }

        $kitab = PerpustakaanKitab::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->findOrFail($request->kitab_id);

        if ($kitab->tersedia < 1) {
            return back()->with('error', 'Stok kitab tidak tersedia.');
        }

        $data = $request->validated();
        $data['tenant_id'] = $tenantId;
        $data['status'] = 'dipinjam';

        PerpustakaanPeminjaman::withoutTenantScope()->create($data);

        $kitab->decrement('tersedia');

        activity()->log('Peminjaman kitab: ' . $kitab->judul);

        return redirect()->route('perpustakaan.peminjaman.index')
            ->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function kembalikan(PerpustakaanPeminjaman $perpustakaanPeminjaman): RedirectResponse
    {
        if ($perpustakaanPeminjaman->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($perpustakaanPeminjaman->status === 'dikembalikan') {
            return back()->with('error', 'Kitab sudah dikembalikan.');
        }

        $tanggalKembali = now()->startOfDay();
        $denda = 0;

        if ($tanggalKembali->gt($perpustakaanPeminjaman->tanggal_jatuh_tempo)) {
            $hariTerlambat = $tanggalKembali->diffInDays($perpustakaanPeminjaman->tanggal_jatuh_tempo);
            $denda = $hariTerlambat * 1000;
        }

        $perpustakaanPeminjaman->update([
            'tanggal_kembali' => $tanggalKembali,
            'denda' => $denda,
            'status' => $denda > 0 ? 'terlambat' : 'dikembalikan',
        ]);

        $perpustakaanPeminjaman->kitab()->increment('tersedia');

        activity()->log('Pengembalian kitab: ' . $perpustakaanPeminjaman->kitab?->judul . ' (denda: Rp ' . number_format($denda) . ')');

        return redirect()->route('perpustakaan.peminjaman.index')
            ->with('success', 'Pengembalian berhasil dicatat.' . ($denda > 0 ? ' Denda: Rp ' . number_format($denda) : ''));
    }

    public function destroy(PerpustakaanPeminjaman $perpustakaanPeminjaman): RedirectResponse
    {
        if ($perpustakaanPeminjaman->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($perpustakaanPeminjaman->status === 'dipinjam') {
            $perpustakaanPeminjaman->kitab()->increment('tersedia');
        }

        $perpustakaanPeminjaman->delete();

        activity()->log('Menghapus record peminjaman');

        return redirect()->route('perpustakaan.peminjaman.index')
            ->with('success', 'Record peminjaman berhasil dihapus.');
    }
}
