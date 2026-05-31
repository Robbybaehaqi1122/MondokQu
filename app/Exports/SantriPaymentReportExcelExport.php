<?php

namespace App\Exports;

use App\Models\SantriPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SantriPaymentReportExcelExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected ?User $currentUser,
        protected Carbon $dateFrom,
        protected Carbon $dateTo,
    ) {}

    public function query(): Builder
    {
        return SantriPayment::query()
            ->visibleTo($this->currentUser)
            ->with(['invoice', 'santri', 'recorder'])
            ->paidBetween($this->dateFrom, $this->dateTo)
            ->latest('paid_at');
    }

    public function headings(): array
    {
        return [
            'Tanggal Bayar',
            'Nomor Invoice',
            'Judul Tagihan',
            'Nama Santri',
            'NIS',
            'Metode Pembayaran',
            'Nomor Referensi',
            'Nominal (Rp)',
            'Dicatat Oleh',
            'Catatan',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->paid_at?->format('Y-m-d H:i:s'),
            $payment->invoice?->invoice_number,
            $payment->invoice?->title,
            $payment->santri?->full_name,
            $payment->santri?->nis,
            Str::headline($payment->payment_method),
            $payment->reference_number,
            number_format($payment->amount / 100, 2, '.', ''),
            $payment->recorder?->name ?? 'System',
            $payment->note,
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
        return 'laporan-pembayaran-'.now()->format('Ymd-His').'.xlsx';
    }
}
