<?php

namespace App\Modules\KepengurusanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KepengurusanQu\Models\Pengurus;
use App\Modules\KepengurusanQu\Requests\StorePengurusRequest;
use App\Modules\KepengurusanQu\Requests\UpdatePengurusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengurusController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $penguruses = Pengurus::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->search($request->get('search'))
            ->orderByDesc('id')
            ->paginate(20);

        return view('modules.kepengurusan-qu.pengurus.index', compact('penguruses'));
    }

    public function create(): View|RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return redirect()->route('kepengurusan.dashboard')->with('error', 'Akses ditolak.');
        }

        return view('modules.kepengurusan-qu.pengurus.create');
    }

    public function store(StorePengurusRequest $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return back()->with('error', 'Akses ditolak.');
        }

        Pengurus::withoutTenantScope()->create([
            'tenant_id' => $tenantId,
            'created_by' => auth()->id(),
            ...$request->validated(),
        ]);

        return redirect()->route('kepengurusan.pengurus.index')
            ->with('success', 'Pengurus berhasil ditambahkan.');
    }

    public function show(Pengurus $pengurus): View
    {
        if ($pengurus->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        return view('modules.kepengurusan-qu.pengurus.show', compact('pengurus'));
    }

    public function edit(Pengurus $pengurus): View|RedirectResponse
    {
        if ($pengurus->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        return view('modules.kepengurusan-qu.pengurus.edit', compact('pengurus'));
    }

    public function update(UpdatePengurusRequest $request, Pengurus $pengurus): RedirectResponse
    {
        if ($pengurus->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $pengurus->update($request->validated());

        return redirect()->route('kepengurusan.pengurus.show', $pengurus)
            ->with('success', 'Pengurus berhasil diperbarui.');
    }

    public function destroy(Pengurus $pengurus): RedirectResponse
    {
        if ($pengurus->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $pengurus->delete();

        return redirect()->route('kepengurusan.pengurus.index')
            ->with('success', 'Pengurus berhasil dihapus.');
    }
}
