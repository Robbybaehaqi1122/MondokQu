<?php

namespace App\Services;

use App\Enums\ExportFormat;
use App\Exports\KesehatanLaporanExcelExport;
use App\Exports\SantriExcelExport;
use App\Exports\SantriInvoiceExcelExport;
use App\Exports\SantriInvoicePdfExport;
use App\Exports\SantriPaymentReportExcelExport;
use App\Exports\SantriPaymentReportPdfExport;
use App\Exports\SantriPdfExport;
use App\Models\DataExport;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormatDispatcher
{
    public function downloadSantri(?User $user, ExportFormat $format, string $search, string $status, string $gender): BinaryFileResponse|StreamedResponse|Response
    {
        return match ($format) {
            ExportFormat::XLSX => Excel::download(
                new SantriExcelExport($user, $search, $status, $gender),
                (new SantriExcelExport($user, $search, $status, $gender))->filename()
            ),
            ExportFormat::PDF => (new SantriPdfExport($user, $search, $status, $gender))->download(),
        };
    }

    public function downloadInvoices(?User $user, ExportFormat $format, string $search, string $status, string $santriId): BinaryFileResponse|StreamedResponse|Response
    {
        return match ($format) {
            ExportFormat::XLSX => Excel::download(
                new SantriInvoiceExcelExport($user, $search, $status, $santriId),
                (new SantriInvoiceExcelExport($user, $search, $status, $santriId))->filename()
            ),
            ExportFormat::PDF => (new SantriInvoicePdfExport($user, $search, $status, $santriId))->download(),
        };
    }

    public function downloadPaymentReport(?User $user, ExportFormat $format, Carbon $dateFrom, Carbon $dateTo): BinaryFileResponse|StreamedResponse|Response
    {
        return match ($format) {
            ExportFormat::XLSX => Excel::download(
                new SantriPaymentReportExcelExport($user, $dateFrom, $dateTo),
                (new SantriPaymentReportExcelExport($user, $dateFrom, $dateTo))->filename()
            ),
            ExportFormat::PDF => (new SantriPaymentReportPdfExport($user, $dateFrom, $dateTo))->download(),
        };
    }

    public function storeSantri(DataExport $export, User $user, array $filters): array
    {
        $search = (string) ($filters['q'] ?? '');
        $status = (string) ($filters['status'] ?? '');
        $gender = (string) ($filters['gender'] ?? '');
        $format = ExportFormat::tryFrom($export->format) ?? ExportFormat::XLSX;

        return match ($format) {
            ExportFormat::XLSX => $this->storeExcel($export,
                new SantriExcelExport($user, $search, $status, $gender)
            ),
            ExportFormat::PDF => $this->storePdf($export,
                new SantriPdfExport($user, $search, $status, $gender)
            ),
        };
    }

    public function storeInvoices(DataExport $export, User $user, array $filters): array
    {
        $search = (string) ($filters['q'] ?? '');
        $status = (string) ($filters['status'] ?? '');
        $santriId = (string) ($filters['santri'] ?? '');
        $format = ExportFormat::tryFrom($export->format) ?? ExportFormat::XLSX;

        return match ($format) {
            ExportFormat::XLSX => $this->storeExcel($export,
                new SantriInvoiceExcelExport($user, $search, $status, $santriId)
            ),
            ExportFormat::PDF => $this->storePdf($export,
                new SantriInvoicePdfExport($user, $search, $status, $santriId)
            ),
        };
    }

    public function downloadKesehatanLaporan(?User $user, string $dateFrom, string $dateTo): BinaryFileResponse|StreamedResponse|Response
    {
        $export = new KesehatanLaporanExcelExport($user, $dateFrom, $dateTo);

        return Excel::download($export, $export->filename());
    }

    public function storeKesehatanLaporan(DataExport $export, User $user, array $filters): array
    {
        $dateFrom = (string) ($filters['date_from'] ?? now()->startOfMonth()->toDateString());
        $dateTo = (string) ($filters['date_to'] ?? now()->toDateString());

        $excelExport = new KesehatanLaporanExcelExport($user, $dateFrom, $dateTo);

        return $this->storeExcel($export, $excelExport);
    }

    protected function storeExcel(DataExport $export, $excelExport): array
    {
        $filename = $export->filename ?: $excelExport->filename();
        $path = 'exports/'.$export->id.'/'.$filename;

        Excel::store($excelExport, $path, $export->disk);

        return [$path, $filename, $excelExport->rowCount()];
    }

    protected function storePdf(DataExport $export, $pdfExport): array
    {
        $filename = $export->filename ?: $pdfExport->filename();
        $path = 'exports/'.$export->id.'/'.$filename;

        $pdfExport->store(storage_path('app/'.$path));

        return [$path, $filename, $pdfExport->rowCount()];
    }
}
