<?php

namespace App\Modules\KeuanganQu\Services;

use App\Modules\KeuanganQu\Models\CoaAccount;
use App\Modules\KeuanganQu\Models\JournalEntry;
use App\Modules\KeuanganQu\Models\JournalEntryDetail;
use Illuminate\Support\Collection;

class FinancialReportService
{
    public function profitLoss(int $tenantId, int $year, int $month): array
    {
        $pendapatanAccounts = CoaAccount::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('type', 'pendapatan')
            ->where('is_active', true)
            ->get();

        $bebanAccounts = CoaAccount::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('type', 'beban')
            ->where('is_active', true)
            ->get();

        $entryIds = JournalEntry::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->pluck('id');

        $details = JournalEntryDetail::whereIn('journal_entry_id', $entryIds)->get();

        $pendapatan = $this->groupByAccount($details, $pendapatanAccounts, 'kredit');
        $totalPendapatan = $pendapatan->sum('amount');

        $beban = $this->groupByAccount($details, $bebanAccounts, 'debit');
        $totalBeban = $beban->sum('amount');

        return [
            'pendapatan' => $pendapatan,
            'total_pendapatan' => $totalPendapatan,
            'beban' => $beban,
            'total_beban' => $totalBeban,
            'laba_rugi' => $totalPendapatan - $totalBeban,
            'year' => $year,
            'month' => $month,
        ];
    }

    public function cashFlow(int $tenantId, int $year, int $month): array
    {
        $entryIds = JournalEntry::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->pluck('id');

        $kasAccounts = CoaAccount::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('code', 'like', '1-1%')
            ->where('is_active', true)
            ->pluck('id');

        $details = JournalEntryDetail::whereIn('journal_entry_id', $entryIds)
            ->whereIn('coa_account_id', $kasAccounts)
            ->get();

        $pemasukan = $details->where('debit', '>', 0)->sum('debit');
        $pengeluaran = $details->where('kredit', '>', 0)->sum('kredit');

        return [
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'arus_bersih' => $pemasukan - $pengeluaran,
            'year' => $year,
            'month' => $month,
        ];
    }

    public function generalLedger(int $tenantId, int $year, ?int $month = null, ?int $coaAccountId = null): Collection
    {
        $query = JournalEntryDetail::query()
            ->whereHas('journalEntry', function ($q) use ($tenantId, $year, $month) {
                $q->withoutTenantScope()
                  ->where('tenant_id', $tenantId)
                  ->where('status', 'posted')
                  ->where('period_year', $year);
                if ($month) {
                    $q->where('period_month', $month);
                }
            })
            ->with(['journalEntry', 'coaAccount']);

        if ($coaAccountId) {
            $query->where('coa_account_id', $coaAccountId);
        }

        return $query->orderBy('coa_account_id')->orderBy('journal_entry_id')->get();
    }

    public function monthlyTrend(int $tenantId, int $year): Collection
    {
        $months = collect(range(1, 12));
        return $months->map(function ($month) use ($tenantId, $year) {
            $entryIds = JournalEntry::withoutTenantScope()
                ->where('tenant_id', $tenantId)
                ->where('status', 'posted')
                ->where('period_year', $year)
                ->where('period_month', $month)
                ->pluck('id');

            $details = JournalEntryDetail::whereIn('journal_entry_id', $entryIds)->get();

            $pendapatanAccounts = CoaAccount::withoutTenantScope()
                ->where('tenant_id', $tenantId)->where('type', 'pendapatan')->pluck('id');

            $bebanAccounts = CoaAccount::withoutTenantScope()
                ->where('tenant_id', $tenantId)->where('type', 'beban')->pluck('id');

            $pemasukan = $details->whereIn('coa_account_id', $pendapatanAccounts)->sum('kredit');
            $pengeluaran = $details->whereIn('coa_account_id', $bebanAccounts)->sum('debit');

            return [
                'month' => $month,
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
            ];
        });
    }

    public function pieChartData(int $tenantId, int $year, int $month): Collection
    {
        $entryIds = JournalEntry::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->pluck('id');

        return CoaAccount::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('type', 'pendapatan')
            ->where('is_active', true)
            ->get()
            ->map(function ($account) use ($entryIds) {
                $amount = JournalEntryDetail::whereIn('journal_entry_id', $entryIds)
                    ->where('coa_account_id', $account->id)
                    ->sum('kredit');
                return [
                    'label' => $account->name,
                    'amount' => (int) $amount,
                ];
            })
            ->filter(fn ($item) => $item['amount'] > 0)
            ->values();
    }

    private function groupByAccount(Collection $details, Collection $accounts, string $column): Collection
    {
        return $accounts->map(function ($account) use ($details, $column) {
            $amount = $details->where('coa_account_id', $account->id)->sum($column);
            return [
                'account' => $account,
                'amount' => (int) $amount,
            ];
        })->filter(fn ($item) => $item['amount'] > 0)->values();
    }
}
