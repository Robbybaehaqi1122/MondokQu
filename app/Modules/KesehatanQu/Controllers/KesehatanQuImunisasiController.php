<?php

namespace App\Modules\KesehatanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KesehatanImunisasi;
use App\Models\Santri;
use App\Models\User;
use App\Modules\KesehatanQu\Requests\StoreImunisasiRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KesehatanQuImunisasiController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));
        $statusFilter = $request->string('status');

        $imunisasis = KesehatanImunisasi::query()
            ->visibleTo($currentUser)
            ->with(['santri', 'pemberi'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('santri', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                })->orWhere('jenis_imunisasi', 'like', "%{$search}%");
            })
            ->when($statusFilter->notEmpty(), fn ($query) => $query->where('status', $statusFilter))
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $santriOptions = Santri::query()
            ->visibleTo($currentUser)
            ->orderBy('full_name')
            ->get();

        $petugasOptions = User::query()
            ->where('tenant_id', $currentUser->tenant_id)
            ->orderBy('name')
            ->get();

        return view('modules.kesehatan-qu.imunisasi.index', [
            'imunisasis' => $imunisasis,
            'santriOptions' => $santriOptions,
            'petugasOptions' => $petugasOptions,
            'filters' => [
                'q' => $search,
                'status' => $statusFilter->value(),
            ],
        ]);
    }

    public function store(StoreImunisasiRequest $request): RedirectResponse
    {
        $currentUser = $request->user();
        $validated = $request->validated();

        $imunisasi = KesehatanImunisasi::query()->create([
            'tenant_id' => $currentUser->tenant_id,
            'santri_id' => $validated['santri_id'],
            'jenis_imunisasi' => $validated['jenis_imunisasi'],
            'tanggal' => $validated['tanggal'],
            'status' => $validated['status'],
            'catatan' => $validated['catatan'] ?? null,
            'diberikan_oleh' => $validated['diberikan_oleh'] ?? null,
        ]);

        $this->activityLogger->log(
            action: 'imunisasi_created',
            actor: $currentUser,
            target: $imunisasi,
            description: "Imunisasi {$imunisasi->jenis_imunisasi} untuk santri #{$imunisasi->santri_id} dicatat.",
            properties: ['imunisasi_id' => $imunisasi->id, 'jenis_imunisasi' => $imunisasi->jenis_imunisasi, 'status' => $imunisasi->status],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('kesehatan.imunisasi.index')
            ->with('success', "Imunisasi {$imunisasi->jenis_imunisasi} berhasil dicatat.");
    }

    public function update(StoreImunisasiRequest $request, KesehatanImunisasi $kesehatanImunisasi): RedirectResponse
    {
        $currentUser = $request->user();
        $validated = $request->validated();

        $imunisasi = KesehatanImunisasi::query()
            ->visibleTo($currentUser)
            ->findOrFail($kesehatanImunisasi->id);

        $imunisasi->update([
            'santri_id' => $validated['santri_id'],
            'jenis_imunisasi' => $validated['jenis_imunisasi'],
            'tanggal' => $validated['tanggal'],
            'status' => $validated['status'],
            'catatan' => $validated['catatan'] ?? null,
            'diberikan_oleh' => $validated['diberikan_oleh'] ?? null,
        ]);

        $this->activityLogger->log(
            action: 'imunisasi_updated',
            actor: $currentUser,
            target: $imunisasi,
            description: "Imunisasi {$imunisasi->jenis_imunisasi} diperbarui.",
            properties: ['imunisasi_id' => $imunisasi->id, 'jenis_imunisasi' => $imunisasi->jenis_imunisasi, 'status' => $imunisasi->status],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('kesehatan.imunisasi.index')
            ->with('success', "Imunisasi {$imunisasi->jenis_imunisasi} berhasil diperbarui.");
    }

    public function destroy(Request $request, KesehatanImunisasi $kesehatanImunisasi): RedirectResponse
    {
        $currentUser = $request->user();

        $imunisasi = KesehatanImunisasi::query()
            ->visibleTo($currentUser)
            ->findOrFail($kesehatanImunisasi->id);

        $this->activityLogger->log(
            action: 'imunisasi_deleted',
            actor: $currentUser,
            target: $imunisasi,
            description: "Imunisasi {$imunisasi->jenis_imunisasi} dihapus.",
            properties: ['imunisasi_id' => $imunisasi->id, 'jenis_imunisasi' => $imunisasi->jenis_imunisasi],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $imunisasi->delete();

        return redirect()
            ->route('kesehatan.imunisasi.index')
            ->with('success', "Imunisasi {$imunisasi->jenis_imunisasi} berhasil dihapus.");
    }
}
