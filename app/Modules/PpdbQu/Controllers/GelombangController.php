<?php

namespace App\Modules\PpdbQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PpdbQu\Models\PpdbGelombang;
use App\Modules\PpdbQu\Requests\StoreGelombangRequest;
use App\Modules\PpdbQu\Requests\UpdateGelombangRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Facades\Activity;

class GelombangController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $gelombangs = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            return view('modules.ppdb-qu.gelombang.index', compact('gelombangs'));
        }

        $gelombangs = PpdbGelombang::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->withCount('pendaftarans')
            ->orderByDesc('tanggal_mulai')
            ->paginate(20);

        return view('modules.ppdb-qu.gelombang.index', compact('gelombangs'));
    }

    public function create(): View
    {
        return view('modules.ppdb-qu.gelombang.create');
    }

    public function store(StoreGelombangRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        if (! $tenantId) {
            return back()->with('error', 'Akses ditolak.');
        }

        PpdbGelombang::withoutTenantScope()->create(
            $request->validated() + ['tenant_id' => $tenantId]
        );

        Activity::log('Menambah gelombang PPDB: ' . $request->nama);

        return redirect()->route('ppdb.gelombang.index')
            ->with('success', 'Gelombang berhasil ditambahkan.');
    }

    public function edit(PpdbGelombang $ppdbGelombang): View
    {
        if ($ppdbGelombang->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
        return view('modules.ppdb-qu.gelombang.edit', ['gelombang' => $ppdbGelombang]);
    }

    public function update(UpdateGelombangRequest $request, PpdbGelombang $ppdbGelombang): RedirectResponse
    {
        if ($ppdbGelombang->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $ppdbGelombang->update($request->validated());

        Activity::log('Mengupdate gelombang PPDB: ' . $ppdbGelombang->nama);

        return redirect()->route('ppdb.gelombang.index')
            ->with('success', 'Gelombang berhasil diperbarui.');
    }

    public function destroy(PpdbGelombang $ppdbGelombang): RedirectResponse
    {
        if ($ppdbGelombang->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($ppdbGelombang->pendaftarans()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus gelombang yang sudah memiliki pendaftar.');
        }

        $ppdbGelombang->delete();

        return redirect()->route('ppdb.gelombang.index')
            ->with('success', 'Gelombang berhasil dihapus.');
    }
}
