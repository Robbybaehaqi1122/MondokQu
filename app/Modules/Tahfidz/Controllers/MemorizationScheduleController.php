<?php

namespace App\Modules\Tahfidz\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MemorizationSchedule;
use App\Models\Room;
use App\Models\User;
use App\Modules\Tahfidz\Requests\StoreMemorizationScheduleRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemorizationScheduleController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));
        $selectedMusyrif = trim((string) $request->string('musyrif'));
        $selectedDay = trim((string) $request->string('day'));

        $schedules = MemorizationSchedule::query()
            ->visibleTo($currentUser)
            ->with(['musyrif', 'room'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('musyrif', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->when($selectedMusyrif !== '', fn ($query) => $query->where('musyrif_id', $selectedMusyrif))
            ->when($selectedDay !== '', fn ($query) => $query->where('day_of_week', $selectedDay))
            ->orderByRaw("FIELD(day_of_week, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday')")
            ->orderBy('start_time')
            ->paginate(20)
            ->withQueryString();

        $musyrifOptions = User::query()
            ->visibleTo($currentUser)
            ->whereHas('roles', function ($q) use ($currentUser) {
                $q->where('name', 'Musyrif/Ustadz')
                    ->where(function ($tenantQ) use ($currentUser) {
                        $tenantQ->whereNull('tenant_id')
                            ->orWhere('tenant_id', $currentUser->tenant_id);
                    });
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('modules.tahfidz.jadwal.index', [
            'schedules' => $schedules,
            'musyrifOptions' => $musyrifOptions,
            'daysOfWeek' => MemorizationSchedule::daysOfWeek(),
            'filters' => [
                'q' => $search,
                'musyrif' => $selectedMusyrif,
                'day' => $selectedDay,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $currentUser = $request->user();

        $roomOptions = Room::query()
            ->visibleTo($currentUser)
            ->where('status', Room::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name']);

        $musyrifOptions = User::query()
            ->visibleTo($currentUser)
            ->whereHas('roles', function ($q) use ($currentUser) {
                $q->where('name', 'Musyrif/Ustadz')
                    ->where(function ($tenantQ) use ($currentUser) {
                        $tenantQ->whereNull('tenant_id')
                            ->orWhere('tenant_id', $currentUser->tenant_id);
                    });
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('modules.tahfidz.jadwal.create', [
            'musyrifOptions' => $musyrifOptions,
            'roomOptions' => $roomOptions,
            'daysOfWeek' => MemorizationSchedule::daysOfWeek(),
        ]);
    }

    public function store(StoreMemorizationScheduleRequest $request): RedirectResponse
    {
        $currentUser = $request->user();
        $validated = $request->validated();

        $schedule = MemorizationSchedule::query()->create([
            'tenant_id' => $currentUser->tenant_id,
            'musyrif_id' => $validated['musyrif_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'max_santri' => $validated['max_santri'],
            'room_id' => $validated['room_id'] ?? null,
            'is_active' => true,
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->activityLogger->log(
            action: 'memorization_schedule_created',
            actor: $currentUser,
            target: $schedule,
            description: 'Jadwal setoran tahfidz baru ditambahkan.',
            properties: [
                'musyrif_id' => $schedule->musyrif_id,
                'day_of_week' => $schedule->day_of_week,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('tahfidz.jadwal.index')
            ->with('success', 'Jadwal setoran tahfidz berhasil ditambahkan.');
    }

    public function edit(Request $request, MemorizationSchedule $memorizationSchedule): View
    {
        $currentUser = $request->user();

        $schedule = MemorizationSchedule::query()
            ->visibleTo($currentUser)
            ->findOrFail($memorizationSchedule->id);

        $roomOptions = Room::query()
            ->visibleTo($currentUser)
            ->where('status', Room::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name']);

        $musyrifOptions = User::query()
            ->visibleTo($currentUser)
            ->whereHas('roles', function ($q) use ($currentUser) {
                $q->where('name', 'Musyrif/Ustadz')
                    ->where(function ($tenantQ) use ($currentUser) {
                        $tenantQ->whereNull('tenant_id')
                            ->orWhere('tenant_id', $currentUser->tenant_id);
                    });
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('modules.tahfidz.jadwal.edit', [
            'schedule' => $schedule,
            'musyrifOptions' => $musyrifOptions,
            'roomOptions' => $roomOptions,
            'daysOfWeek' => MemorizationSchedule::daysOfWeek(),
        ]);
    }

    public function update(StoreMemorizationScheduleRequest $request, MemorizationSchedule $memorizationSchedule): RedirectResponse
    {
        $currentUser = $request->user();
        $validated = $request->validated();

        $schedule = MemorizationSchedule::query()
            ->visibleTo($currentUser)
            ->findOrFail($memorizationSchedule->id);

        $schedule->update([
            'musyrif_id' => $validated['musyrif_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'max_santri' => $validated['max_santri'],
            'room_id' => $validated['room_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->activityLogger->log(
            action: 'memorization_schedule_updated',
            actor: $currentUser,
            target: $schedule,
            description: 'Jadwal setoran tahfidz diperbarui.',
            properties: [
                'musyrif_id' => $schedule->musyrif_id,
                'day_of_week' => $schedule->day_of_week,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('tahfidz.jadwal.index')
            ->with('success', 'Jadwal setoran tahfidz berhasil diperbarui.');
    }

    public function toggleActive(Request $request, MemorizationSchedule $memorizationSchedule): RedirectResponse
    {
        $currentUser = $request->user();

        $schedule = MemorizationSchedule::query()
            ->visibleTo($currentUser)
            ->findOrFail($memorizationSchedule->id);

        $schedule->update([
            'is_active' => ! $schedule->is_active,
        ]);

        $status = $schedule->is_active ? 'diaktifkan' : 'dinonaktifkan';

        $this->activityLogger->log(
            action: 'memorization_schedule_toggled',
            actor: $currentUser,
            target: $schedule,
            description: "Jadwal setoran tahfidz {$status}.",
            properties: ['is_active' => $schedule->is_active],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('tahfidz.jadwal.index')
            ->with('success', "Jadwal setoran tahfidz berhasil {$status}.");
    }

    public function destroy(Request $request, MemorizationSchedule $memorizationSchedule): RedirectResponse
    {
        $currentUser = $request->user();

        $schedule = MemorizationSchedule::query()
            ->visibleTo($currentUser)
            ->findOrFail($memorizationSchedule->id);

        $this->activityLogger->log(
            action: 'memorization_schedule_deleted',
            actor: $currentUser,
            target: $schedule,
            description: 'Jadwal setoran tahfidz dihapus.',
            properties: [
                'musyrif_id' => $schedule->musyrif_id,
                'day_of_week' => $schedule->day_of_week,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $schedule->delete();

        return redirect()
            ->route('tahfidz.jadwal.index')
            ->with('success', 'Jadwal setoran tahfidz berhasil dihapus.');
    }
}
