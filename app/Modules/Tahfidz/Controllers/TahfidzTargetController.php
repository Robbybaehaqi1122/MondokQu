<?php

namespace App\Modules\Tahfidz\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\TahfidzTarget;
use App\Modules\Tahfidz\Requests\StoreTahfidzTargetRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TahfidzTargetController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));
        $selectedSantriId = trim((string) $request->string('santri'));
        $type = trim((string) $request->string('type'));

        $targets = TahfidzTarget::query()
            ->visibleTo($currentUser)
            ->with(['santri.room', 'creator'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('santri', fn ($q) => $q
                    ->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%"));
            })
            ->when($selectedSantriId !== '', fn ($query) => $query->where('santri_id', $selectedSantriId))
            ->when($type !== '', fn ($query) => $query->where('target_type', $type))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $santriOptions = Santri::query()
            ->visibleTo($currentUser)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nis']);

        return view('modules.tahfidz.targets.index', [
            'targets' => $targets,
            'santriOptions' => $santriOptions,
            'filters' => [
                'q' => $search,
                'santri' => $selectedSantriId,
                'type' => $type,
            ],
            'typeOptions' => TahfidzTarget::availableTypes(),
        ]);
    }

    public function create(Request $request): View
    {
        $currentUser = $request->user();
        $preselectedSantriId = $request->integer('santri_id') ?: null;

        $santriOptions = Santri::query()
            ->visibleTo($currentUser)
            ->where('status', Santri::STATUS_ACTIVE)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nis']);

        return view('modules.tahfidz.targets.create', [
            'santriOptions' => $santriOptions,
            'preselectedSantriId' => $preselectedSantriId,
            'typeOptions' => TahfidzTarget::availableTypes(),
        ]);
    }

    public function store(StoreTahfidzTargetRequest $request): RedirectResponse
    {
        $currentUser = $request->user();
        $validated = $request->validated();

        $santri = Santri::findOrFail($validated['santri_id']);

        $target = TahfidzTarget::query()->create([
            'tenant_id' => $santri->tenant_id,
            'santri_id' => $santri->id,
            'target_type' => $validated['target_type'],
            'target_value' => $validated['target_value'],
            'target_date' => $validated['target_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $currentUser->id,
        ]);

        $santriName = $target->santri?->full_name ?? "Santri #{$target->santri_id}";

        $this->activityLogger->log(
            action: 'tahfidz_target_created',
            actor: $currentUser,
            target: $target,
            description: "Target hafalan {$target->typeLabel()} {$target->target_value} untuk {$santriName} ditetapkan.",
            properties: [
                'santri_id' => $target->santri_id,
                'santri_name' => $santriName,
                'target_type' => $target->target_type,
                'target_value' => $target->target_value,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('tahfidz.targets.index')
            ->with('success', "Target hafalan untuk {$santriName} berhasil ditetapkan.");
    }

    public function edit(Request $request, TahfidzTarget $tahfidzTarget): View
    {
        $currentUser = $request->user();

        $target = TahfidzTarget::query()
            ->visibleTo($currentUser)
            ->with('santri')
            ->findOrFail($tahfidzTarget->id);

        $santriOptions = Santri::query()
            ->visibleTo($currentUser)
            ->where('status', Santri::STATUS_ACTIVE)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nis']);

        return view('modules.tahfidz.targets.edit', [
            'target' => $target,
            'santriOptions' => $santriOptions,
            'typeOptions' => TahfidzTarget::availableTypes(),
        ]);
    }

    public function update(StoreTahfidzTargetRequest $request, TahfidzTarget $tahfidzTarget): RedirectResponse
    {
        $currentUser = $request->user();

        $target = TahfidzTarget::query()
            ->visibleTo($currentUser)
            ->findOrFail($tahfidzTarget->id);

        $validated = $request->validated();

        $target->update([
            'santri_id' => $validated['santri_id'],
            'target_type' => $validated['target_type'],
            'target_value' => $validated['target_value'],
            'target_date' => $validated['target_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $santriName = $target->santri?->full_name ?? "Santri #{$target->santri_id}";

        $this->activityLogger->log(
            action: 'tahfidz_target_updated',
            actor: $currentUser,
            target: $target,
            description: "Target hafalan {$target->typeLabel()} {$target->target_value} untuk {$santriName} diperbarui.",
            properties: [
                'santri_id' => $target->santri_id,
                'santri_name' => $santriName,
                'target_type' => $target->target_type,
                'target_value' => $target->target_value,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('tahfidz.targets.index')
            ->with('success', "Target hafalan untuk {$santriName} berhasil diperbarui.");
    }

    public function destroy(Request $request, TahfidzTarget $tahfidzTarget): RedirectResponse
    {
        $currentUser = $request->user();

        $target = TahfidzTarget::query()
            ->visibleTo($currentUser)
            ->findOrFail($tahfidzTarget->id);

        $santriName = $target->santri?->full_name ?? "Santri #{$target->santri_id}";

        $this->activityLogger->log(
            action: 'tahfidz_target_deleted',
            actor: $currentUser,
            target: $target,
            description: "Target hafalan untuk {$santriName} dihapus.",
            properties: [
                'santri_id' => $target->santri_id,
                'santri_name' => $santriName,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $target->delete();

        return redirect()
            ->route('tahfidz.targets.index')
            ->with('success', "Target hafalan untuk {$santriName} berhasil dihapus.");
    }
}
