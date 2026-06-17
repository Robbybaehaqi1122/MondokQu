<?php

namespace App\Modules\InventarisQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\InventarisQu\Services\InventarisReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index()
    {
        return view('modules.inventaris-qu.laporan.index');
    }

    public function perLokasi(Request $request, InventarisReportService $reportService)
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $data = collect();
            return view('modules.inventaris-qu.laporan.per-lokasi', compact('data'));
        }

        $data = $reportService->perLokasi($tenantId);

        return view('modules.inventaris-qu.laporan.per-lokasi', compact('data'));
    }

    public function perKategori(Request $request, InventarisReportService $reportService)
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $data = collect();
            return view('modules.inventaris-qu.laporan.per-kategori', compact('data'));
        }

        $data = $reportService->perKategori($tenantId);

        return view('modules.inventaris-qu.laporan.per-kategori', compact('data'));
    }

    public function kondisi(Request $request, InventarisReportService $reportService)
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $data = collect();
            return view('modules.inventaris-qu.laporan.kondisi', compact('data'));
        }

        $data = $reportService->kondisiBreakdown($tenantId);

        return view('modules.inventaris-qu.laporan.kondisi', compact('data'));
    }
}
