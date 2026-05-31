<?php

namespace App\Services;

use App\Enums\ExportFormat;
use App\Exports\SantriCsvExport;
use App\Exports\SantriExcelExport;
use App\Exports\SantriInvoiceCsvExport;
use App\Exports\SantriInvoiceExcelExport;
use App\Exports\SantriInvoicePdfExport;
use App\Exports\SantriPaymentReportCsvExport;
use App\Exports\SantriPaymentReportExcelExport;
use App\Exports\SantriPaymentReportPdfExport;
use App\Exports\SantriPdfExport;
use App\Models\DataExport;
use App\Models\User;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormatDispatcher
{
    public function downloadSantri(?User $user, ExportFormat $format, string $search, string $status, string $gender): StreamedResponse|\Illuminate\Http\Response
    {
        return match ($format) {
            ExportFormat::CSV => app(SantriCsvExport::class)->download($user, $search, $status, $gender),
            ExportFormat::XLSX => Excel::download(
                new SantriExcelExport($user, $search, $status, $gender),
                (new SantriExcelExport($user, $search, $status, $gender))->filename()
            ),
            ExportFormat::PDF => (new SantriPdfExport($user, $search, $status, $gender))->download(),
        };
    }

    public function downloadInvoices(?User $user, ExportFormat $format, string $search, string $status, string $santriId): StreamedResponse|\Illuminate\Http\Response
    {
        return match ($format) {
            ExportFormat::CSV => app(SantriInvoiceCsvExport::class)->download($user, $search, $status, $santriId),
            ExportFormat::XLSX => Excel::download(
                new SantriInvoiceExcelExport($user, $search, $status, $santriId),
                (new SantriInvoiceExcelExport($user, $search, $status, $santriId))->filename()
            ),
            ExportFormat::PDF => (new SantriInvoicePdfExport($user, $search, $status, $santriId))->download(),
        };
    }

    public function downloadPaymentReport(?User $user, ExportFormat $format, Carbon $dateFrom, Carbon $dateTo): StreamedResponse|\Illuminate\Http\Response
    {
        return match ($format) {
            ExportFormat::CSV => app(SantriPaymentReportCsvExport::class)->download($user, $dateFrom, $dateTo),
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
        $format = ExportFormat::tryFrom($export->format) ?? ExportFormat::CSV;

        return match ($format) {
            ExportFormat::CSV => app(SantriCsvExport::class)->store($export, $user, $filters),
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
        $format = ExportFormat::tryFrom($export->format) ?? ExportFormat::CSV;

        return match ($format) {
            ExportFormat::CSV => app(SantriInvoiceCsvExport::class)->store($export, $user, $filters),
            ExportFormat::XLSX => $this->storeExcel($export,
                new SantriInvoiceExcelExport($user, $search, $status, $santriId)
            ),
            ExportFormat::PDF => $this->storePdf($export,
                new SantriInvoicePdfExport($user, $search, $status, $santriId)
            ),
        };
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
