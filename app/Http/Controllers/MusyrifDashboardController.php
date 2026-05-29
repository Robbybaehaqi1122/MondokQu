<?php

namespace App\Http\Controllers;

use App\Models\AttendanceActivity;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\LeaveRequest;
use App\Models\Pelanggaran;
use App\Models\Santri;
use App\Models\TahfidzSession;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MusyrifDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $currentUser = $request->user();
        $today = now();

        $santriQuery = Santri::query()->visibleTo($currentUser);
        $activeSantriCount = (clone $santriQuery)->where('status', Santri::STATUS_ACTIVE)->count();

        $santriOnLeaveToday = (clone $santriQuery)
            ->whereHas('leaveRequests', fn (Builder $q) => $q->activeOnDate($today))
            ->count();

        $todaySessions = AttendanceSession::query()
            ->visibleTo($currentUser)
            ->whereDate('session_date', $today->toDateString())
            ->orderBy('status')
            ->orderBy('id')
            ->get();

        $todayStatusCounts = AttendanceRecord::query()
            ->visibleTo($currentUser)
            ->join('attendance_sessions', fn ($join) => $join
                ->on('attendance_records.attendance_session_id', '=', 'attendance_sessions.id')
                ->on('attendance_records.tenant_id', '=', 'attendance_sessions.tenant_id')
            )
            ->whereDate('attendance_sessions.session_date', $today->toDateString())
            ->select('attendance_records.status', DB::raw('COUNT(*) as total'))
            ->groupBy('attendance_records.status')
            ->pluck('total', 'attendance_records.status');

        $tahfidzQuery = TahfidzSession::query()->visibleTo($currentUser)->where('musyrif_id', $currentUser->id);
        $totalTahfidzSessions = (clone $tahfidzQuery)->count();
        $tahfidzSessionsToday = (clone $tahfidzQuery)->whereDate('session_date', $today->toDateString())->count();
        $santriWithTahfidz = (clone $tahfidzQuery)->distinct('santri_id')->count('santri_id');

        $recentTahfidz = (clone $tahfidzQuery)
            ->with(['santri', 'records.surah'])
            ->orderBy('session_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $recentPelanggaran = Pelanggaran::query()
            ->visibleTo($currentUser)
            ->with(['santri', 'kategori'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $activeActivities = AttendanceActivity::query()
            ->visibleTo($currentUser)
            ->where('status', AttendanceActivity::STATUS_ACTIVE)
            ->with('responsibleUser')
            ->orderBy('start_time')
            ->get();

        $todayName = strtolower($today->format('l'));
        $todayActivities = $activeActivities->filter(fn (AttendanceActivity $a) => in_array($todayName, $a->active_days ?? []));

        return view('musyrif.dashboard', [
            'stats' => [
                'active_santri' => $activeSantriCount,
                'on_leave_today' => $santriOnLeaveToday,
                'sessions_today' => $todaySessions->count(),
                'tahfidz_sessions' => $totalTahfidzSessions,
                'tahfidz_today' => $tahfidzSessionsToday,
                'santri_with_tahfidz' => $santriWithTahfidz,
                'total_pelanggaran' => Pelanggaran::query()->visibleTo($currentUser)->count(),
            ],
            'statusSummary' => collect(AttendanceRecord::statusOptions())->map(fn (array $opt): array => [
                'value' => $opt['value'],
                'label' => $opt['label'],
                'count' => (int) $todayStatusCounts->get($opt['value'], 0),
            ]),
            'todaySessions' => $todaySessions,
            'recentTahfidz' => $recentTahfidz,
            'recentPelanggaran' => $recentPelanggaran,
            'todayActivities' => $todayActivities,
            'today' => $today,
        ]);
    }
}
