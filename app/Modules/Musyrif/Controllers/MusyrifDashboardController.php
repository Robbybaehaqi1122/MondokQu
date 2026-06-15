<?php

namespace App\Modules\Musyrif\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttendanceActivity;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\MemorizationSchedule;
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

        $santriBinaanIds = (clone $tahfidzQuery)
            ->distinct('santri_id')
            ->pluck('santri_id');

        $santriBinaanCount = $santriBinaanIds->count();

        $pelanggaranBulanIniQuery = Pelanggaran::query()
            ->visibleTo($currentUser)
            ->whereIn('santri_id', $santriBinaanIds)
            ->whereMonth('tanggal', $today->month)
            ->whereYear('tanggal', $today->year);

        $totalPelanggaranBulanIni = (clone $pelanggaranBulanIniQuery)->count();
        $totalPoinBulanIni = (clone $pelanggaranBulanIniQuery)->sum('poin');
        $santriTercatatBulanIni = (clone $pelanggaranBulanIniQuery)->distinct('santri_id')->count('santri_id');

        $santriPelanggaranTertinggi = Pelanggaran::query()
            ->visibleTo($currentUser)
            ->whereIn('santri_id', $santriBinaanIds)
            ->select('santri_id', DB::raw('SUM(poin) as total_poin'), DB::raw('COUNT(*) as total_kali'))
            ->groupBy('santri_id')
            ->with('santri')
            ->orderByDesc('total_poin')
            ->limit(10)
            ->get();

        $grafikKategori = Pelanggaran::query()
            ->visibleTo($currentUser)
            ->whereIn('santri_id', $santriBinaanIds)
            ->select('kategori_id', DB::raw('COUNT(*) as total'))
            ->groupBy('kategori_id')
            ->with('kategori')
            ->get();

        $santriBinaanWithPoin = Santri::query()
            ->visibleTo($currentUser)
            ->whereIn('id', $santriBinaanIds)
            ->withCount(['tahfidzSessions as total_setoran' => fn ($q) => $q->where('musyrif_id', $currentUser->id)])
            ->orderBy('full_name')
            ->get()
            ->each(function ($santri) use ($currentUser) {
                $santri->total_poin = (int) Pelanggaran::query()
                    ->visibleTo($currentUser)
                    ->where('santri_id', $santri->id)
                    ->sum('poin');
                $santri->total_pelanggaran = Pelanggaran::query()
                    ->visibleTo($currentUser)
                    ->where('santri_id', $santri->id)
                    ->count();
            })
            ->sortByDesc('total_poin')
            ->values();

        $recentPelanggaran = Pelanggaran::query()
            ->visibleTo($currentUser)
            ->whereIn('santri_id', $santriBinaanIds)
            ->with(['santri', 'kategori'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $activeActivities = AttendanceActivity::query()
            ->visibleTo($currentUser)
            ->where('status', AttendanceActivity::STATUS_ACTIVE)
            ->with('responsibleUser')
            ->orderBy('start_time')
            ->get();

        $todayName = strtolower($today->format('l'));
        $todayActivities = $activeActivities->filter(fn (AttendanceActivity $a) => in_array($todayName, $a->active_days ?? []));

        $todaySchedules = MemorizationSchedule::query()
            ->visibleTo($currentUser)
            ->active()
            ->where('musyrif_id', $currentUser->id)
            ->where('day_of_week', $todayName)
            ->with('room')
            ->orderBy('start_time')
            ->get();

        $allSchedules = MemorizationSchedule::query()
            ->visibleTo($currentUser)
            ->active()
            ->where('musyrif_id', $currentUser->id)
            ->with('room')
            ->orderByRaw("FIELD(day_of_week, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday')")
            ->orderBy('start_time')
            ->get();

        return view('musyrif.dashboard', [
            'stats' => [
                'active_santri' => $activeSantriCount,
                'on_leave_today' => $santriOnLeaveToday,
                'sessions_today' => $todaySessions->count(),
                'tahfidz_sessions' => $totalTahfidzSessions,
                'tahfidz_today' => $tahfidzSessionsToday,
                'santri_with_tahfidz' => $santriWithTahfidz,
                'santri_binaan' => $santriBinaanCount,
                'total_pelanggaran_bulan_ini' => $totalPelanggaranBulanIni,
                'total_poin_bulan_ini' => $totalPoinBulanIni,
                'santri_tercatat_bulan_ini' => $santriTercatatBulanIni,
            ],
            'statusSummary' => collect(AttendanceRecord::statusOptions())->map(fn (array $opt): array => [
                'value' => $opt['value'],
                'label' => $opt['label'],
                'count' => (int) $todayStatusCounts->get($opt['value'], 0),
            ]),
            'todaySessions' => $todaySessions,
            'recentTahfidz' => $recentTahfidz,
            'recentPelanggaran' => $recentPelanggaran,
            'santriPelanggaranTertinggi' => $santriPelanggaranTertinggi,
            'grafikKategori' => $grafikKategori,
            'santriBinaanWithPoin' => $santriBinaanWithPoin,
            'todayActivities' => $todayActivities,
            'activeActivities' => $activeActivities,
            'todaySchedules' => $todaySchedules,
            'allSchedules' => $allSchedules,
            'today' => $today,
        ]);
    }
}
