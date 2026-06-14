<?php

namespace App\Exports;

use App\Models\SantriPayment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SantriPaymentReportPdfExport
{
    public function __construct(
        protected ?User $currentUser,
        protected Carbon $dateFrom,
        protected Carbon $dateTo,
    ) {}

    public function download(): Response
    {
        $payments = $this->getData();
        $dateFrom = $this->dateFrom;
        $dateTo = $this->dateTo;

        $pdf = Pdf::loadView('exports.pdf.payment-report', compact('payments', 'dateFrom', 'dateTo'));

        return $pdf->download($this->filename());
    }

    public function store(string $path): string
    {
        $payments = $this->getData();
        $dateFrom = $this->dateFrom;
        $dateTo = $this->dateTo;

        $pdf = Pdf::loadView('exports.pdf.payment-report', compact('payments', 'dateFrom', 'dateTo'));
        $pdf->save($path);

        return $path;
    }

    public function rowCount(): int
    {
        return $this->query()->count();
    }

    public function filename(): string
    {
        return 'laporan-pembayaran-'
            .$this->dateFrom->toDateString()
            .'-sd-'
            .$this->dateTo->toDateString()
            .'.pdf';
    }

    protected function getData(): Collection
    {
        return $this->query()->get();
    }

    protected function query()
    {
        return SantriPayment::query()
            ->visibleTo($this->currentUser)
            ->with(['invoice', 'santri', 'recorder'])
            ->paidBetween($this->dateFrom, $this->dateTo)
            ->latest('paid_at');
    }
}
