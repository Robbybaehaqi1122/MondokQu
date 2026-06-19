<?php

namespace App\Modules\KepengurusanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KepengurusanQu\Models\Jadwal;
use App\Modules\KepengurusanQu\Models\Pengajar;
use App\Modules\KepengurusanQu\Requests\StoreJadwalRequest;
use App\Modules\KepengurusanQu\Requests\UpdateJadwalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $jadwals = Jadwal::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with('pengajar')
            ->search($request->get('search'))
            ->hari($request->get('hari'))
            ->pengajar($request->get('pengajar_id'))
            ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->orderBy('jam_mulai')
            ->paginate(20);

        $pengajars = Pengajar::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->active()
            ->orderBy('nama')
            ->get();

        return view('modules.kepengurusan-qu.jadwal.index', compact('jadwals', 'pengajars'));
    }

    public function create(): View|RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return redirect()->route('kepengurusan.dashboard')->with('error', 'Akses ditolak.');
        }

        $pengajars = Pengajar::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->active()
            ->orderBy('nama')
            ->get();

        return view('modules.kepengurusan-qu.jadwal.create', compact('pengajars'));
    }

    public function store(StoreJadwalRequest $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return back()->with('error', 'Akses ditolak.');
        }

        Jadwal::withoutTenantScope()->create([
            'tenant_id' => $tenantId,
            'created_by' => auth()->id(),
            ...$request->validated(),
        ]);

        return redirect()->route('kepengurusan.jadwal.index')
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function edit(Jadwal $jadwal): View|RedirectResponse
    {
        if ($jadwal->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $pengajars = Pengajar::withoutTenantScope()
            ->where('tenant_id', $jadwal->tenant_id)
            ->active()
            ->orderBy('nama')
            ->get();

        return view('modules.kepengurusan-qu.jadwal.edit', compact('jadwal', 'pengajars'));
    }

    public function update(UpdateJadwalRequest $request, Jadwal $jadwal): RedirectResponse
    {
        if ($jadwal->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $jadwal->update($request->validated());

        return redirect()->route('kepengurusan.jadwal.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        if ($jadwal->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $jadwal->delete();

        return redirect()->route('kepengurusan.jadwal.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }
}
