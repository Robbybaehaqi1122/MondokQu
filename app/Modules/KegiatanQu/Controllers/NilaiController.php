<?php

namespace App\Modules\KegiatanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Modules\KegiatanQu\Models\Kegiatan;
use App\Modules\KegiatanQu\Models\KegiatanNilai;
use App\Modules\KegiatanQu\Requests\StoreNilaiRequest;
use App\Modules\KegiatanQu\Requests\UpdateNilaiRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NilaiController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $nilais = collect();
            $kegiatans = collect();
            return view('modules.kegiatan-qu.nilai.index', compact('nilais', 'kegiatans'));
        }

        $search = trim((string) $request->string('q'));
        $kegiatanId = $request->get('kegiatan_id');
        $aspek = $request->get('aspek');

        $nilais = KegiatanNilai::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with(['kegiatan', 'santri', 'penilai'])
            ->when($search !== '', fn ($q) => $q->whereHas('santri', fn ($sq) => $sq->where('full_name', 'like', "%{$search}%")))
            ->when($kegiatanId, fn ($q) => $q->where('kegiatan_id', $kegiatanId))
            ->when($aspek, fn ($q) => $q->where('aspek', $aspek))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $kegiatans = Kegiatan::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->orderBy('nama')
            ->get();

        $aspekList = KegiatanNilai::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->select('aspek')->distinct()->pluck('aspek');

        return view('modules.kegiatan-qu.nilai.index', compact('nilais', 'kegiatans', 'aspekList'));
    }

    public function store(StoreNilaiRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        if (! $tenantId) {
            return back()->with('error', 'Anda tidak memiliki akses tanpa terhubung ke pesantren.');
        }

        $exists = KegiatanNilai::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('kegiatan_id', $request->kegiatan_id)
            ->where('santri_id', $request->santri_id)
            ->where('aspek', $request->aspek)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Nilai untuk aspek ini sudah ada. Gunakan fitur edit.');
        }

        KegiatanNilai::withoutTenantScope()->create(
            $request->validated() + [
                'tenant_id' => $tenantId,
                'dinilai_oleh' => $user->id,
            ]
        );

        return redirect()->route('kegiatan.nilai.index')
            ->with('success', 'Nilai berhasil ditambahkan.');
    }

    public function update(UpdateNilaiRequest $request, KegiatanNilai $kegiatanNilai): RedirectResponse
    {
        if ($kegiatanNilai->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $kegiatanNilai->update($request->validated());

        return redirect()->route('kegiatan.nilai.index')
            ->with('success', 'Nilai berhasil diperbarui.');
    }

    public function destroy(KegiatanNilai $kegiatanNilai): RedirectResponse
    {
        if ($kegiatanNilai->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $kegiatanNilai->delete();

        return redirect()->route('kegiatan.nilai.index')
            ->with('success', 'Nilai berhasil dihapus.');
    }
}
