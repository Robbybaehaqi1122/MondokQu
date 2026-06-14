<?php

namespace App\Modules\Bendahara\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use App\Services\FinancialReportingService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BendaharaDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return $this->dashboard($request);
    }

    public function dashboard(Request $request): View
    {
        $currentUser = $request->user();

        $invoiceQuery = SantriInvoice::query()->visibleTo($currentUser);
        $paymentQuery = SantriPayment::query()->visibleTo($currentUser);

        $summary = app(FinancialReportingService::class)->invoiceSummary(clone $invoiceQuery);

        $thisMonthStart = now()->startOfMonth();
        $thisMonthEnd = now()->endOfMonth();

        $paidThisMonth = (clone $paymentQuery)
            ->paidBetween($thisMonthStart, $thisMonthEnd)
            ->sum('amount');

        $monthlyTrend = (clone $paymentQuery)
            ->selectRaw('YEAR(paid_at) as yr, MONTH(paid_at) as mo, SUM(amount) as total')
            ->where('paid_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('yr', 'mo')
            ->orderBy('yr')
            ->orderBy('mo')
            ->get();

        $trendLabels = $monthlyTrend->map(fn ($item) => Carbon::createFromDate($item->yr, $item->mo, 1)->translatedFormat('M Y'));
        $trendData = $monthlyTrend->pluck('total');

        $overdueInvoices = (clone $invoiceQuery)
            ->overdue()
            ->with('santri')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $upcomingInvoices = (clone $invoiceQuery)
            ->whereIn('status', [SantriInvoice::STATUS_PENDING, SantriInvoice::STATUS_PARTIAL])
            ->whereDate('due_date', '>=', now())
            ->whereDate('due_date', '<=', now()->addDays(7))
            ->with('santri')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $recentPayments = (clone $paymentQuery)
            ->with(['invoice', 'santri', 'recorder'])
            ->latest('paid_at')
            ->limit(10)
            ->get();

        return view('bendahara.dashboard', [
            'summary' => $summary,
            'paidThisMonth' => (int) $paidThisMonth,
            'trendLabels' => $trendLabels,
            'trendData' => $trendData,
            'overdueInvoices' => $overdueInvoices,
            'upcomingInvoices' => $upcomingInvoices,
            'recentPayments' => $recentPayments,
            'today' => now(),
        ]);
    }
}
