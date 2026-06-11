<?php

namespace App\Modules\KesehatanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KesehatanRekamMedis;
use App\Models\Santri;
use App\Modules\KesehatanQu\Requests\StoreRekamMedisRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KesehatanQuRekamMedisController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));

        $santris = Santri::query()
            ->visibleTo($currentUser)
            ->with('rekamMedis')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        return view('modules.kesehatan-qu.rekam-medis.index', [
            'santris' => $santris,
            'filters' => ['q' => $search],
        ]);
    }

    public function show(Request $request, Santri $santri): View
    {
        $currentUser = $request->user();

        $santri->load(['rekamMedis']);

        return view('modules.kesehatan-qu.rekam-medis.show', [
            'santri' => $santri,
            'rekamMedis' => $santri->rekamMedis,
        ]);
    }

    public function store(StoreRekamMedisRequest $request): RedirectResponse
    {
        $currentUser = $request->user();
        $validated = $request->validated();

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->findOrFail($validated['santri_id']);

        $rekamMedis = KesehatanRekamMedis::query()->updateOrCreate(
            ['tenant_id' => $currentUser->tenant_id, 'santri_id' => $santri->id],
            [
                'golongan_darah' => $validated['golongan_darah'] ?? null,
                'riwayat_penyakit' => $validated['riwayat_penyakit'] ?? null,
                'alergi_obat' => $validated['alergi_obat'] ?? null,
                'alergi_makanan' => $validated['alergi_makanan'] ?? null,
                'tinggi_badan' => $validated['tinggi_badan'] ?? null,
                'berat_badan' => $validated['berat_badan'] ?? null,
                'catatan' => $validated['catatan'] ?? null,
                'created_by' => $currentUser->id,
            ]
        );

        $this->activityLogger->log(
            action: 'rekam_medis_' . ($rekamMedis->wasRecentlyCreated ? 'created' : 'updated'),
            actor: $currentUser,
            target: $rekamMedis,
            description: "Rekam medis untuk {$santri->full_name} " . ($rekamMedis->wasRecentlyCreated ? 'dibuat' : 'diperbarui') . ".",
            properties: ['santri_id' => $santri->id, 'santri_name' => $santri->full_name],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('kesehatan.rekam-medis.show', $santri)
            ->with('success', "Rekam medis {$santri->full_name} berhasil disimpan.");
    }

    public function update(StoreRekamMedisRequest $request, Santri $santri): RedirectResponse
    {
        return $this->store($request);
    }
}
