<?php

namespace App\Modules\KeuanganQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KeuanganQu\Models\CoaAccount;
use App\Modules\KeuanganQu\Services\FinancialReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('modules.keuangan-qu.laporan.index');
    }

    public function profitLoss(Request $request, FinancialReportService $reportService)
    {
        $tenantId = auth()->user()->tenant_id;
        $year = (int) ($request->get('year', now()->year));
        $month = (int) ($request->get('month', now()->month));

        if (! $tenantId) {
            $report = [];
            return view('modules.keuangan-qu.laporan.profit-loss', compact('report', 'year', 'month'));
        }

        $report = $reportService->profitLoss($tenantId, $year, $month);

        return view('modules.keuangan-qu.laporan.profit-loss', compact('report', 'year', 'month'));
    }

    public function cashFlow(Request $request, FinancialReportService $reportService)
    {
        $tenantId = auth()->user()->tenant_id;
        $year = (int) ($request->get('year', now()->year));
        $month = (int) ($request->get('month', now()->month));

        if (! $tenantId) {
            $report = [];
            return view('modules.keuangan-qu.laporan.cash-flow', compact('report', 'year', 'month'));
        }

        $report = $reportService->cashFlow($tenantId, $year, $month);

        return view('modules.keuangan-qu.laporan.cash-flow', compact('report', 'year', 'month'));
    }

    public function ledger(Request $request, FinancialReportService $reportService)
    {
        $tenantId = auth()->user()->tenant_id;
        $year = (int) ($request->get('year', now()->year));
        $month = $request->get('month');
        $coaAccountId = $request->get('coa_account_id');

        if (! $tenantId) {
            $entries = collect();
            $accounts = collect();
            $selectedAccount = null;
            $saldoAwal = 0;
            return view('modules.keuangan-qu.laporan.ledger', compact(
                'entries', 'accounts', 'selectedAccount', 'year', 'month', 'saldoAwal'
            ));
        }

        $details = $reportService->generalLedger($tenantId, $year, $month ? (int) $month : null, $coaAccountId ? (int) $coaAccountId : null);

        $accounts = CoaAccount::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        $selectedAccount = $coaAccountId
            ? CoaAccount::withoutTenantScope()->find($coaAccountId)
            : null;

        $entries = $details->groupBy(fn ($d) => $d->coaAccount->code . ' - ' . $d->coaAccount->name);

        $saldoAwal = 0;

        return view('modules.keuangan-qu.laporan.ledger', compact(
            'entries', 'accounts', 'selectedAccount', 'year', 'month', 'saldoAwal'
        ));
    }
}
