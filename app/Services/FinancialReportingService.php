<?php

namespace App\Services;

use App\Models\SantriInvoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FinancialReportingService
{
    /**
     * Build invoice summary stats for the given tenant-scoped query.
     */
    public function invoiceSummary(Builder $query): array
    {
        $totalAmount = (clone $query)->sum('amount');
        $paidAmount = (clone $query)->sum('paid_amount');

        return [
            'total_invoices' => (clone $query)->count(),
            'paid_invoices' => (clone $query)->where('status', SantriInvoice::STATUS_PAID)->count(),
            'pending_invoices' => (clone $query)->where('status', SantriInvoice::STATUS_PENDING)->count(),
            'partial_invoices' => (clone $query)->where('status', SantriInvoice::STATUS_PARTIAL)->count(),
            'overdue_invoices' => (clone $query)->overdue()->count(),
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'outstanding_amount' => max(0, (int) $totalAmount - (int) $paidAmount),
            'overdue_amount' => (clone $query)
                ->overdue()
                ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')
                ->value('total') ?? 0,
        ];
    }

    /**
     * Sum payments within a date range for the given tenant-scoped query.
     */
    public function paidBetween(Builder $query, Carbon $dateFrom, Carbon $dateTo): int
    {
        return (int) (clone $query)
            ->paidBetween($dateFrom, $dateTo)
            ->sum('amount');
    }

    /**
     * Build high-level payment report summary for the given filtered query.
     */
    public function paymentSummary(Builder $query): array
    {
        return [
            'received' => (int) (clone $query)->sum('amount'),
            'transactions' => (clone $query)->count(),
            'average_payment' => (int) ((clone $query)->avg('amount') ?? 0),
        ];
    }

    /**
     * Build grouped payment method totals for the given filtered query.
     */
    public function paymentMethodTotals(Builder $query): Collection
    {
        return (clone $query)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();
    }
}
