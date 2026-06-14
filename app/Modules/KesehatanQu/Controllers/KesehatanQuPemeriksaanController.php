<?php

namespace App\Modules\KesehatanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KesehatanObat;
use App\Models\KesehatanPemeriksaan;
use App\Models\Santri;
use App\Modules\KesehatanQu\Requests\StorePemeriksaanRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KesehatanQuPemeriksaanController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        $pemeriksaans = KesehatanPemeriksaan::query()
            ->visibleTo($currentUser)
            ->with(['santri', 'pencatat', 'rujukan'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('santri', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('tanggal_pemeriksaan', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('tanggal_pemeriksaan', '<=', $dateTo))
            ->orderBy('tanggal_pemeriksaan', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('modules.kesehatan-qu.pemeriksaan.index', [
            'pemeriksaans' => $pemeriksaans,
            'filters' => [
                'q' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $currentUser = $request->user();

        $santriOptions = Santri::query()
            ->visibleTo($currentUser)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nis']);

        $obatOptions = KesehatanObat::query()
            ->visibleTo($currentUser)
            ->where('stok', '>', 0)
            ->orderBy('nama_obat')
            ->get();

        return view('modules.kesehatan-qu.pemeriksaan.create', [
            'santriOptions' => $santriOptions,
            'obatOptions' => $obatOptions,
        ]);
    }

    public function store(StorePemeriksaanRequest $request): RedirectResponse
    {
        $currentUser = $request->user();
        $validated = $request->validated();

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->findOrFail($validated['santri_id']);

        $pemeriksaan = KesehatanPemeriksaan::query()->create([
            'tenant_id' => $currentUser->tenant_id,
            'santri_id' => $santri->id,
            'tanggal_pemeriksaan' => $validated['tanggal_pemeriksaan'],
            'keluhan' => $validated['keluhan'],
            'diagnosis' => $validated['diagnosis'] ?? null,
            'tindakan' => $validated['tindakan'] ?? null,
            'catatan' => $validated['catatan'] ?? null,
            'dicatat_oleh' => $currentUser->id,
        ]);

        if (! empty($validated['rujuk'])) {
            $pemeriksaan->rujukan()->create([
                'tenant_id' => $currentUser->tenant_id,
                'tempat_rujukan' => $validated['tempat_rujukan'],
                'diagnosis_dokter' => $validated['diagnosis_dokter'] ?? null,
                'tanggal_rujuk' => $validated['tanggal_rujuk'],
                'tanggal_kembali' => $validated['tanggal_kembali'] ?? null,
                'catatan' => $validated['catatan_rujukan'] ?? null,
            ]);
        }

        if (! empty($validated['obat_ids'])) {
            foreach ($validated['obat_ids'] as $i => $obatId) {
                if (empty($obatId)) {
                    continue;
                }

                $obat = KesehatanObat::query()
                    ->visibleTo($currentUser)
                    ->findOrFail($obatId);

                $jumlah = (int) ($validated['obat_jumlahs'][$i] ?? 1);
                $jumlah = min($jumlah, $obat->stok);

                $pemeriksaan->pemakaianObat()->create([
                    'tenant_id' => $currentUser->tenant_id,
                    'obat_id' => $obat->id,
                    'jumlah' => $jumlah,
                    'catatan' => $validated['obat_catatans'][$i] ?? null,
                ]);

                $obat->decrement('stok', $jumlah);
            }
        }

        $this->activityLogger->log(
            action: 'pemeriksaan_created',
            actor: $currentUser,
            target: $pemeriksaan,
            description: "Pemeriksaan kesehatan untuk {$santri->full_name} dengan keluhan {$pemeriksaan->keluhan}.",
            properties: [
                'santri_id' => $santri->id,
                'santri_name' => $santri->full_name,
                'tanggal' => $pemeriksaan->tanggal_pemeriksaan->toDateString(),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('kesehatan.pemeriksaan.index')
            ->with('success', "Pemeriksaan untuk {$santri->full_name} berhasil dicatat.");
    }

    public function show(Request $request, KesehatanPemeriksaan $kesehatanPemeriksaan): View
    {
        $currentUser = $request->user();

        $pemeriksaan = KesehatanPemeriksaan::query()
            ->visibleTo($currentUser)
            ->with(['santri', 'pencatat', 'rujukan', 'pemakaianObat.obat'])
            ->findOrFail($kesehatanPemeriksaan->id);

        return view('modules.kesehatan-qu.pemeriksaan.show', [
            'pemeriksaan' => $pemeriksaan,
        ]);
    }

    public function destroy(Request $request, KesehatanPemeriksaan $kesehatanPemeriksaan): RedirectResponse
    {
        $currentUser = $request->user();

        $pemeriksaan = KesehatanPemeriksaan::query()
            ->visibleTo($currentUser)
            ->with('santri')
            ->findOrFail($kesehatanPemeriksaan->id);

        $santriName = $pemeriksaan->santri?->full_name ?? "Santri #{$pemeriksaan->santri_id}";

        $this->activityLogger->log(
            action: 'pemeriksaan_deleted',
            actor: $currentUser,
            target: $pemeriksaan,
            description: "Pemeriksaan kesehatan untuk {$santriName} pada {$pemeriksaan->tanggal_pemeriksaan->toDateString()} dihapus.",
            properties: [
                'santri_id' => $pemeriksaan->santri_id,
                'santri_name' => $santriName,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $pemeriksaan->delete();

        return redirect()
            ->route('kesehatan.pemeriksaan.index')
            ->with('success', "Pemeriksaan untuk {$santriName} berhasil dihapus.");
    }
}
