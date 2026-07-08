<?php

namespace App\Modules\KeuanganQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KeuanganQu\Models\CoaAccount;
use App\Modules\KeuanganQu\Models\JournalEntry;
use App\Modules\KeuanganQu\Models\JournalEntryDetail;
use App\Modules\KeuanganQu\Services\FinancialReportService;
use Illuminate\Http\Request;

class KeuanganQuDashboardController extends Controller
{
    public function __invoke(Request $request, FinancialReportService $reportService)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;
        $year = (int) ($request->get('year', now()->year));
        $month = (int) ($request->get('month', now()->month));

        if (! $tenantId) {
            return view('modules.keuangan-qu.dashboard', [
                'totalPendapatan' => 0,
                'totalBeban' => 0,
                'labaRugi' => 0,
                'saldoKas' => 0,
                'totalAkun' => 0,
                'totalPosted' => 0,
                'totalDraft' => 0,
                'jumlahJurnal' => 0,
                'jurnalTerbaru' => collect(),
                'pieData' => [],
                'year' => $year,
                'month' => $month,
            ]);
        }

        $data = $reportService->profitLoss($tenantId, $year, $month);
        $cashFlow = $reportService->cashFlow($tenantId, $year, $month);
        $trend = $reportService->monthlyTrend($tenantId, $year);
        $pie = $reportService->pieChartData($tenantId, $year, $month);

        $latestEntries = JournalEntry::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->limit(5)
            ->with(['details.coaAccount', 'creator'])
            ->get();

        $totalAkun = CoaAccount::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();

        $totalPosted = JournalEntry::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->count();

        $totalDraft = JournalEntry::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('status', 'draft')
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->count();

        $kasAccounts = CoaAccount::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('code', 'like', '1-1%')
            ->where('is_active', true)
            ->pluck('id');

        $entryIds = JournalEntry::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->pluck('id');

        $saldoKas = JournalEntryDetail::whereIn('journal_entry_id', $entryIds)
            ->whereIn('coa_account_id', $kasAccounts)
            ->get()
            ->sum(fn ($d) => $d->debit - $d->kredit);

        return view('modules.keuangan-qu.dashboard', [
            'totalPendapatan' => $data['total_pendapatan'],
            'totalBeban' => $data['total_beban'],
            'labaRugi' => $data['laba_rugi'],
            'saldoKas' => $saldoKas,
            'totalAkun' => $totalAkun,
            'totalPosted' => $totalPosted,
            'totalDraft' => $totalDraft,
            'jumlahJurnal' => $totalPosted + $totalDraft,
            'jurnalTerbaru' => $latestEntries,
            'trend' => $trend->values(),
            'pieData' => $pie,
            'year' => $year,
            'month' => $month,
        ]);
    }
}
