<?php

namespace App\Modules\KesehatanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KesehatanObat;
use App\Modules\KesehatanQu\Requests\StoreObatRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KesehatanQuObatController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));

        $obats = KesehatanObat::query()
            ->visibleTo($currentUser)
            ->when($search !== '', function ($query) use ($search) {
                $query->where('nama_obat', 'like', "%{$search}%");
            })
            ->orderBy('nama_obat')
            ->paginate(15)
            ->withQueryString();

        return view('modules.kesehatan-qu.obat.index', [
            'obats' => $obats,
            'filters' => ['q' => $search],
        ]);
    }

    public function store(StoreObatRequest $request): RedirectResponse
    {
        $currentUser = $request->user();
        $validated = $request->validated();

        $obat = KesehatanObat::query()->create([
            'tenant_id' => $currentUser->tenant_id,
            'nama_obat' => $validated['nama_obat'],
            'jenis' => $validated['jenis'] ?? null,
            'stok' => $validated['stok'] ?? 0,
            'satuan' => $validated['satuan'] ?? 'pcs',
            'expired_date' => $validated['expired_date'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        $this->activityLogger->log(
            action: 'obat_created',
            actor: $currentUser,
            target: $obat,
            description: "Obat {$obat->nama_obat} ditambahkan ke stok UKS.",
            properties: ['obat_id' => $obat->id, 'nama_obat' => $obat->nama_obat, 'stok' => $obat->stok],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('kesehatan.obat.index')
            ->with('success', "Obat {$obat->nama_obat} berhasil ditambahkan.");
    }

    public function update(StoreObatRequest $request, KesehatanObat $kesehatanObat): RedirectResponse
    {
        $currentUser = $request->user();
        $validated = $request->validated();

        $obat = KesehatanObat::query()
            ->visibleTo($currentUser)
            ->findOrFail($kesehatanObat->id);

        $obat->update([
            'nama_obat' => $validated['nama_obat'],
            'jenis' => $validated['jenis'] ?? null,
            'stok' => $validated['stok'] ?? 0,
            'satuan' => $validated['satuan'] ?? 'pcs',
            'expired_date' => $validated['expired_date'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        $this->activityLogger->log(
            action: 'obat_updated',
            actor: $currentUser,
            target: $obat,
            description: "Obat {$obat->nama_obat} diperbarui.",
            properties: ['obat_id' => $obat->id, 'nama_obat' => $obat->nama_obat, 'stok' => $obat->stok],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('kesehatan.obat.index')
            ->with('success', "Obat {$obat->nama_obat} berhasil diperbarui.");
    }

    public function destroy(Request $request, KesehatanObat $kesehatanObat): RedirectResponse
    {
        $currentUser = $request->user();

        $obat = KesehatanObat::query()
            ->visibleTo($currentUser)
            ->findOrFail($kesehatanObat->id);

        $this->activityLogger->log(
            action: 'obat_deleted',
            actor: $currentUser,
            target: $obat,
            description: "Obat {$obat->nama_obat} dihapus dari stok UKS.",
            properties: ['obat_id' => $obat->id, 'nama_obat' => $obat->nama_obat],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $obat->delete();

        return redirect()
            ->route('kesehatan.obat.index')
            ->with('success', "Obat {$obat->nama_obat} berhasil dihapus.");
    }
}
