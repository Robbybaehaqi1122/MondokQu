<?php

namespace App\Services;

use App\Models\AttitudeGrade;
use App\Models\NilaiSantri;
use App\Models\NilaiSikap;
use App\Models\Pelanggaran;
use App\Models\Santri;
use App\Models\TahfidzRecord;
use App\Models\TahfidzSession;
use App\Models\User;

class RaporService
{
    public function getRaporData(Santri $santri, string $semester, User $user): array
    {
        $nilais = NilaiSantri::query()
            ->visibleTo($user)
            ->where('santri_id', $santri->id)
            ->where('semester', $semester)
            ->with(['mataPelajaran', 'inputBy'])
            ->get();

        $nilaiSikap = NilaiSikap::query()
            ->visibleTo($user)
            ->where('santri_id', $santri->id)
            ->where('semester', $semester)
            ->first();

        $attitudeGrades = AttitudeGrade::query()
            ->visibleTo($user)
            ->where('santri_id', $santri->id)
            ->where('semester', $semester)
            ->get()
            ->groupBy('aspect');

        $tahfidzStats = TahfidzRecord::query()
            ->visibleTo($user)
            ->whereIn('tahfidz_session_id', TahfidzSession::query()
                ->where('santri_id', $santri->id)
                ->select('id')
            )
            ->selectRaw('COALESCE(SUM(verse_end - verse_start + 1), 0) as total_ayat')
            ->selectRaw('COUNT(*) as total_record')
            ->selectRaw("COALESCE(SUM(CASE WHEN evaluation = 'lancar' THEN 1 ELSE 0 END), 0) as lancar_count")
            ->first();

        $totalPoinPelanggaran = Pelanggaran::query()
            ->visibleTo($user)
            ->where('santri_id', $santri->id)
            ->sum('poin');

        $rataRataKelas = NilaiSantri::query()
            ->visibleTo($user)
            ->where('semester', $semester)
            ->selectRaw('mata_pelajaran_id, COALESCE(ROUND(AVG((nilai_pengetahuan + nilai_keterampilan) / 2)), 0) as avg')
            ->groupBy('mata_pelajaran_id')
            ->pluck('avg', 'mata_pelajaran_id');

        $peringkatKelas = $this->hitungPeringkatKelas($semester, $santri->id, $user);

        $semesters = NilaiSantri::query()
            ->visibleTo($user)
            ->distinct()
            ->orderBy('semester', 'desc')
            ->pluck('semester');

        return [
            'nilais' => $nilais,
            'nilaiSikap' => $nilaiSikap,
            'attitudeGrades' => $attitudeGrades,
            'tahfidzStats' => $tahfidzStats,
            'totalPoinPelanggaran' => $totalPoinPelanggaran,
            'rataRataKelas' => $rataRataKelas,
            'peringkatKelas' => $peringkatKelas,
            'semesters' => $semesters,
        ];
    }

    private function hitungPeringkatKelas(string $semester, int $santriId, User $user): array
    {
        $rataRataSantris = NilaiSantri::query()
            ->visibleTo($user)
            ->where('semester', $semester)
            ->selectRaw('santri_id, COALESCE(ROUND(AVG((nilai_pengetahuan + nilai_keterampilan) / 2)), 0) as rata_rata')
            ->groupBy('santri_id')
            ->orderByDesc('rata_rata')
            ->get()
            ->values();

        $totalSantri = $rataRataSantris->count();
        $peringkat = null;
        $rataSaya = null;

        foreach ($rataRataSantris as $i => $item) {
            if ((int) $item->santri_id === $santriId) {
                $peringkat = $i + 1;
                $rataSaya = (int) $item->rata_rata;
                break;
            }
        }

        return [
            'peringkat' => $peringkat,
            'total' => $totalSantri,
            'rata_rata' => $rataSaya,
        ];
    }
}
