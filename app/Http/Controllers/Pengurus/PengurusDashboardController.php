<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\User;
use Illuminate\View\View;

class PengurusDashboardController extends Controller
{
    /**
     * Display the pengurus dashboard with comprehensive Santri management overview.
     */
    public function index(): View
    {
        $currentUser = request()->user();
        $tenant = $currentUser?->tenant;

        $santriBaseQuery = Santri::query()->visibleTo($currentUser);

        return view('pengurus.dashboard', [
            'tenantName' => $tenant?->name ?? 'Tanpa Tenant',
            'stats' => [
                'total_santri' => (clone $santriBaseQuery)->count(),
                'active_santri' => (clone $santriBaseQuery)->where('status', Santri::STATUS_ACTIVE)->count(),
                'leave_santri' => (clone $santriBaseQuery)->where('status', Santri::STATUS_LEAVE)->count(),
                'alumni_santri' => (clone $santriBaseQuery)->where('status', Santri::STATUS_ALUMNI)->count(),
                'exited_santri' => (clone $santriBaseQuery)->where('status', Santri::STATUS_EXITED)->count(),
            ],
            'genderStats' => [
                'male' => (clone $santriBaseQuery)->where('gender', Santri::GENDER_MALE)->count(),
                'female' => (clone $santriBaseQuery)->where('gender', Santri::GENDER_FEMALE)->count(),
            ],
            'roomStats' => $this->buildRoomStats(clone $santriBaseQuery),
            'entryYearStats' => $this->buildEntryYearStats(clone $santriBaseQuery),
            'recentSantri' => (clone $santriBaseQuery)
                ->latest()
                ->limit(5)
                ->get(),
            'recentlyUpdatedSantri' => (clone $santriBaseQuery)
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get(),
            'canCreateSantri' => $currentUser?->can('create', Santri::class) ?? false,
            'canViewSantri' => $currentUser?->can('viewAny', Santri::class) ?? false,
        ]);
    }

    /**
     * Build room distribution statistics.
     */
    protected function buildRoomStats($query): array
    {
        return $query
            ->selectRaw("COALESCE(NULLIF(room_name, ''), 'Belum diatur') as room_name, COUNT(*) as count")
            ->groupBy('room_name')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(fn ($item) => [
                'room_name' => (string) $item->room_name,
                'count' => (int) $item->count,
            ])
            ->toArray();
    }

    /**
     * Build entry year statistics.
     */
    protected function buildEntryYearStats($query): array
    {
        return $query
            ->selectRaw("COALESCE(CAST(entry_year AS CHAR), 'Belum diatur') as entry_year, COUNT(*) as count")
            ->groupBy('entry_year')
            ->orderByDesc('entry_year')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'entry_year' => (string) $item->entry_year,
                'count' => (int) $item->count,
            ])
            ->toArray();
    }
}
