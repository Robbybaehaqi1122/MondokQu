<?php

namespace App\Modules\KesehatanQu\Controllers;

use App\Enums\ExportFormat;
use App\Http\Controllers\Controller;
use App\Models\DataExport;
use App\Models\KesehatanImunisasi;
use App\Models\KesehatanObat;
use App\Models\KesehatanPemeriksaan;
use App\Models\Santri;
use App\Services\DataExportManager;
use App\Services\FormatDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KesehatanQuLaporanController extends Controller
{
    public function __construct(
        protected DataExportManager $dataExportManager,
    ) {}
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $dateFrom = trim((string) $request->string('date_from', now()->startOfMonth()->toDateString()));
        $dateTo = trim((string) $request->string('date_to', now()->toDateString()));

        $totalPemeriksaan = KesehatanPemeriksaan::query()
            ->visibleTo($currentUser)
            ->whereBetween('tanggal_pemeriksaan', [$dateFrom, $dateTo])
            ->count();

        $totalRujukan = KesehatanPemeriksaan::query()
            ->visibleTo($currentUser)
            ->whereHas('rujukan')
            ->whereBetween('tanggal_pemeriksaan', [$dateFrom, $dateTo])
            ->count();

        $santriDenganKondisiKhusus = Santri::query()
            ->visibleTo($currentUser)
            ->whereHas('rekamMedis', function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('riwayat_penyakit')
                        ->where('riwayat_penyakit', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('alergi_obat')
                        ->where('alergi_obat', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('alergi_makanan')
                        ->where('alergi_makanan', '!=', '');
                });
            })
            ->with('rekamMedis')
            ->orderBy('full_name')
            ->get();

        $imunisasiPerSantri = KesehatanImunisasi::query()
            ->visibleTo($currentUser)
            ->with('santri')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(fn ($item) => $item->santri?->full_name ?? 'Tanpa Nama');

        $obatExpired = KesehatanObat::query()
            ->visibleTo($currentUser)
            ->whereNotNull('expired_date')
            ->where('expired_date', '<=', now()->addMonth())
            ->orderBy('expired_date')
            ->get();

        return view('modules.kesehatan-qu.laporan.index', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalPemeriksaan' => $totalPemeriksaan,
            'totalRujukan' => $totalRujukan,
            'santriDenganKondisiKhusus' => $santriDenganKondisiKhusus,
            'imunisasiPerSantri' => $imunisasiPerSantri,
            'obatExpired' => $obatExpired,
        ]);
    }

    public function export(Request $request): RedirectResponse|BinaryFileResponse|StreamedResponse
    {
        $currentUser = $request->user();
        $dateFrom = trim((string) $request->string('date_from', now()->startOfMonth()->toDateString()));
        $dateTo = trim((string) $request->string('date_to', now()->toDateString()));
        $format = ExportFormat::tryFrom($request->string('format', 'xlsx')) ?? ExportFormat::XLSX;

        $export = new \App\Exports\KesehatanLaporanExcelExport($currentUser, $dateFrom, $dateTo);
        $rowCount = 0;
        foreach ($export->sheets() as $sheet) {
            $rowCount += $sheet->collection()->count();
        }

        if ($this->dataExportManager->shouldQueue($rowCount)) {
            $this->dataExportManager->queue(
                $currentUser,
                DataExport::TYPE_KESEHATAN_LAPORAN,
                'Export Laporan Kesehatan',
                $export->filename(),
                ['date_from' => $dateFrom, 'date_to' => $dateTo],
                $rowCount,
                $format->value
            );

            return redirect()
                ->route('kesehatan.laporan.index', ['date_from' => $dateFrom, 'date_to' => $dateTo])
                ->with('success', 'Export laporan kesehatan sedang diproses di background. Anda akan mendapat notifikasi saat selesai.');
        }

        return app(FormatDispatcher::class)->downloadKesehatanLaporan($currentUser, $dateFrom, $dateTo);
    }
}
