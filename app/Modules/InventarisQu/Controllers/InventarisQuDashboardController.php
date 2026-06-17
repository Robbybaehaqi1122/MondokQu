<?php

namespace App\Modules\InventarisQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\InventarisQu\Models\Aset;
use App\Modules\InventarisQu\Services\InventarisReportService;
use Illuminate\Http\Request;

class InventarisQuDashboardController extends Controller
{
    public function __invoke(Request $request, InventarisReportService $reportService)
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            return view('modules.inventaris-qu.dashboard', [
                'summary' => [],
                'asetTerbaru' => collect(),
                'peminjamanAktif' => collect(),
            ]);
        }

        $summary = $reportService->summary($tenantId);
        $asetTerbaru = Aset::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with(['kategori', 'lokasi'])
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $peminjamanAktif = \App\Modules\InventarisQu\Models\PeminjamanAset::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('status', 'dipinjam')
            ->with('aset')
            ->orderByDesc('tanggal_pinjam')
            ->limit(5)
            ->get();

        return view('modules.inventaris-qu.dashboard', compact('summary', 'asetTerbaru', 'peminjamanAktif'));
    }
}
