<?php

namespace App\Exports;

use App\Models\SantriInvoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SantriInvoiceExcelExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected ?User $currentUser,
        protected string $search = '',
        protected string $status = '',
        protected string $santriId = '',
    ) {}

    public function query(): Builder
    {
        return SantriInvoice::query()
            ->withoutTenantScope()
            ->visibleTo($this->currentUser)
            ->withFilters($this->search, $this->status, $this->santriId)
            ->with('santri')
            ->orderByRaw('CASE WHEN status = ? THEN 1 ELSE 0 END', [SantriInvoice::STATUS_PAID])
            ->orderBy('due_date');
    }

    public function headings(): array
    {
        return [
            'Nomor Invoice',
            'Judul Tagihan',
            'Nama Santri',
            'NIS',
            'Periode Bulan',
            'Periode Tahun',
            'Jatuh Tempo',
            'Nominal (Rp)',
            'Terbayar (Rp)',
            'Sisa Tagihan (Rp)',
            'Status',
            'Catatan',
        ];
    }

    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            $invoice->title,
            $invoice->santri?->full_name,
            $invoice->santri?->nis,
            $invoice->period_month,
            $invoice->period_year,
            $invoice->due_date?->toDateString(),
            number_format($invoice->amount / 100, 2, '.', ''),
            number_format($invoice->paid_amount / 100, 2, '.', ''),
            number_format($invoice->outstandingAmount() / 100, 2, '.', ''),
            $invoice->statusLabel(),
            $invoice->notes,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function filename(): string
    {
        return 'tagihan-santri-'.now()->format('Ymd-His').'.xlsx';
    }
}
