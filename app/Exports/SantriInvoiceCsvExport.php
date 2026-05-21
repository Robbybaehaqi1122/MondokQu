<?php

namespace App\Exports;

use App\Models\DataExport;
use App\Models\SantriInvoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SantriInvoiceCsvExport
{
    /**
     * Download filtered invoice list as CSV.
     */
    public function download(?User $currentUser, string $search, string $status, string $santriId): StreamedResponse
    {
        return response()->streamDownload(
            fn () => $this->write($currentUser, $search, $status, $santriId),
            $this->filename(),
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    /**
     * Store filtered invoice list as CSV for a background export.
     *
     * @return array{0: string, 1: string, 2: int}
     */
    public function store(DataExport $export, User $currentUser, array $filters): array
    {
        $search = (string) ($filters['q'] ?? '');
        $status = (string) ($filters['status'] ?? '');
        $santriId = (string) ($filters['santri'] ?? '');
        $filename = $export->filename ?: $this->filename();
        $path = 'exports/'.$export->id.'/'.$filename;
        $disk = Storage::disk($export->disk);

        $disk->makeDirectory(dirname($path));

        $handle = fopen($disk->path($path), 'w');

        if ($handle === false) {
            throw new \RuntimeException('File export tidak dapat dibuat.');
        }

        $this->writeToHandle($handle, $this->query($currentUser, $search, $status, $santriId));
        fclose($handle);

        return [$path, $filename, $this->rowCount($currentUser, $search, $status, $santriId)];
    }

    public function filename(): string
    {
        return 'tagihan-santri-'.now()->format('Ymd-His').'.csv';
    }

    public function rowCount(?User $currentUser, string $search, string $status, string $santriId): int
    {
        return (clone $this->filteredQuery($currentUser, $search, $status, $santriId))->count();
    }

    protected function write(?User $currentUser, string $search, string $status, string $santriId): void
    {
        $handle = fopen('php://output', 'w');

        if ($handle === false) {
            return;
        }

        $this->writeToHandle($handle, $this->query($currentUser, $search, $status, $santriId));
        fclose($handle);
    }

    protected function query(?User $currentUser, string $search, string $status, string $santriId): Builder
    {
        return $this->filteredQuery($currentUser, $search, $status, $santriId)
            ->with('santri')
            ->orderByRaw('CASE WHEN status = ? THEN 1 ELSE 0 END', [SantriInvoice::STATUS_PAID])
            ->orderBy('due_date');
    }

    protected function filteredQuery(?User $currentUser, string $search, string $status, string $santriId): Builder
    {
        return SantriInvoice::query()
            ->withoutTenantScope()
            ->visibleTo($currentUser)
            ->withFilters($search, $status, $santriId);
    }

    protected function writeToHandle($handle, Builder $query): void
    {
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, [
            'Nomor Invoice',
            'Judul Tagihan',
            'Nama Santri',
            'NIS',
            'Periode Bulan',
            'Periode Tahun',
            'Jatuh Tempo',
            'Nominal',
            'Terbayar',
            'Sisa Tagihan',
            'Status',
            'Catatan',
        ]);

        $query->chunk(500, function (Collection $invoices) use ($handle): void {
            foreach ($invoices as $invoice) {
                fputcsv($handle, [
                    $invoice->invoice_number,
                    $invoice->title,
                    $invoice->santri?->full_name,
                    $invoice->santri?->nis,
                    $invoice->period_month,
                    $invoice->period_year,
                    $invoice->due_date?->toDateString(),
                    number_format((float) $invoice->amount, 2, '.', ''),
                    number_format((float) $invoice->paid_amount, 2, '.', ''),
                    number_format($invoice->outstandingAmount(), 2, '.', ''),
                    $invoice->statusLabel(),
                    $invoice->notes,
                ]);
            }
        });
    }
}
