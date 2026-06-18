<?php

namespace App\Modules\PerpustakaanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PerpustakaanQu\Models\PerpustakaanKategori;
use App\Modules\PerpustakaanQu\Models\PerpustakaanKitab;
use App\Modules\PerpustakaanQu\Models\PerpustakaanPeminjaman;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerpustakaanQuDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            return view('modules.perpustakaan-qu.dashboard', [
                'summary' => null,
                'recentPeminjamans' => collect(),
                'statsPerKategori' => [],
            ]);
        }

        $totalKitab = PerpustakaanKitab::withoutTenantScope()
            ->where('tenant_id', $tenantId)->count();
        $totalEksemplar = PerpustakaanKitab::withoutTenantScope()
            ->where('tenant_id', $tenantId)->sum('jumlah_eksemplar');
        $totalKategori = PerpustakaanKategori::withoutTenantScope()
            ->where('tenant_id', $tenantId)->count();
        $dipinjam = PerpustakaanPeminjaman::withoutTenantScope()
            ->where('tenant_id', $tenantId)->where('status', 'dipinjam')->count();

        $recentPeminjamans = PerpustakaanPeminjaman::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with(['kitab', 'santri'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $statsPerKategori = PerpustakaanKategori::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->withCount('kitabs')
            ->orderBy('nama')
            ->get()
            ->toArray();

        return view('modules.perpustakaan-qu.dashboard', compact(
            'totalKitab', 'totalEksemplar', 'totalKategori', 'dipinjam',
            'recentPeminjamans', 'statsPerKategori'
        ));
    }
}
