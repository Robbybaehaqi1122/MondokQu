<?php

namespace App\Modules\KitabQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KitabQu\Models\Kitab;
use App\Modules\KitabQu\Models\KitabSetoran;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitabQuDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $totalKitab = 0;
        $totalSetoran = 0;
        $totalSantri = 0;
        $pendingReview = 0;
        $recentSetorans = collect();
        $kitabPerKategori = collect();

        if ($tenantId) {
            $totalKitab = Kitab::withoutTenantScope()->where('tenant_id', $tenantId)->count();
            $totalSetoran = KitabSetoran::withoutTenantScope()->where('tenant_id', $tenantId)->count();
            $totalSantri = Santri::query()->visibleTo(auth()->user())->count();
            $pendingReview = KitabSetoran::withoutTenantScope()
                ->where('tenant_id', $tenantId)
                ->where('status', KitabSetoran::STATUS_PENDING)
                ->count();

            $recentSetorans = KitabSetoran::withoutTenantScope()
                ->where('tenant_id', $tenantId)
                ->with(['santri', 'kitab'])
                ->latest()
                ->limit(10)
                ->get();

            $kitabPerKategori = Kitab::withoutTenantScope()
                ->where('tenant_id', $tenantId)
                ->with('kategori')
                ->get()
                ->groupBy(fn ($k) => $k->kategori?->nama ?? 'Tanpa Kategori')
                ->map(fn ($items, $key) => ['kategori' => $key, 'total' => $items->count()])
                ->values();
        }

        return view('modules.kitab-qu.dashboard', compact(
            'totalKitab', 'totalSetoran', 'totalSantri', 'pendingReview',
            'recentSetorans', 'kitabPerKategori'
        ));
    }
}
