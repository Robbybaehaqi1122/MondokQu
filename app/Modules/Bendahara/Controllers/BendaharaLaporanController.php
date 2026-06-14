<?php

namespace App\Modules\Bendahara\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use App\Services\FinancialReportingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BendaharaLaporanController extends Controller
{
    public function __invoke(Request $request): View
    {
        $currentUser = $request->user();

        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'payment_method' => ['nullable', 'string', 'in:'.implode(',', SantriPayment::paymentMethods())],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $validated['date_to'] ?? now()->endOfMonth()->toDateString();

        $paymentQuery = SantriPayment::query()
            ->visibleTo($currentUser)
            ->paidBetween(now()->parse($dateFrom), now()->parse($dateTo)->endOfDay());

        if (! empty($validated['payment_method'])) {
            $paymentQuery->where('payment_method', $validated['payment_method']);
        }

        $invoiceQuery = SantriInvoice::query()->visibleTo($currentUser);

        $methodOptions = collect(SantriPayment::paymentMethods())->map(fn (string $method) => [
            'value' => $method,
            'label' => ucfirst($method),
        ]);

        return view('bendahara.laporan', [
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'payment_method' => $validated['payment_method'] ?? '',
            ],
            'summary' => app(FinancialReportingService::class)->invoiceSummary(clone $invoiceQuery),
            'reportSummary' => app(FinancialReportingService::class)->paymentSummary(clone $paymentQuery),
            'methodTotals' => app(FinancialReportingService::class)->paymentMethodTotals(clone $paymentQuery),
            'methodOptions' => $methodOptions,
            'payments' => (clone $paymentQuery)
                ->with(['invoice', 'santri', 'recorder'])
                ->latest('paid_at')
                ->paginate(15)
                ->withQueryString(),
            'today' => now(),
        ]);
    }
}
