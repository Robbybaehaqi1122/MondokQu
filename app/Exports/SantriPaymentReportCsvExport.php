<?php

namespace App\Exports;

use App\Models\SantriPayment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SantriPaymentReportCsvExport
{
    /**
     * Download filtered payment report as CSV.
     */
    public function download(?User $currentUser, Carbon $dateFrom, Carbon $dateTo): StreamedResponse
    {
        $filename = 'laporan-pembayaran-'
            .$dateFrom->toDateString()
            .'-sd-'
            .$dateTo->toDateString()
            .'.csv';

        return response()->streamDownload(
            fn () => $this->write($currentUser, $dateFrom, $dateTo),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    protected function write(?User $currentUser, Carbon $dateFrom, Carbon $dateTo): void
    {
        $handle = fopen('php://output', 'w');

        if ($handle === false) {
            return;
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, [
            'Tanggal Bayar',
            'Nomor Invoice',
            'Judul Tagihan',
            'Nama Santri',
            'NIS',
            'Metode Pembayaran',
            'Nomor Referensi',
            'Nominal',
            'Dicatat Oleh',
            'Catatan',
        ]);

        SantriPayment::query()
            ->visibleTo($currentUser)
            ->with(['invoice', 'santri', 'recorder'])
            ->paidBetween($dateFrom, $dateTo)
            ->latest('paid_at')
            ->chunk(500, function (Collection $payments) use ($handle): void {
                foreach ($payments as $payment) {
                    fputcsv($handle, [
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
                    ]);
                }
            });

        fclose($handle);
    }
}
