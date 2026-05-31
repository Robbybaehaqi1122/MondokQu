<?php

namespace App\Jobs;

use App\Actions\Santri\GenerateMonthlySantriInvoices;
use App\Models\Santri;
use App\Models\User;
use App\Notifications\BatchInvoiceNotification;
use App\Services\ActivityLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

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
        public int $amount,
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

        if ($result['created'] > 0) {
            $periodLabel = \Carbon\Carbon::createFromDate($this->periodYear, $this->periodMonth, 1)->translatedFormat('F Y');

            $guardianUserIds = Santri::query()
                ->withoutTenantScope()
                ->forTenant($this->tenantId)
                ->where('status', Santri::STATUS_ACTIVE)
                ->whereHas('guardians', fn ($q) => $q->where('status', User::STATUS_ACTIVE))
                ->with('guardians')
                ->get()
                ->flatMap(fn ($santri) => $santri->guardians->pluck('id'))
                ->unique()
                ->values()
                ->all();

            if (! empty($guardianUserIds)) {
                $guardians = User::query()->whereIn('id', $guardianUserIds)->get();

                Notification::send($guardians, new BatchInvoiceNotification(
                    count: $result['created'],
                    title: $this->title,
                    periodLabel: $periodLabel,
                    amount: $this->amount,
                    dueDate: $this->dueDate,
                ));
            }
        }
    }
}
