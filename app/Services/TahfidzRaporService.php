<?php

namespace App\Services;

use App\Models\TahfidzSession;
use Illuminate\Support\Collection;

class TahfidzRaporService
{
    public function computeStatsFromSessions(Collection $sessions): array
    {
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

        return [
            'total_ayat' => $totalAyat,
            'total_lancar' => $totalLancar,
            'total_perlu_pengulangan' => $totalPerluPengulangan,
            'total_belum_lancar' => $totalBelumLancar,
        ];
    }

    public function buildRaporForSantri(int $santriId, string $dateFrom = '', string $dateTo = ''): array
    {
        $sessions = TahfidzSession::query()
            ->with(['records.surah'])
            ->where('santri_id', $santriId)
            ->when($dateFrom !== '', fn ($q) => $q->whereDate('session_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($q) => $q->whereDate('session_date', '<=', $dateTo))
            ->orderBy('session_date', 'desc')
            ->get();

        $stats = $this->computeStatsFromSessions($sessions);

        return array_merge($stats, [
            'sessions' => $sessions,
            'total_sessions' => $sessions->count(),
        ]);
    }
}
