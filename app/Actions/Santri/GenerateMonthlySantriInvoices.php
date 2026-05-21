<?php

namespace App\Actions\Santri;

use App\Models\Santri;
use App\Models\SantriInvoice;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateMonthlySantriInvoices
{
    /**
     * Generate tenant-scoped monthly invoices for active santri.
     *
     * @return array{created: int, skipped: int, eligible: int, tenant_id: int, period_month: int, period_year: int}
     */
    public function handle(
        int $tenantId,
        string $title,
        int $periodMonth,
        int $periodYear,
        string $dueDate,
        float $amount,
        ?string $notes = null,
        ?int $createdBy = null,
        bool $dryRun = false
    ): array {
        return DB::transaction(function () use ($tenantId, $title, $periodMonth, $periodYear, $dueDate, $amount, $notes, $createdBy, $dryRun): array {
            $tenant = Tenant::query()
                ->lockForUpdate()
                ->findOrFail($tenantId);

            $eligible = Santri::query()
                ->withoutTenantScope()
                ->forTenant($tenant)
                ->where('status', Santri::STATUS_ACTIVE)
                ->count();

            $existingSantriIds = SantriInvoice::query()
                ->withoutTenantScope()
                ->forTenant($tenant)
                ->where('title', $title)
                ->where('period_month', $periodMonth)
                ->where('period_year', $periodYear)
                ->pluck('santri_id')
                ->all();

            $skipped = count($existingSantriIds);
            $created = 0;

            if ($dryRun) {
                return [
                    'created' => max(0, $eligible - $skipped),
                    'skipped' => $skipped,
                    'eligible' => $eligible,
                    'tenant_id' => $tenant->id,
                    'period_month' => $periodMonth,
                    'period_year' => $periodYear,
                ];
            }

            Santri::query()
                ->withoutTenantScope()
                ->forTenant($tenant)
                ->where('status', Santri::STATUS_ACTIVE)
                ->whereNotIn('id', $existingSantriIds ?: [0])
                ->orderBy('id')
                ->chunkById(250, function ($santris) use ($tenant, $title, $periodMonth, $periodYear, $dueDate, $amount, $notes, $createdBy, &$created): void {
                    foreach ($santris as $santri) {
                        SantriInvoice::query()
                            ->withoutTenantScope()
                            ->create([
                                'tenant_id' => $tenant->id,
                                'santri_id' => $santri->id,
                                'invoice_number' => $this->generateInvoiceNumber($tenant->id, $periodMonth, $periodYear),
                                'title' => $title,
                                'period_month' => $periodMonth,
                                'period_year' => $periodYear,
                                'due_date' => $dueDate,
                                'amount' => $amount,
                                'paid_amount' => 0,
                                'status' => SantriInvoice::STATUS_PENDING,
                                'notes' => $notes,
                                'created_by' => $createdBy,
                            ]);

                        $created++;
                    }
                }, 'id');

            return [
                'created' => $created,
                'skipped' => $skipped,
                'eligible' => $eligible,
                'tenant_id' => $tenant->id,
                'period_month' => $periodMonth,
                'period_year' => $periodYear,
            ];
        });
    }

    protected function generateInvoiceNumber(int $tenantId, int $periodMonth, int $periodYear): string
    {
        $periodKey = sprintf('%04d%02d', $periodYear, $periodMonth);
        $prefix = 'INV-'.$periodKey.'-'.Str::padLeft((string) $tenantId, 3, '0');
        $nextNumber = SantriInvoice::query()
            ->withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('invoice_number', 'like', $prefix.'-%')
            ->count() + 1;

        return $prefix.'-'.Str::padLeft((string) $nextNumber, 4, '0');
    }
}
