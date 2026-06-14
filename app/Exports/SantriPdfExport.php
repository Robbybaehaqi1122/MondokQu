<?php

namespace App\Exports;

use App\Models\Santri;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

class SantriPdfExport
{
    public function __construct(
        protected ?User $currentUser,
        protected string $search = '',
        protected string $status = '',
        protected string $gender = '',
    ) {}

    public function download(): Response
    {
        $santris = $this->getData();

        $pdf = Pdf::loadView('exports.pdf.santri', compact('santris'));

        return $pdf->download($this->filename());
    }

    public function store(string $path): string
    {
        $santris = $this->getData();

        $pdf = Pdf::loadView('exports.pdf.santri', compact('santris'));
        $pdf->save($path);

        return $path;
    }

    public function filename(): string
    {
        return 'data-santri-'.now()->format('Ymd-His').'.pdf';
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
        return Santri::query()
            ->withoutTenantScope()
            ->visibleTo($this->currentUser)
            ->withFilters($this->search, $this->status, $this->gender)
            ->with(['guardians', 'room'])
            ->orderBy('full_name');
    }
}
