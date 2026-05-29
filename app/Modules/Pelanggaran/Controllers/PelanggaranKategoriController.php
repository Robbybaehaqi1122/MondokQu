<?php

namespace App\Modules\Pelanggaran\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PelanggaranKategori;
use App\Modules\Pelanggaran\Requests\StorePelanggaranKategoriRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelanggaranKategoriController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));

        $kategoris = PelanggaranKategori::query()
            ->visibleTo($currentUser)
            ->withCount('pelanggarans')
            ->when($search !== '', fn ($query) => $query->where('nama', 'like', "%{$search}%"))
            ->orderBy('poin')
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('modules.pelanggaran.kategori.index', [
            'kategoris' => $kategoris,
            'filters' => ['q' => $search],
        ]);
    }

    public function store(StorePelanggaranKategoriRequest $request): RedirectResponse
    {
        $currentUser = $request->user();

        if (! $currentUser?->tenant_id) {
            abort(403);
        }

        $validated = $request->validated();

        $kategori = PelanggaranKategori::query()->create([
            'tenant_id' => $currentUser->tenant_id,
            'nama' => $validated['nama'],
            'poin' => $validated['poin'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'created_by' => $currentUser->id,
        ]);

        $this->activityLogger->log(
            action: 'pelanggaran_kategori_created',
            actor: $currentUser,
            target: $kategori,
            description: "Kategori pelanggaran {$kategori->nama} dibuat.",
            properties: [
                'nama' => $kategori->nama,
                'poin' => $kategori->poin,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('pelanggaran.kategori.index')
            ->with('success', "Kategori pelanggaran {$kategori->nama} berhasil dibuat.");
    }

    public function update(StorePelanggaranKategoriRequest $request, PelanggaranKategori $pelanggaranKategori): RedirectResponse
    {
        $currentUser = $request->user();

        $kategori = PelanggaranKategori::query()
            ->visibleTo($currentUser)
            ->findOrFail($pelanggaranKategori->id);

        $previous = $kategori->only(['nama', 'poin', 'deskripsi']);
        $validated = $request->validated();

        $kategori->update([
            'nama' => $validated['nama'],
            'poin' => $validated['poin'],
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);

        $this->activityLogger->log(
            action: 'pelanggaran_kategori_updated',
            actor: $currentUser,
            target: $kategori,
            description: "Kategori pelanggaran {$kategori->nama} diperbarui.",
            properties: [
                'before' => $previous,
                'after' => $kategori->only(['nama', 'poin', 'deskripsi']),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('pelanggaran.kategori.index')
            ->with('success', "Kategori pelanggaran {$kategori->nama} berhasil diperbarui.");
    }

    public function destroy(Request $request, PelanggaranKategori $pelanggaranKategori): RedirectResponse
    {
        $currentUser = $request->user();
        $kategori = PelanggaranKategori::query()
            ->visibleTo($currentUser)
            ->withCount('pelanggarans')
            ->findOrFail($pelanggaranKategori->id);

        if ($kategori->pelanggarans_count > 0) {
            return redirect()
                ->route('pelanggaran.kategori.index')
                ->with('error', "Kategori {$kategori->nama} tidak dapat dihapus karena masih memiliki {$kategori->pelanggarans_count} catatan pelanggaran.");
        }

        $this->activityLogger->log(
            action: 'pelanggaran_kategori_deleted',
            actor: $currentUser,
            target: $kategori,
            description: "Kategori pelanggaran {$kategori->nama} dihapus.",
            properties: ['nama' => $kategori->nama, 'poin' => $kategori->poin],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $kategori->delete();

        return redirect()
            ->route('pelanggaran.kategori.index')
            ->with('success', "Kategori pelanggaran {$kategori->nama} berhasil dihapus.");
    }
}
