<?php

namespace App\Modules\Akademik\Controllers;

use App\Models\MataPelajaran;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class MataPelajaranController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $currentUser = $request->user();

        $mapels = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->withCount('nilaiSantris')
            ->orderBy('is_active', 'desc')
            ->orderBy('nama')
            ->paginate(20);

        return view('modules.akademik.mata-pelajaran.index', [
            'mapels' => $mapels,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MataPelajaran::class);
        $currentUser = $request->user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'kkm' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $tenantId = $currentUser->effectiveTenantId();

        if (! $tenantId) {
            return back()->withErrors(['nama' => 'Tidak ada tenant yang tersedia. Hubungi administrator.'])->withInput();
        }

        $exists = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->where('nama', $validated['nama'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['nama' => 'Mata pelajaran dengan nama tersebut sudah ada.'])->withInput();
        }

        MataPelajaran::query()->create([
            'tenant_id' => $tenantId,
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'],
            'kkm' => $validated['kkm'],
        ]);

        return redirect()->route('akademik.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, MataPelajaran $mataPelajaran): RedirectResponse
    {
        $this->authorize('update', $mataPelajaran);
        $currentUser = $request->user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'kkm' => ['required', 'integer', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        $exists = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->where('nama', $validated['nama'])
            ->where('id', '!=', $mataPelajaran->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['nama' => 'Mata pelajaran dengan nama tersebut sudah ada.'])->withInput();
        }

        $mataPelajaran->update($validated);

        return redirect()->route('akademik.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Request $request, MataPelajaran $mataPelajaran): RedirectResponse
    {
        $this->authorize('delete', $mataPelajaran);
        $currentUser = $request->user();

        if ($mataPelajaran->nilaiSantris()->exists()) {
            return back()->withErrors(['delete' => 'Tidak dapat menghapus mata pelajaran yang sudah memiliki nilai.']);
        }

        $mataPelajaran->delete();

        return redirect()->route('akademik.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
