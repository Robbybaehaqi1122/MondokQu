<?php

namespace App\Modules\KepengurusanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KepengurusanQu\Models\Jadwal;
use App\Modules\KepengurusanQu\Models\Pengajar;
use App\Modules\KepengurusanQu\Models\Pengurus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KepengurusanQuDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $totalPengajar = 0;
        $totalPengurus = 0;
        $totalJadwal = 0;
        $recentJadwals = collect();

        if ($tenantId) {
            $totalPengajar = Pengajar::withoutTenantScope()->where('tenant_id', $tenantId)->count();
            $totalPengurus = Pengurus::withoutTenantScope()->where('tenant_id', $tenantId)->count();
            $totalJadwal = Jadwal::withoutTenantScope()->where('tenant_id', $tenantId)->count();

            $recentJadwals = Jadwal::withoutTenantScope()
                ->where('tenant_id', $tenantId)
                ->with('pengajar')
                ->latest()
                ->limit(10)
                ->get();
        }

        return view('modules.kepengurusan-qu.dashboard', compact(
            'totalPengajar', 'totalPengurus', 'totalJadwal', 'recentJadwals'
        ));
    }
}
