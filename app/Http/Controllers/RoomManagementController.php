<?php

namespace App\Http\Controllers;

use App\Http\Requests\Room\AssignRoomSantriRequest;
use App\Http\Requests\Room\StoreRoomRequest;
use App\Http\Requests\Room\UpdateRoomRequest;
use App\Models\Room;
use App\Models\RoomTransfer;
use App\Models\Santri;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RoomManagementController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Display the room management panel.
     */
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));
        $selectedStatus = trim((string) $request->string('status'));

        $baseQuery = Room::query()->visibleTo($currentUser);

        $filteredQuery = (clone $baseQuery)
            ->tap(fn (Builder $builder) => $this->applyRoomFilters($builder, $search, $selectedStatus));

        $rooms = (clone $filteredQuery)
            ->withCount([
                'santris',
                'santris as active_santris_count' => fn (Builder $query) => $query->where('status', Santri::STATUS_ACTIVE),
            ])
            ->with(['santris' => fn ($query) => $query->orderBy('full_name')->limit(8)])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('rooms.index', [
            'filters' => [
                'q' => $search,
                'status' => $selectedStatus,
            ],
            'rooms' => $rooms,
            'roomStats' => [
                'total' => (clone $baseQuery)->count(),
                'active' => (clone $baseQuery)->where('status', Room::STATUS_ACTIVE)->count(),
                'inactive' => (clone $baseQuery)->where('status', Room::STATUS_INACTIVE)->count(),
                'capacity' => (clone $baseQuery)->sum('capacity'),
            ],
            'statusOptions' => $this->statusOptions(),
            'recentRoomTransfers' => RoomTransfer::query()
                ->visibleTo($currentUser)
                ->with(['santri', 'fromRoom', 'toRoom', 'mover'])
                ->latest('moved_at')
                ->limit(10)
                ->get(),
            'assignableSantris' => Santri::query()
                ->visibleTo($currentUser)
                ->with('room')
                ->where('status', Santri::STATUS_ACTIVE)
                ->orderBy('full_name')
                ->limit(500)
                ->get(),
        ]);
    }

    /**
     * Store a newly created room.
     */
    public function store(StoreRoomRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $currentUser = $request->user();

        if (! $currentUser?->tenant_id) {
            abort(403);
        }

        $room = Room::query()->create([
            'tenant_id' => $currentUser->tenant_id,
            'name' => $validated['name'],
            'capacity' => $validated['capacity'] ?? null,
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
            'created_by' => $currentUser->id,
        ]);

        $this->activityLogger->log(
            action: 'room_created',
            actor: $currentUser,
            target: $room,
            description: 'Master kamar baru dibuat.',
            properties: [
                'target_name' => $room->name,
                'capacity' => $room->capacity,
                'status' => $room->status,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Kamar berhasil dibuat.');
    }

    /**
     * Update the selected room.
     */
    public function update(UpdateRoomRequest $request, Room $room): RedirectResponse
    {
        $validated = $request->validated();
        $currentUser = $request->user();
        [$room, $previousValues, $afterValues] = DB::transaction(function () use ($currentUser, $room, $validated): array {
            $room = Room::query()
                ->visibleTo($currentUser)
                ->lockForUpdate()
                ->withCount(['santris as active_santris_count' => fn (Builder $query) => $query->where('status', Santri::STATUS_ACTIVE)])
                ->findOrFail($room->id);

            if (isset($validated['capacity']) && $validated['capacity'] !== null && (int) $validated['capacity'] < (int) $room->active_santris_count) {
                $exception = ValidationException::withMessages([
                    'capacity' => 'Kapasitas tidak boleh lebih kecil dari jumlah santri aktif di kamar ini.',
                ]);
                $exception->errorBag = 'updateRoom';

                throw $exception;
            }

            $previousValues = $room->only(['name', 'capacity', 'status', 'description']);

            $room->update([
                'name' => $validated['name'],
                'capacity' => $validated['capacity'] ?? null,
                'status' => $validated['status'],
                'description' => $validated['description'] ?? null,
            ]);

            $room->refresh();

            return [$room, $previousValues, $room->only(['name', 'capacity', 'status', 'description'])];
        });

        $this->activityLogger->log(
            action: 'room_updated',
            actor: $currentUser,
            target: $room,
            description: 'Master kamar diperbarui.',
            properties: [
                'target_name' => $room->name,
                'before' => $previousValues,
                'after' => $afterValues,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Kamar berhasil diperbarui.');
    }

    /**
     * Assign active santri to a room.
     */
    public function assignSantris(AssignRoomSantriRequest $request, Room $room): RedirectResponse
    {
        $validated = $request->validated();
        $currentUser = $request->user();

        DB::transaction(function () use ($currentUser, $room, $validated): void {
            $room = Room::query()
                ->visibleTo($currentUser)
                ->lockForUpdate()
                ->findOrFail($room->id);

            $santris = Santri::query()
                ->visibleTo($currentUser)
                ->with('room')
                ->where('tenant_id', $room->tenant_id)
                ->where('status', Santri::STATUS_ACTIVE)
                ->whereIn('id', $validated['santri_ids'])
                ->lockForUpdate()
                ->get();

            if ($santris->count() !== count($validated['santri_ids'])) {
                $exception = ValidationException::withMessages([
                    'santri_ids' => 'Pilih santri aktif dari tenant pondok yang sama.',
                ]);
                $exception->errorBag = 'assignRoomSantri';

                throw $exception;
            }

            $incomingCount = $santris
                ->reject(fn (Santri $santri) => (int) $santri->room_id === (int) $room->id)
                ->count();
            $currentOccupancy = $room->santris()
                ->where('status', Santri::STATUS_ACTIVE)
                ->lockForUpdate()
                ->count();

            if ($room->capacity && $currentOccupancy + $incomingCount > $room->capacity) {
                $exception = ValidationException::withMessages([
                    'santri_ids' => 'Kapasitas kamar tidak mencukupi untuk santri yang dipilih.',
                ]);
                $exception->errorBag = 'assignRoomSantri';

                throw $exception;
            }

            $changedSantris = $santris
                ->filter(fn (Santri $santri) => (int) $santri->room_id !== (int) $room->id)
                ->values();
            $movedAt = now();

            foreach ($changedSantris as $santri) {
                RoomTransfer::query()->create([
                    'tenant_id' => $room->tenant_id,
                    'santri_id' => $santri->id,
                    'from_room_id' => $santri->room_id,
                    'from_room_name' => $santri->displayRoomName('') ?: null,
                    'to_room_id' => $room->id,
                    'to_room_name' => $room->name,
                    'moved_by' => $currentUser?->id,
                    'moved_at' => $movedAt,
                ]);
            }

            Santri::query()
                ->visibleTo($currentUser)
                ->whereIn('id', $changedSantris->pluck('id'))
                ->update([
                    'room_id' => $room->id,
                ]);
        });

        $this->activityLogger->log(
            action: 'room_santris_assigned',
            actor: $currentUser,
            target: $room,
            description: 'Santri ditempatkan ke kamar.',
            properties: [
                'target_name' => $room->name,
                'santri_ids' => collect($validated['santri_ids'])->map(fn ($id) => (int) $id)->values()->all(),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Santri berhasil ditempatkan ke kamar.');
    }

    /**
     * Release a santri from the selected room.
     */
    public function releaseSantri(Request $request, Room $room, Santri $santri): RedirectResponse
    {
        $currentUser = $request->user();

        $releaseContext = DB::transaction(function () use ($currentUser, $room, $santri): ?array {
            $room = Room::query()
                ->visibleTo($currentUser)
                ->lockForUpdate()
                ->findOrFail($room->id);

            $santri = Santri::query()
                ->visibleTo($currentUser)
                ->with('room')
                ->where('tenant_id', $room->tenant_id)
                ->lockForUpdate()
                ->findOrFail($santri->id);

            if ((int) $santri->room_id !== (int) $room->id) {
                return null;
            }

            RoomTransfer::query()->create([
                'tenant_id' => $room->tenant_id,
                'santri_id' => $santri->id,
                'from_room_id' => $room->id,
                'from_room_name' => $santri->displayRoomName($room->name),
                'to_room_id' => null,
                'to_room_name' => null,
                'moved_by' => $currentUser?->id,
                'moved_at' => now(),
            ]);

            $santri->update([
                'room_id' => null,
            ]);

            return [
                'room' => $room,
                'santri' => $santri,
            ];
        });

        if (! $releaseContext) {
            return redirect()
                ->route('rooms.index')
                ->with('error', 'Santri tidak berada di kamar yang dipilih.');
        }

        /** @var Room $releasedFromRoom */
        $releasedFromRoom = $releaseContext['room'];
        /** @var Santri $releasedSantri */
        $releasedSantri = $releaseContext['santri'];

        $this->activityLogger->log(
            action: 'room_santri_released',
            actor: $currentUser,
            target: $releasedSantri,
            description: 'Santri dikeluarkan dari kamar.',
            properties: [
                'santri_id' => $releasedSantri->id,
                'santri_name' => $releasedSantri->full_name,
                'from_room_id' => $releasedFromRoom->id,
                'from_room_name' => $releasedFromRoom->name,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Santri berhasil dikeluarkan dari kamar.');
    }

    /**
     * Delete an empty room.
     */
    public function destroy(Request $request, Room $room): RedirectResponse
    {
        $currentUser = $request->user();
        $room = Room::query()
            ->visibleTo($currentUser)
            ->withCount('santris')
            ->findOrFail($room->id);

        if ($room->santris_count > 0) {
            return redirect()
                ->route('rooms.index')
                ->with('error', 'Kamar yang masih memiliki santri tidak dapat dihapus.');
        }

        $this->activityLogger->log(
            action: 'room_deleted',
            actor: $currentUser,
            target: $room,
            description: 'Master kamar kosong dihapus.',
            properties: [
                'target_name' => $room->name,
                'capacity' => $room->capacity,
                'status' => $room->status,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $room->delete();

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Kamar berhasil dihapus.');
    }

    protected function applyRoomFilters(Builder $builder, string $search, string $selectedStatus): void
    {
        $builder
            ->when($search !== '', fn ($builder) => $builder->where('name', 'like', "%{$search}%"))
            ->when($selectedStatus !== '', fn ($builder) => $builder->where('status', $selectedStatus));
    }

    /**
     * Build room status options.
     *
     * @return array<int, array{value: string, label: string}>
     */
    protected function statusOptions(): array
    {
        return [
            ['value' => Room::STATUS_ACTIVE, 'label' => 'Aktif'],
            ['value' => Room::STATUS_INACTIVE, 'label' => 'Nonaktif'],
        ];
    }
}
