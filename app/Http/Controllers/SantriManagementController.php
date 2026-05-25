<?php

namespace App\Http\Controllers;

use App\Exports\SantriCsvExport;
use App\Http\Requests\Santri\StoreSantriRequest;
use App\Http\Requests\Santri\UpdateSantriRequest;
use App\Models\DataExport;
use App\Models\Room;
use App\Models\Santri;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\DataExportManager;
use App\Services\SantriPhotoUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SantriManagementController extends Controller
{
    protected SantriCsvExport $santriCsvExport;

    protected DataExportManager $dataExportManager;

    public function __construct(
        protected ActivityLogger $activityLogger,
        protected SantriPhotoUploader $santriPhotoUploader,
        ?DataExportManager $dataExportManager = null,
        ?SantriCsvExport $santriCsvExport = null
    ) {
        $this->dataExportManager = $dataExportManager ?? new DataExportManager;
        $this->santriCsvExport = $santriCsvExport ?? new SantriCsvExport;
    }

    /**
     * Display the santri management panel.
     */
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
        $guardianUserOptionsByTenant = $this->guardianUserOptionsByTenant($currentUser, $tenantIds);
        $roomOptionsByTenant = $this->roomOptionsByTenant($currentUser, $tenantIds);

        return view('santri.index', [
            'allSantriCount' => (clone $baseQuery)->count(),
            'filters' => [
                'q' => $query,
                'status' => $selectedStatus,
                'gender' => $selectedGender,
            ],
            'genders' => $this->genderOptions(),
            'canCreateSantri' => $currentUser?->can('create', Santri::class) ?? false,
            'guardianUserOptionsByTenant' => $guardianUserOptionsByTenant,
            'roomOptionsByTenant' => $roomOptionsByTenant,
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

    /**
     * Export filtered santri data as CSV.
     */
    public function export(Request $request): RedirectResponse|StreamedResponse
    {
        $this->authorize('viewAny', Santri::class);

        $currentUser = $request->user();
        $query = trim((string) $request->string('q'));
        $selectedStatus = trim((string) $request->string('status'));
        $selectedGender = trim((string) $request->string('gender'));
        $rowCount = $this->santriCsvExport->rowCount($currentUser, $query, $selectedStatus, $selectedGender);

        if ($this->dataExportManager->shouldQueue($rowCount)) {
            $this->dataExportManager->queue(
                $currentUser,
                DataExport::TYPE_SANTRI,
                'Export Data Santri',
                $this->santriCsvExport->filename(),
                [
                    'q' => $query,
                    'status' => $selectedStatus,
                    'gender' => $selectedGender,
                ],
                $rowCount
            );

            return redirect()
                ->route('santri.index', array_filter([
                    'q' => $query,
                    'status' => $selectedStatus,
                    'gender' => $selectedGender,
                ], fn ($value) => $value !== ''))
                ->with('success', 'Export data santri sedang diproses di background. Link download akan muncul di daftar export terbaru setelah selesai.');
        }

        return $this->santriCsvExport->download($currentUser, $query, $selectedStatus, $selectedGender);
    }

    /**
     * Display the detail page for a santri.
     */
    public function show(Santri $santri): View
    {
        $this->authorize('view', $santri);

        $santri->load(['creator', 'guardians', 'room']);

        return view('santri.show', [
            'canDeleteSantri' => request()->user()?->can('delete', $santri) ?? false,
            'santri' => $santri,
        ]);
    }

    /**
     * Store a newly created santri.
     */
    public function store(StoreSantriRequest $request): RedirectResponse
    {
        $this->authorize('create', Santri::class);

        $validated = $request->validated();
        $tenantId = (int) $request->user()?->tenant_id;
        $guardianUserIds = $request->guardianUserIds();

        if (! $tenantId) {
            abort(403);
        }

        $photoPath = $this->santriPhotoUploader->store($request->file('photo'));

        try {
            $santri = DB::transaction(function () use ($tenantId, $validated, $photoPath, $request, $guardianUserIds): Santri {
                $room = $this->resolveRoomForSantri($tenantId, $validated);
                $santri = Santri::query()->create([
                    'tenant_id' => $tenantId,
                    'nis' => $validated['nis'],
                    'full_name' => $validated['full_name'],
                    'gender' => $validated['gender'],
                    'birth_place' => $validated['birth_place'],
                    'birth_date' => $validated['birth_date'],
                    'address' => $validated['address'],
                    'guardian_name' => $validated['guardian_name'] ?: null,
                    'father_name' => $validated['father_name'],
                    'mother_name' => $validated['mother_name'],
                    'guardian_phone_number' => $validated['guardian_phone_number'] ?: null,
                    'emergency_contact' => $validated['emergency_contact'],
                    'entry_date' => $validated['entry_date'],
                    'entry_year' => $validated['entry_year'],
                    'room_id' => $room->id,
                    'notes' => $validated['notes'] ?? null,
                    'status' => $validated['status'],
                    'photo_path' => $photoPath,
                    'created_by' => $request->user()?->id,
                ]);
                $this->syncGuardianUsers($santri, $guardianUserIds);

                return $santri->load('room');
            });
        } catch (Throwable $exception) {
            $this->santriPhotoUploader->deleteIfManaged($photoPath);

            throw $exception;
        }

        $this->activityLogger->log(
            action: 'santri_created',
            actor: $request->user(),
            target: $santri,
            description: 'Data santri baru ditambahkan.',
            properties: [
                'nis' => $santri->nis,
                'status' => $santri->status,
                'room_name' => $santri->displayRoomName(''),
                'entry_year' => $santri->entry_year,
                'guardian_user_ids' => $guardianUserIds->all(),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('santri.index')
            ->with('success', 'Data santri berhasil ditambahkan.');
    }

    /**
     * Update the selected santri.
     */
    public function update(UpdateSantriRequest $request, Santri $santri): RedirectResponse
    {
        $this->authorize('update', $santri);

        $validated = $request->validated();
        $guardianUserIds = $request->guardianUserIds();
        $santri->loadMissing('room');
        $previousValues = $santri->only([
            'nis',
            'full_name',
            'gender',
            'birth_place',
            'birth_date',
            'address',
            'guardian_name',
            'father_name',
            'mother_name',
            'guardian_phone_number',
            'emergency_contact',
            'entry_date',
            'entry_year',
            'room_id',
            'notes',
            'status',
            'photo_path',
        ]);
        $previousGuardianUserIds = $santri->guardians()
            ->pluck('users.id')
            ->map(fn ($userId) => (int) $userId)
            ->values()
            ->all();

        $previousPhotoPath = $santri->photo_path;
        $newPhotoPath = null;
        $photoPathToDeleteAfterCommit = null;

        if ($request->boolean('delete_photo') && ! $request->file('photo')) {
            $photoPath = null;
        } else {
            $newPhotoPath = $request->file('photo')
                ? $this->santriPhotoUploader->store($request->file('photo'))
                : null;
            $photoPath = $newPhotoPath ?? $previousPhotoPath;
        }

        if ($previousPhotoPath && $previousPhotoPath !== $photoPath) {
            $photoPathToDeleteAfterCommit = $previousPhotoPath;
        }

        try {
            DB::transaction(function () use ($santri, $validated, $photoPath, $guardianUserIds): void {
                $room = $this->resolveRoomForSantri((int) $santri->tenant_id, $validated);

                $santri->update([
                    'nis' => $validated['nis'],
                    'full_name' => $validated['full_name'],
                    'gender' => $validated['gender'],
                    'birth_place' => $validated['birth_place'],
                    'birth_date' => $validated['birth_date'],
                    'address' => $validated['address'],
                    'guardian_name' => $validated['guardian_name'] ?: null,
                    'father_name' => $validated['father_name'],
                    'mother_name' => $validated['mother_name'],
                    'guardian_phone_number' => $validated['guardian_phone_number'] ?: null,
                    'emergency_contact' => $validated['emergency_contact'],
                    'entry_date' => $validated['entry_date'],
                    'entry_year' => $validated['entry_year'],
                    'room_id' => $room->id,
                    'notes' => $validated['notes'] ?? null,
                    'status' => $validated['status'],
                    'photo_path' => $photoPath,
                ]);
                $this->syncGuardianUsers($santri, $guardianUserIds);
            });
        } catch (Throwable $exception) {
            $this->santriPhotoUploader->deleteIfManaged($newPhotoPath);

            throw $exception;
        }

        $santri->refresh()->load('room');

        $this->activityLogger->log(
            action: 'santri_updated',
            actor: $request->user(),
            target: $santri,
            description: 'Data santri diperbarui.',
            properties: [
                'before' => $previousValues,
                'after' => $santri->only([
                    'nis',
                    'full_name',
                    'gender',
                    'birth_place',
                    'birth_date',
                    'address',
                    'guardian_name',
                    'father_name',
                    'mother_name',
                    'guardian_phone_number',
                    'emergency_contact',
                    'entry_date',
                    'entry_year',
                    'room_id',
                    'room_name',
                    'notes',
                    'status',
                    'photo_path',
                ]),
                'guardian_user_ids' => [
                    'before' => $previousGuardianUserIds,
                    'after' => $guardianUserIds->all(),
                ],
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $this->santriPhotoUploader->deleteIfManaged($photoPathToDeleteAfterCommit);

        return redirect()
            ->route('santri.index')
            ->with('success', 'Data santri berhasil diperbarui.');
    }

    /**
     * Delete the selected santri.
     */
    public function destroy(Request $request, Santri $santri): RedirectResponse
    {
        $this->authorize('delete', $santri);

        $this->activityLogger->log(
            action: 'santri_deleted',
            actor: $request->user(),
            target: $santri,
            description: 'Data santri dihapus dari sistem.',
            properties: [
                'nis' => $santri->nis,
                'status' => $santri->status,
                'room_name' => $santri->displayRoomName(''),
                'entry_year' => $santri->entry_year,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $photoPath = $santri->photo_path;
        $santri->delete();
        $this->santriPhotoUploader->deleteIfManaged($photoPath);

        return redirect()
            ->route('santri.index')
            ->with('success', 'Data santri berhasil dihapus.');
    }

    /**
     * Build the santri status options for views.
     *
     * @return Collection<int, array{value: string, label: string}>
     */
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

    /**
     * Build the santri gender options for views.
     *
     * @return Collection<int, array{value: string, label: string}>
     */
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

    /**
     * Build selectable wali portal users grouped by tenant.
     */
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

    /**
     * Build selectable room master data grouped by tenant.
     */
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

    /**
     * Sync wali portal users to the selected santri.
     */
    protected function syncGuardianUsers(Santri $santri, Collection $guardianUserIds): void
    {
        $existingRelationships = $santri->guardianLinks()
            ->pluck('relationship', 'user_id');
        $existingPrimaryFlags = $santri->guardianLinks()
            ->pluck('is_primary', 'user_id');
        $syncPayload = $guardianUserIds
            ->mapWithKeys(fn (int $userId) => [
                $userId => [
                    'tenant_id' => $santri->tenant_id,
                    'relationship' => $existingRelationships->get($userId) ?: 'Wali',
                    'is_primary' => (bool) ($existingPrimaryFlags->get($userId) ?? false),
                ],
            ])
            ->all();

        $santri->guardians()->sync($syncPayload);
    }

    protected function resolveRoomForSantri(int $tenantId, array|string $roomInput): Room
    {
        if (is_array($roomInput) && filled($roomInput['room_id'] ?? null)) {
            return Room::query()
                ->withoutTenantScope()
                ->where('tenant_id', $tenantId)
                ->findOrFail((int) $roomInput['room_id']);
        }

        $roomName = is_array($roomInput)
            ? (string) ($roomInput['room_name'] ?? '')
            : $roomInput;

        $roomName = $this->normalizeRoomName($roomName);

        $room = Room::query()
            ->withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('name', trim($roomName))
            ->first();

        if ($room) {
            return $room;
        }

        if (! request()->user()?->can('manage kamar')) {
            throw ValidationException::withMessages([
                'room_name' => 'Kamar "' . $roomName . '" tidak ditemukan. Hubungi admin untuk membuat kamar baru.',
            ]);
        }

        return Room::query()
            ->withoutTenantScope()
            ->create([
                'tenant_id' => $tenantId,
                'name' => trim($roomName),
                'capacity' => null,
                'status' => Room::STATUS_ACTIVE,
                'description' => null,
                'created_by' => request()->user()?->id,
            ]);
    }

    protected function normalizeRoomName(string $roomName): string
    {
        return preg_replace('/\s+/', ' ', trim($roomName)) ?: '';
    }
}
