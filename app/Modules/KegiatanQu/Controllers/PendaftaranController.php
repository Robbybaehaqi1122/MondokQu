<?php

namespace App\Modules\KegiatanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Modules\KegiatanQu\Models\Kegiatan;
use App\Modules\KegiatanQu\Models\KegiatanPendaftaran;
use App\Modules\KegiatanQu\Requests\StorePendaftaranRequest;
use App\Modules\KegiatanQu\Requests\UpdatePendaftaranRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendaftaranController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $pendaftarans = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $kegiatans = collect();
            return view('modules.kegiatan-qu.pendaftaran.index', compact('pendaftarans', 'kegiatans'));
        }

        $search = trim((string) $request->string('q'));
        $kegiatanId = $request->get('kegiatan_id');
        $status = $request->get('status');

        $pendaftarans = KegiatanPendaftaran::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with(['kegiatan', 'santri', 'confirmedBy'])
            ->when($search !== '', fn ($q) => $q->whereHas('santri', fn ($sq) => $sq->where('full_name', 'like', "%{$search}%")))
            ->when($kegiatanId, fn ($q) => $q->where('kegiatan_id', $kegiatanId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $kegiatans = Kegiatan::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->orderBy('nama')
            ->get();

        return view('modules.kegiatan-qu.pendaftaran.index', compact('pendaftarans', 'kegiatans'));
    }

    public function store(StorePendaftaranRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        if (! $tenantId) {
            return back()->with('error', 'Anda tidak memiliki akses tanpa terhubung ke pesantren.');
        }

        $exists = KegiatanPendaftaran::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('kegiatan_id', $request->kegiatan_id)
            ->where('santri_id', $request->santri_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Santri sudah terdaftar di kegiatan ini.');
        }

        $pendaftaran = KegiatanPendaftaran::withoutTenantScope()->create([
            'tenant_id' => $tenantId,
            'kegiatan_id' => $request->kegiatan_id,
            'santri_id' => $request->santri_id,
            'catatan' => $request->catatan,
        ]);

        $kegiatan = Kegiatan::withoutTenantScope()->find($request->kegiatan_id);
        activity()->log('Pendaftaran kegiatan: ' . ($kegiatan?->nama ?? '#' . $request->kegiatan_id));

        return redirect()->route('kegiatan.pendaftaran.index')
            ->with('success', 'Santri berhasil didaftarkan.');
    }

    public function update(UpdatePendaftaranRequest $request, KegiatanPendaftaran $kegiatanPendaftaran): RedirectResponse
    {
        $user = auth()->user();
        if ($kegiatanPendaftaran->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $status = $request->status;
        $data = ['status' => $status];

        if ($status === 'terkonfirmasi' && ! $kegiatanPendaftaran->confirmed_at) {
            $data['confirmed_at'] = now();
            $data['confirmed_by'] = $user->id;
        }

        $kegiatanPendaftaran->update($data);

        activity()->log('Mengupdate status pendaftaran: ' . $status);

        return redirect()->route('kegiatan.pendaftaran.index')
            ->with('success', 'Status pendaftaran berhasil diperbarui.');
    }

    public function destroy(KegiatanPendaftaran $kegiatanPendaftaran): RedirectResponse
    {
        if ($kegiatanPendaftaran->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $kegiatanPendaftaran->delete();

        return redirect()->route('kegiatan.pendaftaran.index')
            ->with('success', 'Pendaftaran berhasil dihapus.');
    }
}
