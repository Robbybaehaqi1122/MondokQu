<?php

namespace App\Exports;

use App\Models\Room;
use App\Models\Santri;
use App\Models\TahfidzSession;
use App\Models\TahfidzTarget;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class TahfidzRaporPdfExport
{
    public function __construct(
        protected ?User $currentUser,
        protected ?int $santriId = null,
        protected ?int $roomId = null,
        protected string $dateFrom = '',
        protected string $dateTo = '',
    ) {}

    public function download(): Response
    {
        if ($this->santriId) {
            return $this->downloadSingle();
        }

        if ($this->roomId) {
            return $this->downloadBatch();
        }

        abort(400, 'Santri atau Kelas harus dipilih.');
    }

    public function downloadSingle(): Response
    {
        $rapor = $this->buildSingleRapor();

        $pdf = Pdf::loadView('exports.pdf.tahfidz-rapor', $rapor);

        return $pdf->download($this->filename());
    }

    public function downloadBatch(): Response
    {
        $data = $this->buildBatchRapor();

        $pdf = Pdf::loadView('exports.pdf.tahfidz-rapor-batch', $data);

        return $pdf->download($this->filename());
    }

    public function store(string $path): string
    {
        $rapor = $this->buildSingleRapor();

        $pdf = Pdf::loadView('exports.pdf.tahfidz-rapor', $rapor);
        $pdf->save($path);

        return $path;
    }

    public function filename(): string
    {
        if ($this->santriId) {
            $santri = Santri::query()->visibleTo($this->currentUser)->find($this->santriId);

            return 'rapor-tahfidz-'
                .($santri ? preg_replace('/[^a-zA-Z0-9]/', '-', strtolower($santri->full_name)) : 'santri')
                .'-'.now()->format('Ymd-His').'.pdf';
        }

        if ($this->roomId) {
            $room = Room::query()->visibleTo($this->currentUser)->find($this->roomId);

            return 'rapor-tahfidz-'
                .($room ? preg_replace('/[^a-zA-Z0-9]/', '-', strtolower($room->name)) : 'kelas')
                .'-'.now()->format('Ymd-His').'.pdf';
        }

        return 'rapor-tahfidz-'.now()->format('Ymd-His').'.pdf';
    }

    protected function buildSingleRapor(): array
    {
        $santri = Santri::query()
            ->visibleTo($this->currentUser)
            ->with(['guardians', 'room'])
            ->findOrFail($this->santriId);

        $sessions = TahfidzSession::query()
            ->visibleTo($this->currentUser)
            ->with(['records.surah', 'musyrif'])
            ->where('santri_id', $santri->id)
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('session_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('session_date', '<=', $this->dateTo))
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

        $targets = TahfidzTarget::query()
            ->visibleTo($this->currentUser)
            ->where('santri_id', $santri->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'santri' => $santri,
            'sessions' => $sessions,
            'total_sessions' => $sessions->count(),
            'total_ayat' => $totalAyat,
            'total_lancar' => $totalLancar,
            'total_perlu_pengulangan' => $totalPerluPengulangan,
            'total_belum_lancar' => $totalBelumLancar,
            'targets' => $targets,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ];
    }

    protected function buildBatchRapor(): array
    {
        $room = Room::query()
            ->visibleTo($this->currentUser)
            ->findOrFail($this->roomId);

        $santris = Santri::query()
            ->visibleTo($this->currentUser)
            ->where('room_id', $room->id)
            ->where('status', Santri::STATUS_ACTIVE)
            ->orderBy('full_name')
            ->get();

        $santriRapors = [];

        foreach ($santris as $santri) {
            $sessions = TahfidzSession::query()
                ->visibleTo($this->currentUser)
                ->with(['records.surah'])
                ->where('santri_id', $santri->id)
                ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('session_date', '>=', $this->dateFrom))
                ->when($this->dateTo !== '', fn ($q) => $q->whereDate('session_date', '<=', $this->dateTo))
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

            $santriRapors[] = [
                'santri' => $santri,
                'sessions' => $sessions,
                'total_sessions' => $sessions->count(),
                'total_ayat' => $totalAyat,
                'total_lancar' => $totalLancar,
                'total_perlu_pengulangan' => $totalPerluPengulangan,
                'total_belum_lancar' => $totalBelumLancar,
            ];
        }

        return [
            'room' => $room,
            'santriRapors' => $santriRapors,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ];
    }
}
