<?php

namespace App\Modules\KeuanganQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KeuanganQu\Models\Budget;
use App\Modules\KeuanganQu\Models\CoaAccount;
use App\Modules\KeuanganQu\Requests\StoreBudgetRequest;
use App\Modules\KeuanganQu\Requests\UpdateBudgetRequest;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $budgets = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $accounts = collect();
            $year = (int) ($request->get('year', now()->year));
            $month = (int) ($request->get('month', now()->month));
            return view('modules.keuangan-qu.anggaran.index', compact('budgets', 'accounts', 'year', 'month'));
        }

        $year = (int) ($request->get('year', now()->year));
        $month = (int) ($request->get('month', now()->month));

        $budgets = Budget::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->period($year, $month)
            ->with('coaAccount')
            ->orderBy('coa_account_id')
            ->paginate(20);

        $accounts = CoaAccount::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereIn('type', ['pendapatan', 'beban'])
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        return view('modules.keuangan-qu.anggaran.index', compact('budgets', 'accounts', 'year', 'month'));
    }

    public function store(StoreBudgetRequest $request)
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            return back()->with('error', 'Anda tidak memiliki akses ke modul ini tanpa terhubung ke pesantren.');
        }

        $exists = Budget::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('coa_account_id', $request->coa_account_id)
            ->where('period_month', $request->period_month)
            ->where('period_year', $request->period_year)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anggaran untuk akun dan periode tersebut sudah ada.');
        }

        Budget::withoutTenantScope()->create(
            $request->validated() + ['tenant_id' => $tenantId]
        );

        activity()->log('Membuat anggaran untuk akun #' . $request->coa_account_id);

        return redirect()->route('keuangan.anggaran.index')
            ->with('success', 'Anggaran berhasil ditambahkan.');
    }

    public function update(UpdateBudgetRequest $request, Budget $budget)
    {
        abort_if($budget->tenant_id !== auth()->user()->tenant_id, 403);

        $budget->update($request->validated());

        activity()->log('Mengupdate anggaran #' . $budget->id);

        return redirect()->route('keuangan.anggaran.index')
            ->with('success', 'Anggaran berhasil diperbarui.');
    }

    public function destroy(Budget $budget)
    {
        abort_if($budget->tenant_id !== auth()->user()->tenant_id, 403);

        $budget->delete();

        activity()->log('Menghapus anggaran #' . $budget->id);

        return redirect()->route('keuangan.anggaran.index')
            ->with('success', 'Anggaran berhasil dihapus.');
    }
}
