<?php

namespace App\Modules\PpdbQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PpdbQu\Models\PpdbGelombang;
use App\Modules\PpdbQu\Models\PpdbPendaftaran;
use App\Modules\PpdbQu\Models\PpdbPengumuman;
use App\Modules\PpdbQu\Requests\StorePengumumanRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;



class PengumumanController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $pengumumans = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            return view('modules.ppdb-qu.pengumuman.index', compact('pengumumans'));
        }

        $pengumumans = PpdbPengumuman::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with(['gelombang', 'creator'])
            ->orderByDesc('tanggal_pengumuman')
            ->paginate(20);

        $gelombangs = PpdbGelombang::withoutTenantScope()
            ->where('tenant_id', $tenantId)->orderBy('nama')->get();

        return view('modules.ppdb-qu.pengumuman.index', compact('pengumumans', 'gelombangs'));
    }

    public function create(): View
    {
        $tenantId = auth()->user()->tenant_id;

        $gelombangs = collect();
        if ($tenantId) {
            $gelombangs = PpdbGelombang::withoutTenantScope()
                ->where('tenant_id', $tenantId)->orderBy('nama')->get();
        }

        return view('modules.ppdb-qu.pengumuman.create', compact('gelombangs'));
    }

    public function store(StorePengumumanRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        if (! $tenantId) {
            return back()->with('error', 'Akses ditolak.');
        }

        PpdbPengumuman::withoutTenantScope()->create(
            $request->validated() + [
                'tenant_id' => $tenantId,
                'created_by' => $user->id,
            ]
        );

        activity()->log('Membuat pengumuman PPDB: ' . $request->judul);

        return redirect()->route('ppdb.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function show(PpdbPengumuman $ppdbPengumuman): View
    {
        if ($ppdbPengumuman->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $ppdbPengumuman->load(['gelombang', 'creator']);

        $pendaftarans = PpdbPendaftaran::withoutTenantScope()
            ->where('tenant_id', $ppdbPengumuman->tenant_id)
            ->where('gelombang_id', $ppdbPengumuman->gelombang_id)
            ->with(['seleksis'])
            ->orderBy('nama_lengkap')
            ->get();

        return view('modules.ppdb-qu.pengumuman.show', [
            'pengumuman' => $ppdbPengumuman,
            'pendaftarans' => $pendaftarans,
        ]);
    }

    public function publicShow(string $uuid): View
    {
        $pengumuman = PpdbPengumuman::withoutTenantScope()
            ->where('uuid', $uuid)
            ->whereNotNull('published_at')
            ->with(['gelombang', 'creator'])
            ->firstOrFail();

        $pendaftarans = PpdbPendaftaran::withoutTenantScope()
            ->where('tenant_id', $pengumuman->tenant_id)
            ->where('gelombang_id', $pengumuman->gelombang_id)
            ->orderBy('nama_lengkap')
            ->get(['id', 'nomor_pendaftaran', 'nama_lengkap', 'status']);

        return view('modules.ppdb-qu.pengumuman.public-show', [
            'pengumuman' => $pengumuman,
            'pendaftarans' => $pendaftarans,
        ]);
    }

    public function publish(PpdbPengumuman $ppdbPengumuman): RedirectResponse
    {
        if ($ppdbPengumuman->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $ppdbPengumuman->update(['published_at' => now()]);

        activity()->log('Mempublikasikan pengumuman PPDB: ' . $ppdbPengumuman->judul);

        return redirect()->route('ppdb.pengumuman.show', $ppdbPengumuman)
            ->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function destroy(PpdbPengumuman $ppdbPengumuman): RedirectResponse
    {
        if ($ppdbPengumuman->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $ppdbPengumuman->delete();

        return redirect()->route('ppdb.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}
