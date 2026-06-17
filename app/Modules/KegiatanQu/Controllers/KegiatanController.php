<?php

namespace App\Modules\KegiatanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\KegiatanQu\Models\Kegiatan;
use App\Modules\KegiatanQu\Models\KegiatanPendaftaran;
use App\Modules\KegiatanQu\Requests\StoreKegiatanRequest;
use App\Modules\KegiatanQu\Requests\UpdateKegiatanRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KegiatanController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $kegiatans = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $pembinas = collect();
            return view('modules.kegiatan-qu.kegiatan.index', compact('kegiatans', 'pembinas'));
        }

        $search = trim((string) $request->string('q'));
        $status = $request->get('status');

        $kegiatans = Kegiatan::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with('pembina')
            ->when($search !== '', fn ($q) => $q->where('nama', 'like', "%{$search}%"))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $pembinas = User::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('modules.kegiatan-qu.kegiatan.index', compact('kegiatans', 'pembinas'));
    }

    public function create(): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            return redirect()->route('kegiatan.dashboard')
                ->with('error', 'Anda tidak memiliki akses tanpa terhubung ke pesantren.');
        }

        $pembinas = User::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('modules.kegiatan-qu.kegiatan.create', compact('pembinas'));
    }

    public function store(StoreKegiatanRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        if (! $tenantId) {
            return back()->with('error', 'Anda tidak memiliki akses tanpa terhubung ke pesantren.');
        }

        $data = $request->validated();
        $data['tenant_id'] = $tenantId;

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('kegiatan-covers', 'public');
        }

        $kegiatan = Kegiatan::withoutTenantScope()->create($data);

        activity()->log('Menambah kegiatan: ' . $kegiatan->nama);

        return redirect()->route('kegiatan.kegiatan.show', $kegiatan)
            ->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function show(Kegiatan $kegiatan): View
    {
        if ($kegiatan->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $kegiatan->load(['pembina', 'pendaftarans.santri', 'pertemuans' => function ($q) {
            $q->orderByDesc('tanggal')->limit(10);
        }]);

        return view('modules.kegiatan-qu.kegiatan.show', compact('kegiatan'));
    }

    public function edit(Kegiatan $kegiatan): View
    {
        if ($kegiatan->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $pembinas = User::withoutTenantScope()
            ->where('tenant_id', $kegiatan->tenant_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('modules.kegiatan-qu.kegiatan.edit', compact('kegiatan', 'pembinas'));
    }

    public function update(UpdateKegiatanRequest $request, Kegiatan $kegiatan): RedirectResponse
    {
        if ($kegiatan->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $data = $request->validated();

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('kegiatan-covers', 'public');
        }

        $kegiatan->update($data);

        activity()->log('Mengupdate kegiatan: ' . $kegiatan->nama);

        return redirect()->route('kegiatan.kegiatan.show', $kegiatan)
            ->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan): RedirectResponse
    {
        if ($kegiatan->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $nama = $kegiatan->nama;
        $kegiatan->nilais()->delete();
        $kegiatan->pendaftarans()->delete();
        $kegiatan->pertemuans()->each(fn ($p) => $p->presensis()->delete());
        $kegiatan->pertemuans()->delete();
        $kegiatan->delete();

        activity()->log('Menghapus kegiatan: ' . $nama);

        return redirect()->route('kegiatan.kegiatan.index')
            ->with('success', 'Kegiatan berhasil dihapus.');
    }
}
