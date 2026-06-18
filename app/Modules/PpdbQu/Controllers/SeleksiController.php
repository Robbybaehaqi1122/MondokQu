<?php

namespace App\Modules\PpdbQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PpdbQu\Models\PpdbPendaftaran;
use App\Modules\PpdbQu\Models\PpdbSeleksi;
use App\Modules\PpdbQu\Requests\StoreSeleksiRequest;
use App\Modules\PpdbQu\Requests\UpdateSeleksiRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeleksiController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $seleksis = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            return view('modules.ppdb-qu.seleksi.index', compact('seleksis'));
        }

        $search = trim((string) $request->string('q'));
        $jenis = $request->get('jenis');
        $hasil = $request->get('hasil');

        $seleksis = PpdbSeleksi::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with(['pendaftaran', 'penguji'])
            ->when($search !== '', fn ($q) => $q->whereHas('pendaftaran', fn ($pq) => $pq->where('nama_lengkap', 'like', "%{$search}%")))
            ->when($jenis, fn ($q) => $q->where('jenis', $jenis))
            ->when($hasil, fn ($q) => $q->where('hasil', $hasil))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('modules.ppdb-qu.seleksi.index', compact('seleksis'));
    }

    public function store(StoreSeleksiRequest $request, PpdbPendaftaran $ppdbPendaftaran): RedirectResponse
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        if ($ppdbPendaftaran->tenant_id !== $tenantId) {
            abort(403);
        }

        $exists = PpdbSeleksi::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('pendaftaran_id', $ppdbPendaftaran->id)
            ->where('jenis', $request->jenis)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Seleksi jenis ' . $request->jenis . ' sudah ada untuk pendaftar ini.');
        }

        PpdbSeleksi::withoutTenantScope()->create(
            $request->validated() + [
                'tenant_id' => $tenantId,
                'pendaftaran_id' => $ppdbPendaftaran->id,
                'diuji_oleh' => $user->id,
            ]
        );

        if ($ppdbPendaftaran->status === 'menunggu') {
            $ppdbPendaftaran->update(['status' => 'diproses', 'diproses_oleh' => $user->id]);
        }

        activity()->log('Input seleksi PPDB: ' . $ppdbPendaftaran->nomor_pendaftaran . ' - ' . $request->jenis);

        return redirect()->route('ppdb.pendaftaran.show', $ppdbPendaftaran)
            ->with('success', 'Hasil seleksi berhasil disimpan.');
    }

    public function update(UpdateSeleksiRequest $request, PpdbSeleksi $ppdbSeleksi): RedirectResponse
    {
        $user = auth()->user();
        if ($ppdbSeleksi->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $ppdbSeleksi->update($request->validated());

        activity()->log('Update seleksi PPDB: ' . $ppdbSeleksi->pendaftaran?->nomor_pendaftaran . ' - ' . $ppdbSeleksi->jenis);

        return redirect()->route('ppdb.seleksi.index')
            ->with('success', 'Hasil seleksi berhasil diperbarui.');
    }

    public function destroy(PpdbSeleksi $ppdbSeleksi): RedirectResponse
    {
        if ($ppdbSeleksi->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $ppdbSeleksi->delete();

        return redirect()->route('ppdb.seleksi.index')
            ->with('success', 'Data seleksi berhasil dihapus.');
    }
}
