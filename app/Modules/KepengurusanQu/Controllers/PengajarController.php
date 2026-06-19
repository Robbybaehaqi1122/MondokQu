<?php

namespace App\Modules\KepengurusanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KepengurusanQu\Models\Pengajar;
use App\Modules\KepengurusanQu\Requests\StorePengajarRequest;
use App\Modules\KepengurusanQu\Requests\UpdatePengajarRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengajarController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $pengajars = Pengajar::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->withCount('jadwals')
            ->search($request->get('search'))
            ->orderByDesc('id')
            ->paginate(20);

        return view('modules.kepengurusan-qu.pengajar.index', compact('pengajars'));
    }

    public function create(): View|RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return redirect()->route('kepengurusan.dashboard')->with('error', 'Akses ditolak.');
        }

        return view('modules.kepengurusan-qu.pengajar.create');
    }

    public function store(StorePengajarRequest $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return back()->with('error', 'Akses ditolak.');
        }

        Pengajar::withoutTenantScope()->create([
            'tenant_id' => $tenantId,
            'created_by' => auth()->id(),
            ...$request->validated(),
        ]);

        return redirect()->route('kepengurusan.pengajar.index')
            ->with('success', 'Pengajar berhasil ditambahkan.');
    }

    public function show(Pengajar $pengajar): View
    {
        if ($pengajar->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $pengajar->load('jadwals');

        return view('modules.kepengurusan-qu.pengajar.show', compact('pengajar'));
    }

    public function edit(Pengajar $pengajar): View|RedirectResponse
    {
        if ($pengajar->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        return view('modules.kepengurusan-qu.pengajar.edit', compact('pengajar'));
    }

    public function update(UpdatePengajarRequest $request, Pengajar $pengajar): RedirectResponse
    {
        if ($pengajar->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $pengajar->update($request->validated());

        return redirect()->route('kepengurusan.pengajar.show', $pengajar)
            ->with('success', 'Pengajar berhasil diperbarui.');
    }

    public function destroy(Pengajar $pengajar): RedirectResponse
    {
        if ($pengajar->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $pengajar->jadwals()->delete();
        $pengajar->delete();

        return redirect()->route('kepengurusan.pengajar.index')
            ->with('success', 'Pengajar berhasil dihapus.');
    }
}
