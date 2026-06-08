<?php

namespace App\Modules\Pelanggaran\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pelanggaran;
use App\Models\PelanggaranKategori;
use App\Models\Santri;
use App\Modules\Pelanggaran\Requests\StorePelanggaranRequest;
use App\Notifications\Concerns\NotifiesGuardians;
use App\Notifications\NewPelanggaranNotification;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelanggaranController extends Controller
{
    use NotifiesGuardians;

    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));
        $selectedSantri = trim((string) $request->string('santri'));
        $selectedKategori = trim((string) $request->string('kategori'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        $pelanggarans = Pelanggaran::query()
            ->visibleTo($currentUser)
            ->with(['santri', 'kategori', 'pencatat'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('santri', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($selectedSantri !== '', fn ($query) => $query->where('santri_id', $selectedSantri))
            ->when($selectedKategori !== '', fn ($query) => $query->where('kategori_id', $selectedKategori))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('tanggal', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('tanggal', '<=', $dateTo))
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $santriOptions = Santri::query()
            ->visibleTo($currentUser)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nis']);

        $kategoriOptions = PelanggaranKategori::query()
            ->visibleTo($currentUser)
            ->orderBy('poin')
            ->orderBy('nama')
            ->get();

        return view('modules.pelanggaran.pelanggaran.index', [
            'pelanggarans' => $pelanggarans,
            'santriOptions' => $santriOptions,
            'kategoriOptions' => $kategoriOptions,
            'filters' => [
                'q' => $search,
                'santri' => $selectedSantri,
                'kategori' => $selectedKategori,
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

        $kategoriOptions = PelanggaranKategori::query()
            ->visibleTo($currentUser)
            ->orderBy('poin')
            ->orderBy('nama')
            ->get();

        return view('modules.pelanggaran.pelanggaran.create', [
            'santriOptions' => $santriOptions,
            'kategoriOptions' => $kategoriOptions,
        ]);
    }

    public function store(StorePelanggaranRequest $request): RedirectResponse
    {
        $this->authorize('create', Pelanggaran::class);

        $currentUser = $request->user();
        $validated = $request->validated();

        $kategori = PelanggaranKategori::query()
            ->visibleTo($currentUser)
            ->findOrFail($validated['kategori_id']);

        $pelanggaran = Pelanggaran::query()->create([
            'tenant_id' => $currentUser->tenant_id,
            'santri_id' => $validated['santri_id'],
            'kategori_id' => $kategori->id,
            'keterangan' => $validated['keterangan'] ?? null,
            'poin' => $kategori->poin,
            'dicatat_oleh' => $currentUser->id,
            'tanggal' => $validated['tanggal'],
        ]);

        $santri = $pelanggaran->santri;
        $santriName = $santri?->full_name ?? "Santri #{$pelanggaran->santri_id}";

        $this->notifyGuardians($santri, new NewPelanggaranNotification($pelanggaran));

        $this->activityLogger->log(
            action: 'pelanggaran_created',
            actor: $currentUser,
            target: $pelanggaran,
            description: "Pelanggaran {$kategori->nama} ({$kategori->poin} poin) untuk {$santriName}.",
            properties: [
                'santri_id' => $pelanggaran->santri_id,
                'santri_name' => $santriName,
                'kategori' => $kategori->nama,
                'poin' => $kategori->poin,
                'tanggal' => $pelanggaran->tanggal->toDateString(),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('pelanggaran.index')
            ->with('success', "Pelanggaran {$kategori->nama} untuk {$santriName} berhasil dicatat.");
    }

    public function destroy(Request $request, Pelanggaran $pelanggaran): RedirectResponse
    {
        $currentUser = $request->user();

        $item = Pelanggaran::query()
            ->visibleTo($currentUser)
            ->with(['santri', 'kategori'])
            ->findOrFail($pelanggaran->id);

        $santriName = $item->santri?->full_name ?? "Santri #{$item->santri_id}";

        $this->activityLogger->log(
            action: 'pelanggaran_deleted',
            actor: $currentUser,
            target: $item,
            description: "Pelanggaran {$item->kategori?->nama} untuk {$santriName} dihapus.",
            properties: [
                'santri_id' => $item->santri_id,
                'santri_name' => $santriName,
                'kategori' => $item->kategori?->nama,
                'poin' => $item->poin,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $item->delete();

        return redirect()
            ->route('pelanggaran.index')
            ->with('success', "Pelanggaran untuk {$santriName} berhasil dihapus.");
    }
}
