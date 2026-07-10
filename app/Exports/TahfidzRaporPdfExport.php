<?php

namespace App\Exports;

use App\Models\Room;
use App\Models\Santri;
use App\Models\TahfidzTarget;
use App\Models\User;
use App\Services\TahfidzRaporService;
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
        protected ?TahfidzRaporService $raporService = null,
    ) {
        $this->raporService = $raporService ?? new TahfidzRaporService;
    }

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

        $raporStats = $this->raporService->buildRaporForSantri(
            santriId: $santri->id,
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo
        );

        $targets = TahfidzTarget::query()
            ->visibleTo($this->currentUser)
            ->where('santri_id', $santri->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'santri' => $santri,
            'sessions' => $raporStats['sessions'],
            'total_sessions' => $raporStats['total_sessions'],
            'total_ayat' => $raporStats['total_ayat'],
            'total_lancar' => $raporStats['total_lancar'],
            'total_perlu_pengulangan' => $raporStats['total_perlu_pengulangan'],
            'total_belum_lancar' => $raporStats['total_belum_lancar'],
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
            $raporStats = $this->raporService->buildRaporForSantri(
                santriId: $santri->id,
                dateFrom: $this->dateFrom,
                dateTo: $this->dateTo
            );

            $santriRapors[] = [
                'santri' => $santri,
                'sessions' => $raporStats['sessions'],
                'total_sessions' => $raporStats['total_sessions'],
                'total_ayat' => $raporStats['total_ayat'],
                'total_lancar' => $raporStats['total_lancar'],
                'total_perlu_pengulangan' => $raporStats['total_perlu_pengulangan'],
                'total_belum_lancar' => $raporStats['total_belum_lancar'],
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
