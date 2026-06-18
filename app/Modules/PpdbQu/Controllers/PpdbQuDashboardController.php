<?php

namespace App\Modules\PpdbQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PpdbQu\Models\PpdbPendaftaran;
use App\Modules\PpdbQu\Services\PpdbReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PpdbQuDashboardController extends Controller
{
    public function __construct(
        protected PpdbReportService $reportService
    ) {}

    public function __invoke(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            return view('modules.ppdb-qu.dashboard', [
                'summary' => [],
                'recentPendaftarans' => collect(),
                'statsPerGelombang' => [],
            ]);
        }

        $summary = $this->reportService->summary($tenantId);
        $recentPendaftarans = PpdbPendaftaran::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with(['gelombang'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $statsPerGelombang = $this->reportService->statPerGelombang($tenantId);

        return view('modules.ppdb-qu.dashboard', compact('summary', 'recentPendaftarans', 'statsPerGelombang'));
    }
}
