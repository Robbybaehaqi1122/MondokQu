<?php

namespace App\Modules\KegiatanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KegiatanQu\Models\Kegiatan;
use App\Modules\KegiatanQu\Models\KegiatanNilai;
use App\Modules\KegiatanQu\Models\KegiatanPresensi;
use App\Modules\KegiatanQu\Services\KegiatanReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function __construct(
        protected KegiatanReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $kegiatans = collect();
        $summary = [];

        if ($tenantId) {
            $kegiatans = Kegiatan::withoutTenantScope()
                ->where('tenant_id', $tenantId)
                ->orderBy('nama')
                ->get();

            $summary = $this->reportService->summary($tenantId);
        }

        return view('modules.kegiatan-qu.laporan.index', compact('kegiatans', 'summary'));
    }

    public function kehadiran(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            return view('modules.kegiatan-qu.laporan.kehadiran', [
                'kegiatans' => collect(),
                'selectedKegiatan' => null,
                'rekap' => [],
            ]);
        }

        $kegiatans = Kegiatan::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->orderBy('nama')
            ->get();

        $kegiatanId = $request->get('kegiatan_id');
        $rekap = [];

        if ($kegiatanId) {
            $rekap = $this->reportService->kehadiranPerKegiatan($tenantId, $kegiatanId);
        }

        $selectedKegiatan = $kegiatanId ? Kegiatan::withoutTenantScope()->find($kegiatanId) : null;

        return view('modules.kegiatan-qu.laporan.kehadiran', compact('kegiatans', 'selectedKegiatan', 'rekap'));
    }

    public function nilai(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            return view('modules.kegiatan-qu.laporan.nilai', [
                'kegiatans' => collect(),
                'selectedKegiatan' => null,
                'nilaisByKegiatan' => collect(),
            ]);
        }

        $kegiatans = Kegiatan::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->orderBy('nama')
            ->get();

        $kegiatanId = $request->get('kegiatan_id');
        $nilaisByKegiatan = collect();
        $selectedKegiatan = null;

        if ($kegiatanId) {
            $selectedKegiatan = Kegiatan::withoutTenantScope()->find($kegiatanId);
            $result = $this->reportService->rekapNilai($tenantId, $kegiatanId);
            $nilaisByKegiatan = collect($result['nilais']);
        }

        return view('modules.kegiatan-qu.laporan.nilai', compact('kegiatans', 'selectedKegiatan', 'nilaisByKegiatan'));
    }
}
