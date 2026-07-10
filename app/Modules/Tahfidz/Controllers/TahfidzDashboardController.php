<?php

namespace App\Modules\Tahfidz\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MemorizationSchedule;
use App\Models\Santri;
use App\Models\TahfidzSession;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TahfidzDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $currentUser = $request->user();
        $today = now()->toDateString();

        $santriQuery = Santri::query()->visibleTo($currentUser)->where('status', Santri::STATUS_ACTIVE);
        $activeSantriCount = (clone $santriQuery)->count();

        $sessionQuery = TahfidzSession::query()->visibleTo($currentUser);

        $sessionsToday = (clone $sessionQuery)->where('session_date', $today)->count();
        $totalSessions = (clone $sessionQuery)->count();
        $totalSantriWithSetoran = (clone $sessionQuery)->distinct('santri_id')->count('santri_id');

        $recentSessions = (clone $sessionQuery)
            ->with(['santri', 'musyrif', 'records'])
            ->orderBy('session_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $todayName = strtolower(now()->format('l'));

        $weekSchedules = MemorizationSchedule::query()
            ->visibleTo($currentUser)
            ->active()
            ->with(['musyrif', 'room'])
            ->orderByRaw("FIELD(day_of_week, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday')")
            ->orderBy('start_time')
            ->get();

        $todaySchedules = $weekSchedules->filter(fn ($s) => $s->day_of_week === $todayName);

        return view('modules.tahfidz.dashboard', [
            'activeSantriCount' => $activeSantriCount,
            'stats' => [
                'sessions_today' => $sessionsToday,
                'total_sessions' => $totalSessions,
                'total_santri_with_setoran' => $totalSantriWithSetoran,
                'total_santri_active' => $activeSantriCount,
            ],
            'recentSessions' => $recentSessions,
            'weekSchedules' => $weekSchedules,
            'todaySchedules' => $todaySchedules,
            'today' => now(),
        ]);
    }
}
