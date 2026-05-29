<?php

namespace App\Modules\Tahfidz\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\TahfidzSession;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TahfidzRaporController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));
        $selectedSantriId = trim((string) $request->string('santri'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        $santriQuery = Santri::query()
            ->visibleTo($currentUser)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($selectedSantriId !== '', fn ($query) => $query->where('id', $selectedSantriId))
            ->orderBy('full_name');

        $santriList = (clone $santriQuery)->get(['id', 'full_name', 'nis']);

        $selectedSantri = $selectedSantriId !== ''
            ? Santri::query()->visibleTo($currentUser)->find($selectedSantriId)
            : null;

        $raporData = collect();
        if ($selectedSantri) {
            $sessions = TahfidzSession::query()
                ->visibleTo($currentUser)
                ->with(['records.surah'])
                ->where('santri_id', $selectedSantri->id)
                ->when($dateFrom !== '', fn ($query) => $query->whereDate('session_date', '>=', $dateFrom))
                ->when($dateTo !== '', fn ($query) => $query->whereDate('session_date', '<=', $dateTo))
                ->orderBy('session_date', 'desc')
                ->get();

            $totalAyat = 0;
            $totalLancar = 0;
            $totalPerluPengulangan = 0;
            $totalBelumLancar = 0;

            foreach ($sessions as $session) {
                foreach ($session->records as $record) {
                    $ayatCount = ($record->verse_end - $record->verse_start) + 1;
                    $totalAyat += $ayatCount;

                    match ($record->evaluation) {
                        'lancar' => $totalLancar += $ayatCount,
                        'perlu_pengulangan' => $totalPerluPengulangan += $ayatCount,
                        'belum_lancar' => $totalBelumLancar += $ayatCount,
                        default => null,
                    };
                }
            }

            $raporData = (object) [
                'santri' => $selectedSantri,
                'sessions' => $sessions,
                'total_sessions' => $sessions->count(),
                'total_ayat' => $totalAyat,
                'total_lancar' => $totalLancar,
                'total_perlu_pengulangan' => $totalPerluPengulangan,
                'total_belum_lancar' => $totalBelumLancar,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ];
        }

        $santriOptions = Santri::query()
            ->visibleTo($currentUser)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nis']);

        return view('modules.tahfidz.rapor.index', [
            'santriList' => $santriList,
            'santriOptions' => $santriOptions,
            'raporData' => $raporData,
            'filters' => [
                'q' => $search,
                'santri' => $selectedSantriId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }
}
