<?php

namespace App\Modules\Santri\Controllers;

use App\Enums\ExportFormat;
use App\Exports\SantriCsvExport;
use App\Exports\SantriExcelExport;
use App\Exports\SantriPdfExport;
use App\Modules\Santri\Requests\StoreSantriRequest;
use App\Modules\Santri\Requests\UpdateSantriRequest;
use App\Models\DataExport;
use App\Models\Room;
use App\Models\Santri;
use App\Models\User;
use App\Services\DataExportManager;
use App\Services\FormatDispatcher;
use App\Services\SantriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SantriManagementController extends \App\Http\Controllers\Controller
{
    public function __construct(
        protected SantriService $santriService,
        protected DataExportManager $dataExportManager,
        protected SantriCsvExport $santriCsvExport,
        protected FormatDispatcher $formatDispatcher,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Santri::class);

        $currentUser = $request->user();
        $query = trim((string) $request->string('q'));
        $selectedStatus = trim((string) $request->string('status'));
        $selectedGender = trim((string) $request->string('gender'));

        $baseQuery = Santri::query()->visibleTo($currentUser);

        $santris = (clone $baseQuery)
            ->with(['creator', 'guardians', 'room'])
            ->withFilters($query, $selectedStatus, $selectedGender)
            ->orderBy('full_name')
            ->paginate(10)
            ->withQueryString();
        $tenantIds = $santris->getCollection()
            ->pluck('tenant_id')
            ->push($currentUser?->tenant_id)
            ->filter()
            ->unique()
            ->values();

        return view('santri.index', [
            'allSantriCount' => (clone $baseQuery)->count(),
            'filters' => [
                'q' => $query,
                'status' => $selectedStatus,
                'gender' => $selectedGender,
            ],
            'genders' => $this->genderOptions(),
            'canCreateSantri' => $currentUser?->can('create', Santri::class) ?? false,
            'guardianUserOptionsByTenant' => $this->guardianUserOptionsByTenant($currentUser, $tenantIds),
            'roomOptionsByTenant' => $this->roomOptionsByTenant($currentUser, $tenantIds),
            'dataExports' => DataExport::query()
                ->visibleTo($currentUser)
                ->forType(DataExport::TYPE_SANTRI)
                ->latest()
                ->limit(5)
                ->get(),
            'statuses' => $this->statusOptions(),
            'santris' => $santris,
        ]);
    }

    public function export(Request $request): RedirectResponse|StreamedResponse|\Illuminate\Http\Response
    {
        $this->authorize('viewAny', Santri::class);

        $currentUser = $request->user();
        $query = trim((string) $request->string('q'));
        $selectedStatus = trim((string) $request->string('status'));
        $selectedGender = trim((string) $request->string('gender'));
        $format = ExportFormat::tryFrom($request->string('format', 'csv')) ?? ExportFormat::CSV;

        if ($format === ExportFormat::CSV) {
            $rowCount = $this->santriCsvExport->rowCount($currentUser, $query, $selectedStatus, $selectedGender);
        } else {
            $rowCount = (new SantriExcelExport($currentUser, $query, $selectedStatus, $selectedGender))->query()->count();
        }

        if ($this->dataExportManager->shouldQueue($rowCount)) {
            $filename = match ($format) {
                ExportFormat::CSV => $this->santriCsvExport->filename(),
                ExportFormat::XLSX => (new SantriExcelExport)->filename(),
                ExportFormat::PDF => (new SantriPdfExport($currentUser, $query, $selectedStatus, $selectedGender))->filename(),
            };

            $this->dataExportManager->queue(
                $currentUser,
                DataExport::TYPE_SANTRI,
                'Export Data Santri',
                $filename,
                [
                    'q' => $query,
                    'status' => $selectedStatus,
                    'gender' => $selectedGender,
                ],
                $rowCount,
                $format->value
            );

            return redirect()
                ->route('santri.index', array_filter([
                    'q' => $query,
                    'status' => $selectedStatus,
                    'gender' => $selectedGender,
                ], fn ($value) => $value !== ''))
                ->with('success', 'Export data santri sedang diproses di background. Link download akan muncul di daftar export terbaru setelah selesai.');
        }

        return $this->formatDispatcher->downloadSantri($currentUser, $format, $query, $selectedStatus, $selectedGender);
    }

    public function show(Santri $santri): View
    {
        $this->authorize('view', $santri);

        $santri->load(['creator', 'guardians', 'room']);

        return view('santri.show', [
            'canDeleteSantri' => request()->user()?->can('delete', $santri) ?? false,
            'santri' => $santri,
        ]);
    }

    public function store(StoreSantriRequest $request): RedirectResponse
    {
        $this->authorize('create', Santri::class);

        $this->santriService->create(
            tenantId: (int) $request->user()->tenant_id,
            validated: $request->validated(),
            photo: $request->file('photo'),
            guardianUserIds: $request->guardianUserIds(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()
            ->route('santri.index')
            ->with('success', 'Data santri berhasil ditambahkan.');
    }

    public function update(UpdateSantriRequest $request, Santri $santri): RedirectResponse
    {
        $this->authorize('update', $santri);

        $this->santriService->update(
            santri: $santri,
            validated: $request->validated(),
            photo: $request->file('photo'),
            deletePhoto: $request->boolean('delete_photo'),
            guardianUserIds: $request->guardianUserIds(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()
            ->route('santri.index')
            ->with('success', 'Data santri berhasil diperbarui.');
    }

    public function destroy(Request $request, Santri $santri): RedirectResponse
    {
        $this->authorize('delete', $santri);

        $this->santriService->delete(
            santri: $santri,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()
            ->route('santri.index')
            ->with('success', 'Data santri berhasil dihapus.');
    }

    protected function statusOptions(): Collection
    {
        return collect(Santri::availableStatuses())
            ->map(fn (string $status): array => [
                'value' => $status,
                'label' => match ($status) {
                    Santri::STATUS_ACTIVE => 'Aktif',
                    Santri::STATUS_LEAVE => 'Cuti',
                    Santri::STATUS_EXITED => 'Keluar',
                    Santri::STATUS_ALUMNI => 'Alumni',
                    default => ucfirst($status),
                },
            ]);
    }

    protected function genderOptions(): Collection
    {
        return collect(Santri::availableGenders())
            ->map(fn (string $gender): array => [
                'value' => $gender,
                'label' => match ($gender) {
                    Santri::GENDER_MALE => 'Laki-laki',
                    Santri::GENDER_FEMALE => 'Perempuan',
                    default => ucfirst($gender),
                },
            ]);
    }

    protected function guardianUserOptionsByTenant(?User $currentUser, Collection $tenantIds): Collection
    {
        if (! $currentUser || $tenantIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->visibleTo($currentUser)
            ->whereIn('tenant_id', $tenantIds)
            ->whereHas('roles', fn ($query) => $query->where('name', 'Wali Santri'))
            ->orderBy('name')
            ->get(['id', 'tenant_id', 'name', 'username', 'email'])
            ->groupBy('tenant_id');
    }

    protected function roomOptionsByTenant(?User $currentUser, Collection $tenantIds): Collection
    {
        if (! $currentUser || $tenantIds->isEmpty()) {
            return collect();
        }

        return Room::query()
            ->withoutTenantScope()
            ->when(! $currentUser->isSuperAdmin(), fn ($query) => $query->where('tenant_id', $currentUser->tenant_id))
            ->whereIn('tenant_id', $tenantIds)
            ->orderBy('name')
            ->get(['id', 'tenant_id', 'name', 'status'])
            ->groupBy('tenant_id');
    }
}
