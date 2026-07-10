<?php

namespace App\Modules\Tahfidz\Controllers;

use App\Exports\TahfidzRaporPdfExport;
use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Santri;
use App\Models\TahfidzTarget;
use App\Services\TahfidzRaporService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TahfidzRaporController extends Controller
{
    public function __construct(
        protected TahfidzRaporService $raporService
    ) {}

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

        $raporData = null;
        if ($selectedSantri) {
            $raporStats = $this->raporService->buildRaporForSantri(
                santriId: $selectedSantri->id,
                dateFrom: $dateFrom,
                dateTo: $dateTo
            );

            $targets = TahfidzTarget::query()
                ->visibleTo($currentUser)
                ->where('santri_id', $selectedSantri->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $raporData = (object) [
                'santri' => $selectedSantri,
                'sessions' => $raporStats['sessions'],
                'total_sessions' => $raporStats['total_sessions'],
                'total_ayat' => $raporStats['total_ayat'],
                'total_lancar' => $raporStats['total_lancar'],
                'total_perlu_pengulangan' => $raporStats['total_perlu_pengulangan'],
                'total_belum_lancar' => $raporStats['total_belum_lancar'],
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'targets' => $targets,
            ];
        }

        $santriOptions = Santri::query()
            ->visibleTo($currentUser)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nis']);

        $roomOptions = Room::query()
            ->visibleTo($currentUser)
            ->where('status', Room::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('modules.tahfidz.rapor.index', [
            'santriList' => $santriList,
            'santriOptions' => $santriOptions,
            'roomOptions' => $roomOptions,
            'raporData' => $raporData,
            'filters' => [
                'q' => $search,
                'santri' => $selectedSantriId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $currentUser = $request->user();
        $santriId = (int) $request->integer('santri');
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        if (! $santriId) {
            abort(400, 'Santri harus dipilih untuk export PDF.');
        }

        $export = new TahfidzRaporPdfExport(
            currentUser: $currentUser,
            santriId: $santriId,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
        );

        return $export->download();
    }

    public function exportBatchPdf(Request $request): Response
    {
        $currentUser = $request->user();
        $roomId = (int) $request->integer('room');
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        if (! $roomId) {
            abort(400, 'Kelas harus dipilih untuk export batch PDF.');
        }

        $export = new TahfidzRaporPdfExport(
            currentUser: $currentUser,
            roomId: $roomId,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
        );

        return $export->download();
    }
}
