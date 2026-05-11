<?php

namespace App\Http\Controllers;

use App\Http\Requests\Santri\StoreSantriRequest;
use App\Http\Requests\Santri\UpdateSantriRequest;
use App\Models\Santri;
use App\Services\ActivityLogger;
use App\Services\SantriPhotoUploader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SantriManagementController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger,
        protected SantriPhotoUploader $santriPhotoUploader
    ) {}

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
            ->with('creator')
            ->tap(fn (Builder $builder) => $this->applySantriFilters($builder, $query, $selectedStatus, $selectedGender))
            ->orderBy('full_name')
            ->paginate(10)
            ->withQueryString();

        return view('santri.index', [
            'allSantriCount' => (clone $baseQuery)->count(),
            'filters' => [
                'q' => $query,
                'status' => $selectedStatus,
                'gender' => $selectedGender,
            ],
            'genders' => $this->genderOptions(),
            'canCreateSantri' => $currentUser?->can('create', Santri::class) ?? false,
            'statuses' => $this->statusOptions(),
            'santris' => $santris,
        ]);
    }

    /**
     * Export filtered santri data as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Santri::class);

        $currentUser = $request->user();
        $query = trim((string) $request->string('q'));
        $selectedStatus = trim((string) $request->string('status'));
        $selectedGender = trim((string) $request->string('gender'));
        $filename = 'data-santri-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($currentUser, $query, $selectedStatus, $selectedGender): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'NIS',
                'Nama Lengkap',
                'Jenis Kelamin',
                'Status',
                'Tempat Lahir',
                'Tanggal Lahir',
                'Alamat',
                'Nama Ayah',
                'Nama Ibu',
                'Nama Wali',
                'Nomor HP Wali',
                'Kontak Darurat',
                'Tanggal Masuk',
                'Angkatan',
                'Kamar',
                'Catatan',
            ]);

            Santri::query()
                ->visibleTo($currentUser)
                ->tap(fn (Builder $builder) => $this->applySantriFilters($builder, $query, $selectedStatus, $selectedGender))
                ->orderBy('full_name')
                ->chunk(500, function (Collection $santris) use ($handle): void {
                    foreach ($santris as $santri) {
                        fputcsv($handle, [
                            $santri->nis,
                            $santri->full_name,
                            $santri->genderLabel(),
                            $santri->statusLabel(),
                            $santri->birth_place,
                            $santri->birth_date?->toDateString(),
                            $santri->address,
                            $santri->father_name,
                            $santri->mother_name,
                            $santri->guardian_name,
                            $santri->guardian_phone_number,
                            $santri->emergency_contact,
                            $santri->entry_date?->toDateString(),
                            $santri->entry_year,
                            $santri->room_name,
                            $santri->notes,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Display the detail page for a santri.
     */
    public function show(Santri $santri): View
    {
        $this->authorize('view', $santri);

        $santri->load('creator');

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

        $santri = Santri::query()->create([
            'tenant_id' => $request->user()?->tenant_id,
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
            'room_name' => $validated['room_name'],
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
            'photo_path' => $this->santriPhotoUploader->store($request->file('photo')),
            'created_by' => $request->user()?->id,
        ]);

        $this->activityLogger->log(
            action: 'santri_created',
            actor: $request->user(),
            target: $santri,
            description: 'Data santri baru ditambahkan.',
            properties: [
                'nis' => $santri->nis,
                'status' => $santri->status,
                'room_name' => $santri->room_name,
                'entry_year' => $santri->entry_year,
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
            'room_name',
            'notes',
            'status',
            'photo_path',
        ]);

        if ($request->boolean('delete_photo') && ! $request->file('photo')) {
            $this->santriPhotoUploader->deleteIfManaged($santri->photo_path);
            $photoPath = null;
        } else {
            $photoPath = $this->santriPhotoUploader->store($request->file('photo'), $santri->photo_path);
        }

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
            'room_name' => $validated['room_name'],
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
            'photo_path' => $photoPath,
        ]);

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
                    'room_name',
                    'notes',
                    'status',
                    'photo_path',
                ]),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

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
                'room_name' => $santri->room_name,
                'entry_year' => $santri->entry_year,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $this->santriPhotoUploader->deleteIfManaged($santri->photo_path);
        $santri->delete();

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

    protected function applySantriFilters(Builder $builder, string $query, string $selectedStatus, string $selectedGender): void
    {
        $builder
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($santriQuery) use ($query) {
                    $santriQuery
                        ->where('nis', 'like', "%{$query}%")
                        ->orWhere('full_name', 'like', "%{$query}%")
                        ->orWhere('guardian_name', 'like', "%{$query}%")
                        ->orWhere('guardian_phone_number', 'like', "%{$query}%")
                        ->orWhere('father_name', 'like', "%{$query}%")
                        ->orWhere('mother_name', 'like', "%{$query}%")
                        ->orWhere('room_name', 'like', "%{$query}%");
                });
            })
            ->when($selectedStatus !== '', fn ($builder) => $builder->where('status', $selectedStatus))
            ->when($selectedGender !== '', fn ($builder) => $builder->where('gender', $selectedGender));
    }
}
