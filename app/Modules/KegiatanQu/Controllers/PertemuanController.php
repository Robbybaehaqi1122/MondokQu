<?php

namespace App\Modules\KegiatanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KegiatanQu\Models\Kegiatan;
use App\Modules\KegiatanQu\Models\KegiatanPendaftaran;
use App\Modules\KegiatanQu\Models\KegiatanPertemuan;
use App\Modules\KegiatanQu\Requests\StorePertemuanRequest;
use App\Modules\KegiatanQu\Requests\UpdatePertemuanRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PertemuanController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $pertemuans = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $kegiatans = collect();
            return view('modules.kegiatan-qu.pertemuan.index', compact('pertemuans', 'kegiatans'));
        }

        $search = trim((string) $request->string('q'));
        $kegiatanId = $request->get('kegiatan_id');

        $pertemuans = KegiatanPertemuan::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with(['kegiatan', 'creator'])
            ->when($search !== '', fn ($q) => $q->where('materi', 'like', "%{$search}%"))
            ->when($kegiatanId, fn ($q) => $q->where('kegiatan_id', $kegiatanId))
            ->orderByDesc('tanggal')
            ->paginate(20)
            ->withQueryString();

        $kegiatans = Kegiatan::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->orderBy('nama')
            ->get();

        return view('modules.kegiatan-qu.pertemuan.index', compact('pertemuans', 'kegiatans'));
    }

    public function create(): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            return redirect()->route('kegiatan.dashboard')
                ->with('error', 'Anda tidak memiliki akses tanpa terhubung ke pesantren.');
        }

        $kegiatans = Kegiatan::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        return view('modules.kegiatan-qu.pertemuan.create', compact('kegiatans'));
    }

    public function store(StorePertemuanRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        if (! $tenantId) {
            return back()->with('error', 'Anda tidak memiliki akses tanpa terhubung ke pesantren.');
        }

        $pertemuan = KegiatanPertemuan::withoutTenantScope()->create(
            $request->validated() + [
                'tenant_id' => $tenantId,
                'created_by' => $user->id,
            ]
        );

        $kegiatan = Kegiatan::withoutTenantScope()->find($request->kegiatan_id);
        activity()->log('Menambah pertemuan kegiatan: ' . ($kegiatan?->nama ?? '#' . $request->kegiatan_id));

        return redirect()->route('kegiatan.pertemuan.index')
            ->with('success', 'Pertemuan berhasil ditambahkan.');
    }

    public function show(KegiatanPertemuan $kegiatanPertemuan): View
    {
        if ($kegiatanPertemuan->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $kegiatanPertemuan->loadMissing(['kegiatan', 'creator', 'presensis.santri']);

        return view('modules.kegiatan-qu.pertemuan.show', [
            'pertemuan' => $kegiatanPertemuan,
        ]);
    }

    public function edit(KegiatanPertemuan $kegiatanPertemuan): View
    {
        if ($kegiatanPertemuan->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $kegiatans = Kegiatan::withoutTenantScope()
            ->where('tenant_id', $kegiatanPertemuan->tenant_id)
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        return view('modules.kegiatan-qu.pertemuan.edit', [
            'pertemuan' => $kegiatanPertemuan,
            'kegiatans' => $kegiatans,
        ]);
    }

    public function update(UpdatePertemuanRequest $request, KegiatanPertemuan $kegiatanPertemuan): RedirectResponse
    {
        if ($kegiatanPertemuan->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $kegiatanPertemuan->update($request->validated());

        return redirect()->route('kegiatan.pertemuan.index')
            ->with('success', 'Pertemuan berhasil diperbarui.');
    }

    public function destroy(KegiatanPertemuan $kegiatanPertemuan): RedirectResponse
    {
        if ($kegiatanPertemuan->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $kegiatanPertemuan->presensis()->delete();
        $kegiatanPertemuan->delete();

        return redirect()->route('kegiatan.pertemuan.index')
            ->with('success', 'Pertemuan berhasil dihapus.');
    }
}
