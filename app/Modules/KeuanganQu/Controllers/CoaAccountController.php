<?php

namespace App\Modules\KeuanganQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KeuanganQu\Models\CoaAccount;
use App\Modules\KeuanganQu\Requests\StoreCoaAccountRequest;
use App\Modules\KeuanganQu\Requests\UpdateCoaAccountRequest;
use Illuminate\Http\Request;

class CoaAccountController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            if ($request->ajax()) {
                return response()->json(['accounts' => []]);
            }
            $accounts = collect();
            $types = CoaAccount::getTypes();
            return view('modules.keuangan-qu.coa.index', compact('accounts', 'types'));
        }

        $accounts = CoaAccount::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->parentsOnly()
            ->search($request->get('search'))
            ->orderBy('code')
            ->get();

        $types = CoaAccount::getTypes();

        if ($request->ajax()) {
            return response()->json([
                'accounts' => CoaAccount::withoutTenantScope()
                    ->where('tenant_id', $tenantId)
                    ->where('is_active', true)
                    ->orderBy('code')
                    ->get(['id', 'code', 'name', 'type']),
            ]);
        }

        return view('modules.keuangan-qu.coa.index', compact('accounts', 'types'));
    }

    public function store(StoreCoaAccountRequest $request)
    {
        if (! auth()->user()->tenant_id) {
            return back()->with('error', 'Anda tidak memiliki akses ke modul ini tanpa terhubung ke pesantren.');
        }

        $account = CoaAccount::withoutTenantScope()->create(
            $request->validated() + ['tenant_id' => auth()->user()->tenant_id]
        );

        activity()->log('Membuat akun COA: ' . $account->code . ' - ' . $account->name);

        return redirect()->route('keuangan.coa.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function update(UpdateCoaAccountRequest $request, CoaAccount $coaAccount)
    {
        abort_if($coaAccount->tenant_id !== auth()->user()->tenant_id, 403);

        $coaAccount->update($request->validated());

        activity()->log('Mengupdate akun COA: ' . $coaAccount->code . ' - ' . $coaAccount->name);

        return redirect()->route('keuangan.coa.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(CoaAccount $coaAccount)
    {
        abort_if($coaAccount->tenant_id !== auth()->user()->tenant_id, 403);

        if ($coaAccount->children()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus akun yang memiliki sub-akun.');
        }

        if ($coaAccount->journalDetails()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus akun yang sudah digunakan di jurnal.');
        }

        $coaAccount->delete();

        activity()->log('Menghapus akun COA: ' . $coaAccount->code);

        return redirect()->route('keuangan.coa.index')
            ->with('success', 'Akun berhasil dihapus.');
    }
}
