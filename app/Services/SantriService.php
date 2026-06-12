<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class SantriService
{
    public function __construct(
        protected SantriPhotoUploader $santriPhotoUploader,
        protected ActivityLogger $activityLogger,
    ) {}

    public function create(
        int $tenantId,
        array $validated,
        ?UploadedFile $photo,
        Collection $guardianUserIds,
        User $actor,
        string $ipAddress,
        string $userAgent,
    ): Santri {
        $photoPath = $this->santriPhotoUploader->store($photo);

        try {
            $santri = DB::transaction(function () use ($tenantId, $validated, $photoPath, $actor, $guardianUserIds): Santri {
                $room = $this->resolveRoomForSantri($tenantId, $validated, $actor);
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
                    'father_phone' => $validated['father_phone'] ?: null,
                    'father_education' => $validated['father_education'] ?: null,
                    'father_job' => $validated['father_job'] ?: null,
                    'mother_name' => $validated['mother_name'],
                    'mother_phone' => $validated['mother_phone'] ?: null,
                    'mother_education' => $validated['mother_education'] ?: null,
                    'mother_job' => $validated['mother_job'] ?: null,
                    'guardian_phone_number' => $validated['guardian_phone_number'] ?: null,
                    'guardian_relation' => $validated['guardian_relation'] ?: null,
                    'guardian_address' => $validated['guardian_address'] ?: null,
                    'emergency_contact' => $validated['emergency_contact'],
                    'entry_date' => $validated['entry_date'],
                    'entry_year' => $validated['entry_year'],
                    'room_id' => $room->id,
                    'notes' => $validated['notes'] ?? null,
                    'status' => $validated['status'],
                    'photo_path' => $photoPath,
                    'created_by' => $actor->id,
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
            actor: $actor,
            target: $santri,
            description: 'Data santri baru ditambahkan.',
            properties: [
                'nis' => $santri->nis,
                'status' => $santri->status,
                'room_name' => $santri->displayRoomName(''),
                'entry_year' => $santri->entry_year,
                'guardian_user_ids' => $guardianUserIds->all(),
            ],
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );

        return $santri;
    }

    public function update(
        Santri $santri,
        array $validated,
        ?UploadedFile $photo,
        bool $deletePhoto,
        Collection $guardianUserIds,
        User $actor,
        string $ipAddress,
        string $userAgent,
    ): Santri {
        $santri->loadMissing('room');
        $previousValues = $santri->only([
            'nis', 'full_name', 'gender', 'birth_place', 'birth_date',
            'address', 'guardian_name', 'father_name', 'father_phone',
            'father_education', 'father_job', 'mother_name', 'mother_phone',
            'mother_education', 'mother_job', 'guardian_phone_number',
            'guardian_relation', 'guardian_address', 'emergency_contact',
            'entry_date', 'entry_year', 'room_id', 'notes', 'status', 'photo_path',
        ]);
        $previousGuardianUserIds = $santri->guardians()
            ->pluck('users.id')
            ->map(fn ($userId) => (int) $userId)
            ->values()
            ->all();

        $previousPhotoPath = $santri->photo_path;

        if ($deletePhoto && ! $photo) {
            $newPhotoPath = null;
        } else {
            $newPhotoPath = $photo
                ? $this->santriPhotoUploader->store($photo)
                : null;
        }

        $photoPath = ($newPhotoPath !== null || $deletePhoto)
            ? $newPhotoPath
            : $previousPhotoPath;

        $photoPathToDeleteAfterCommit = ($previousPhotoPath && $previousPhotoPath !== $photoPath)
            ? $previousPhotoPath
            : null;

        try {
            DB::transaction(function () use ($santri, $validated, $photoPath, $guardianUserIds, $actor): void {
                $room = $this->resolveRoomForSantri((int) $santri->tenant_id, $validated, $actor);

                $santri->update([
                    'nis' => $validated['nis'],
                    'full_name' => $validated['full_name'],
                    'gender' => $validated['gender'],
                    'birth_place' => $validated['birth_place'],
                    'birth_date' => $validated['birth_date'],
                    'address' => $validated['address'],
                    'guardian_name' => $validated['guardian_name'] ?: null,
                    'father_name' => $validated['father_name'],
                    'father_phone' => $validated['father_phone'] ?: null,
                    'father_education' => $validated['father_education'] ?: null,
                    'father_job' => $validated['father_job'] ?: null,
                    'mother_name' => $validated['mother_name'],
                    'mother_phone' => $validated['mother_phone'] ?: null,
                    'mother_education' => $validated['mother_education'] ?: null,
                    'mother_job' => $validated['mother_job'] ?: null,
                    'guardian_phone_number' => $validated['guardian_phone_number'] ?: null,
                    'guardian_relation' => $validated['guardian_relation'] ?: null,
                    'guardian_address' => $validated['guardian_address'] ?: null,
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
            $this->santriPhotoUploader->deleteIfManaged($photoPath);
            throw $exception;
        }

        $santri->refresh()->load('room');

        $afterValues = $santri->only([
            'nis', 'full_name', 'gender', 'birth_place', 'birth_date',
            'address', 'guardian_name', 'father_name', 'father_phone',
            'father_education', 'father_job', 'mother_name', 'mother_phone',
            'mother_education', 'mother_job', 'guardian_phone_number',
            'guardian_relation', 'guardian_address', 'emergency_contact',
            'entry_date', 'entry_year', 'room_id', 'room_name', 'notes', 'status', 'photo_path',
        ]);

        $this->activityLogger->log(
            action: 'santri_updated',
            actor: $actor,
            target: $santri,
            description: 'Data santri diperbarui.',
            properties: [
                'before' => $previousValues,
                'after' => $afterValues,
                'guardian_user_ids' => [
                    'before' => $previousGuardianUserIds,
                    'after' => $guardianUserIds->all(),
                ],
            ],
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );

        $this->santriPhotoUploader->deleteIfManaged($photoPathToDeleteAfterCommit);

        return $santri;
    }

    public function delete(
        Santri $santri,
        User $actor,
        string $ipAddress,
        string $userAgent,
    ): void {
        $this->activityLogger->log(
            action: 'santri_deleted',
            actor: $actor,
            target: $santri,
            description: 'Data santri dihapus dari sistem.',
            properties: [
                'nis' => $santri->nis,
                'status' => $santri->status,
                'room_name' => $santri->displayRoomName(''),
                'entry_year' => $santri->entry_year,
            ],
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );

        $photoPath = $santri->photo_path;
        $santri->delete();
        $this->santriPhotoUploader->deleteIfManaged($photoPath);
    }

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

    protected function resolveRoomForSantri(int $tenantId, array|string $roomInput, User $actor): Room
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

        if (! $actor->can('manage kamar')) {
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
                'created_by' => $actor->id,
            ]);
    }

    protected function normalizeRoomName(string $roomName): string
    {
        return preg_replace('/\s+/', ' ', trim($roomName)) ?: '';
    }
}
