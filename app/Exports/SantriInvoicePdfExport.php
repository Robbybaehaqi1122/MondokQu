<?php

namespace App\Exports;

use App\Models\SantriInvoice;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

class SantriInvoicePdfExport
{
    public function __construct(
        protected ?User $currentUser,
        protected string $search = '',
        protected string $status = '',
        protected string $santriId = '',
    ) {}

    public function download(): Response
    {
        $invoices = $this->getData();

        $pdf = Pdf::loadView('exports.pdf.invoices', compact('invoices'));

        return $pdf->download($this->filename());
    }

    public function store(string $path): string
    {
        $invoices = $this->getData();

        $pdf = Pdf::loadView('exports.pdf.invoices', compact('invoices'));
        $pdf->save($path);

        return $path;
    }

    public function filename(): string
    {
        return 'tagihan-santri-'.now()->format('Ymd-His').'.pdf';
    }

    public function rowCount(): int
    {
        return $this->query()->count();
    }

    protected function getData(): Collection
    {
        return $this->query()->get();
    }

    protected function query()
    {
        return SantriInvoice::query()
            ->withoutTenantScope()
            ->visibleTo($this->currentUser)
            ->withFilters($this->search, $this->status, $this->santriId)
            ->with('santri')
            ->orderByRaw('CASE WHEN status = ? THEN 1 ELSE 0 END', [SantriInvoice::STATUS_PAID])
            ->orderBy('due_date');
    }
}
