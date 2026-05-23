<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\StoreAttendanceActivityRequest;
use App\Http\Requests\Attendance\UpdateAttendanceActivityRequest;
use App\Models\AttendanceActivity;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceActivityController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));
        $selectedStatus = trim((string) $request->string('status'));
        $selectedDay = trim((string) $request->string('day'));
        $todayDayKey = strtolower(now()->format('l'));

        $baseQuery = AttendanceActivity::query()->visibleTo($currentUser);

        $filteredQuery = (clone $baseQuery)
            ->with(['responsibleUser', 'creator'])
            ->tap(fn (Builder $builder) => $this->applyFilters($builder, $search, $selectedStatus, $selectedDay));

        $activities = (clone $filteredQuery)
            ->orderBy('start_time')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('attendance.activities.index', [
            'activities' => $activities,
            'filters' => [
                'q' => $search,
                'status' => $selectedStatus,
                'day' => $selectedDay,
            ],
            'activityStats' => [
                'total' => (clone $baseQuery)->count(),
                'active' => (clone $baseQuery)->where('status', AttendanceActivity::STATUS_ACTIVE)->count(),
                'inactive' => (clone $baseQuery)->where('status', AttendanceActivity::STATUS_INACTIVE)->count(),
                'today' => (clone $baseQuery)
                    ->where('status', AttendanceActivity::STATUS_ACTIVE)
                    ->whereJsonContains('active_days', $todayDayKey)
                    ->count(),
            ],
            'statusOptions' => $this->statusOptions(),
            'dayOptions' => $this->dayOptions(),
            'responsibleUserOptions' => $this->responsibleUserOptions($currentUser),
        ]);
    }

    public function store(StoreAttendanceActivityRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $currentUser = $request->user();

        if (! $currentUser?->tenant_id) {
            abort(403);
        }

        $activity = AttendanceActivity::query()->create([
            'tenant_id' => $currentUser->tenant_id,
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'] ?? null,
            'active_days' => AttendanceActivity::normalizeDays($validated['active_days']),
            'responsible_user_id' => $validated['responsible_user_id'] ?? null,
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
            'created_by' => $currentUser->id,
        ]);

        $this->activityLogger->log(
            action: 'attendance_activity_created',
            actor: $currentUser,
            target: $activity,
            description: 'Master kegiatan absensi baru dibuat.',
            properties: [
                'target_name' => $activity->name,
                'start_time' => $activity->start_time,
                'end_time' => $activity->end_time,
                'active_days' => $activity->active_days,
                'status' => $activity->status,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('attendance.activities.index')
            ->with('success', 'Kegiatan absensi berhasil dibuat.');
    }

    public function update(UpdateAttendanceActivityRequest $request, AttendanceActivity $attendanceActivity): RedirectResponse
    {
        $validated = $request->validated();
        $currentUser = $request->user();

        $activity = AttendanceActivity::query()
            ->visibleTo($currentUser)
            ->findOrFail($attendanceActivity->id);

        $previousValues = $activity->only([
            'name',
            'start_time',
            'end_time',
            'active_days',
            'responsible_user_id',
            'status',
            'description',
        ]);

        $activity->update([
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'] ?? null,
            'active_days' => AttendanceActivity::normalizeDays($validated['active_days']),
            'responsible_user_id' => $validated['responsible_user_id'] ?? null,
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
        ]);

        $activity->refresh();

        $this->activityLogger->log(
            action: 'attendance_activity_updated',
            actor: $currentUser,
            target: $activity,
            description: 'Master kegiatan absensi diperbarui.',
            properties: [
                'target_name' => $activity->name,
                'before' => $previousValues,
                'after' => $activity->only([
                    'name',
                    'start_time',
                    'end_time',
                    'active_days',
                    'responsible_user_id',
                    'status',
                    'description',
                ]),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('attendance.activities.index')
            ->with('success', 'Kegiatan absensi berhasil diperbarui.');
    }

    public function destroy(Request $request, AttendanceActivity $attendanceActivity): RedirectResponse
    {
        $currentUser = $request->user();
        $activity = AttendanceActivity::query()
            ->visibleTo($currentUser)
            ->findOrFail($attendanceActivity->id);

        $this->activityLogger->log(
            action: 'attendance_activity_deleted',
            actor: $currentUser,
            target: $activity,
            description: 'Master kegiatan absensi dihapus.',
            properties: [
                'target_name' => $activity->name,
                'start_time' => $activity->start_time,
                'end_time' => $activity->end_time,
                'active_days' => $activity->active_days,
                'status' => $activity->status,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $activity->delete();

        return redirect()
            ->route('attendance.activities.index')
            ->with('success', 'Kegiatan absensi berhasil dihapus.');
    }

    protected function applyFilters(Builder $builder, string $search, string $selectedStatus, string $selectedDay): void
    {
        $builder
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $nested) use ($search): void {
                $nested
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($selectedStatus !== '', fn (Builder $query) => $query->where('status', $selectedStatus))
            ->when($selectedDay !== '', fn (Builder $query) => $query->whereJsonContains('active_days', $selectedDay));
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    protected function statusOptions(): array
    {
        return [
            ['value' => AttendanceActivity::STATUS_ACTIVE, 'label' => 'Aktif'],
            ['value' => AttendanceActivity::STATUS_INACTIVE, 'label' => 'Nonaktif'],
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    protected function dayOptions(): array
    {
        return collect(AttendanceActivity::dayLabels())
            ->map(fn (string $label, string $value): array => [
                'value' => $value,
                'label' => $label,
            ])
            ->values()
            ->all();
    }

    protected function responsibleUserOptions(?User $currentUser)
    {
        return User::query()
            ->with('tenant')
            ->when(! $currentUser?->isSuperAdmin(), fn (Builder $query) => $query->where('tenant_id', $currentUser?->tenant_id))
            ->whereNotNull('tenant_id')
            ->where('status', User::STATUS_ACTIVE)
            ->orderBy('name')
            ->limit(500)
            ->get();
    }
}
