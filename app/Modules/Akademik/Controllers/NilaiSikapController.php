<?php

namespace App\Modules\Akademik\Controllers;

use App\Models\NilaiSikap;
use App\Models\Santri;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NilaiSikapController extends Controller
{

    public function index(Request $request): View
    {
        $currentUser = $request->user();

        $query = NilaiSikap::query()
            ->visibleTo($currentUser)
            ->with('santri');

        if ($search = $request->input('q')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->whereHas('santri', fn ($q) => $q->where('full_name', 'like', "%{$escaped}%"));
        }
        if ($semester = $request->input('semester')) {
            $query->where('semester', $semester);
        }

        $nilais = $query->orderByDesc('created_at')->paginate(20);

        $semesters = NilaiSikap::query()
            ->visibleTo($currentUser)
            ->distinct()
            ->orderBy('semester', 'desc')
            ->pluck('semester');

        return view('modules.akademik.nilai-sikap.index', [
            'nilais' => $nilais,
            'semesters' => $semesters,
        ]);
    }

    public function create(Request $request): View
    {
        $currentUser = $request->user();

        $santris = Santri::query()
            ->visibleTo($currentUser)
            ->active()
            ->select('id', 'full_name', 'nis')
            ->orderBy('full_name')
            ->get();

        $semesters = $this->availableSemesters();

        return view('modules.akademik.nilai-sikap.create', [
            'santris' => $santris,
            'semesters' => $semesters,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', NilaiSikap::class);
        $currentUser = $request->user();

        $tenantId = $currentUser->effectiveTenantId();

        if (! $tenantId) {
            return back()->withErrors(['santri_id' => 'Tidak ada tenant yang tersedia.'])->withInput();
        }

        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'semester' => ['required', 'string', 'max:50'],
            'sikap_spiritual' => ['nullable', 'in:SB,B,C,K'],
            'deskripsi_spiritual' => ['nullable', 'string', 'max:1000'],
            'sikap_sosial' => ['nullable', 'in:SB,B,C,K'],
            'deskripsi_sosial' => ['nullable', 'string', 'max:1000'],
            'catatan_wali' => ['nullable', 'string', 'max:1000'],
        ]);

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->findOrFail($validated['santri_id']);

        $exists = NilaiSikap::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->where('semester', $validated['semester'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['santri_id' => 'Nilai sikap untuk santri dan semester ini sudah ada. Gunakan form edit.'])->withInput();
        }

        NilaiSikap::query()->create([
            'tenant_id' => $tenantId,
            'santri_id' => $santri->id,
            'semester' => $validated['semester'],
            'sikap_spiritual' => $validated['sikap_spiritual'],
            'deskripsi_spiritual' => $validated['deskripsi_spiritual'],
            'sikap_sosial' => $validated['sikap_sosial'],
            'deskripsi_sosial' => $validated['deskripsi_sosial'],
            'catatan_wali' => $validated['catatan_wali'],
        ]);

        return redirect()->route('akademik.nilai-sikap.index')
            ->with('success', 'Nilai sikap berhasil dicatat.');
    }

    public function show(Request $request): View
    {
        $this->authorize('viewAny', NilaiSikap::class);
        $currentUser = $request->user();

        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'semester' => ['required', 'string'],
        ]);

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->findOrFail($validated['santri_id']);

        $nilaiSikap = NilaiSikap::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->where('semester', $validated['semester'])
            ->first();

        return view('modules.akademik.nilai-sikap.show', [
            'santri' => $santri,
            'semester' => $validated['semester'],
            'nilaiSikap' => $nilaiSikap,
        ]);
    }

    public function edit(Request $request, NilaiSikap $nilaiSikap): View
    {
        $this->authorize('update', $nilaiSikap);
        $currentUser = $request->user();

        $santris = Santri::query()
            ->visibleTo($currentUser)
            ->active()
            ->select('id', 'full_name', 'nis')
            ->orderBy('full_name')
            ->get();

        $semesters = $this->availableSemesters();

        return view('modules.akademik.nilai-sikap.edit', [
            'nilaiSikap' => $nilaiSikap->load('santri'),
            'santris' => $santris,
            'semesters' => $semesters,
        ]);
    }

    public function update(Request $request, NilaiSikap $nilaiSikap): RedirectResponse
    {
        $this->authorize('update', $nilaiSikap);
        $currentUser = $request->user();

        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'semester' => ['required', 'string', 'max:50'],
            'sikap_spiritual' => ['nullable', 'in:SB,B,C,K'],
            'deskripsi_spiritual' => ['nullable', 'string', 'max:1000'],
            'sikap_sosial' => ['nullable', 'in:SB,B,C,K'],
            'deskripsi_sosial' => ['nullable', 'string', 'max:1000'],
            'catatan_wali' => ['nullable', 'string', 'max:1000'],
        ]);

        $otherExists = NilaiSikap::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $validated['santri_id'])
            ->where('semester', $validated['semester'])
            ->where('id', '!=', $nilaiSikap->id)
            ->exists();

        if ($otherExists) {
            return back()->withErrors(['santri_id' => 'Nilai sikap untuk santri dan semester ini sudah ada.'])->withInput();
        }

        $nilaiSikap->update([
            'santri_id' => $validated['santri_id'],
            'semester' => $validated['semester'],
            'sikap_spiritual' => $validated['sikap_spiritual'],
            'deskripsi_spiritual' => $validated['deskripsi_spiritual'],
            'sikap_sosial' => $validated['sikap_sosial'],
            'deskripsi_sosial' => $validated['deskripsi_sosial'],
            'catatan_wali' => $validated['catatan_wali'],
        ]);

        return redirect()->route('akademik.nilai-sikap.index')
            ->with('success', 'Nilai sikap berhasil diperbarui.');
    }

    public function destroy(Request $request, NilaiSikap $nilaiSikap): RedirectResponse
    {
        $this->authorize('delete', $nilaiSikap);

        $nilaiSikap->delete();

        return redirect()->route('akademik.nilai-sikap.index')
            ->with('success', 'Nilai sikap berhasil dihapus.');
    }

    protected function availableSemesters(): array
    {
        $year = now()->year;
        $nextYear = $year + 1;

        return [
            "{$year}/{$nextYear} Ganjil",
            "{$year}/{$nextYear} Genap",
            (($year - 1)."/{$year} Ganjil"),
            (($year - 1)."/{$year} Genap"),
        ];
    }
}
