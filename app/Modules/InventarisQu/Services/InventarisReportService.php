<?php

namespace App\Modules\InventarisQu\Services;

use App\Modules\InventarisQu\Models\Aset;
use App\Modules\InventarisQu\Models\KategoriAset;
use App\Modules\InventarisQu\Models\LokasiAset;
use App\Modules\InventarisQu\Models\PeminjamanAset;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventarisReportService
{
    public function summary(int $tenantId): array
    {
        $totalAset = Aset::withoutTenantScope()->where('tenant_id', $tenantId)->count();
        $totalNilai = Aset::withoutTenantScope()->where('tenant_id', $tenantId)->sum('harga_perolehan');
        $asetBaik = Aset::withoutTenantScope()->where('tenant_id', $tenantId)->where('kondisi', 'baik')->count();
        $asetRusak = Aset::withoutTenantScope()->where('tenant_id', $tenantId)->whereIn('kondisi', ['rusak_ringan', 'rusak_berat'])->count();
        $asetHilang = Aset::withoutTenantScope()->where('tenant_id', $tenantId)->where('kondisi', 'hilang')->count();
        $dipinjam = PeminjamanAset::withoutTenantScope()->where('tenant_id', $tenantId)->where('status', 'dipinjam')->count();

        return compact('totalAset', 'totalNilai', 'asetBaik', 'asetRusak', 'asetHilang', 'dipinjam');
    }

    public function perLokasi(int $tenantId): Collection
    {
        return LokasiAset::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->withCount('asets')
            ->withSum('asets', 'harga_perolehan')
            ->orderByDesc('asets_count')
            ->get();
    }

    public function perKategori(int $tenantId): Collection
    {
        return KategoriAset::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->withCount('asets')
            ->withSum('asets', 'harga_perolehan')
            ->orderByDesc('asets_count')
            ->get();
    }

    public function kondisiBreakdown(int $tenantId): Collection
    {
        return collect(Aset::KONDISI)->map(function ($label, $key) use ($tenantId) {
            $count = Aset::withoutTenantScope()
                ->where('tenant_id', $tenantId)
                ->where('kondisi', $key)
                ->count();
            return ['kondisi' => $label, 'key' => $key, 'total' => $count];
        });
    }
}
