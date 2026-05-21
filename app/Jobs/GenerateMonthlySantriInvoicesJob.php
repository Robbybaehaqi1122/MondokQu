<?php

namespace App\Jobs;

use App\Actions\Santri\GenerateMonthlySantriInvoices;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateMonthlySantriInvoicesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $tenantId,
        public string $title,
        public int $periodMonth,
        public int $periodYear,
        public string $dueDate,
        public float $amount,
        public ?string $notes = null,
        public ?int $createdBy = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(GenerateMonthlySantriInvoices $generator, ActivityLogger $activityLogger): void
    {
        $result = $generator->handle(
            tenantId: $this->tenantId,
            title: $this->title,
            periodMonth: $this->periodMonth,
            periodYear: $this->periodYear,
            dueDate: $this->dueDate,
            amount: $this->amount,
            notes: $this->notes,
            createdBy: $this->createdBy
        );

        $activityLogger->log(
            action: 'santri_monthly_invoices_generated',
            actor: $this->createdBy ? User::query()->find($this->createdBy) : null,
            description: 'Tagihan bulanan santri dibuat otomatis.',
            properties: [
                'tenant_id' => $this->tenantId,
                'title' => $this->title,
                'period_month' => $this->periodMonth,
                'period_year' => $this->periodYear,
                'due_date' => $this->dueDate,
                'amount' => $this->amount,
                'created' => $result['created'],
                'skipped' => $result['skipped'],
                'eligible' => $result['eligible'],
            ]
        );
    }
}
