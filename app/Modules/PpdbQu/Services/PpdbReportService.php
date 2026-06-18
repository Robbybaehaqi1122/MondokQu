<?php

namespace App\Modules\PpdbQu\Services;

use App\Modules\PpdbQu\Models\PpdbGelombang;
use App\Modules\PpdbQu\Models\PpdbPendaftaran;

class PpdbReportService
{
    public function summary(int $tenantId): array
    {
        $totalGelombang = PpdbGelombang::withoutTenantScope()
            ->where('tenant_id', $tenantId)->count();

        $gelombangAktif = PpdbGelombang::withoutTenantScope()
            ->where('tenant_id', $tenantId)->where('status', 'aktif')->count();

        $totalPendaftar = PpdbPendaftaran::withoutTenantScope()
            ->where('tenant_id', $tenantId)->count();

        $menunggu = PpdbPendaftaran::withoutTenantScope()
            ->where('tenant_id', $tenantId)->where('status', 'menunggu')->count();

        $diterima = PpdbPendaftaran::withoutTenantScope()
            ->where('tenant_id', $tenantId)->where('status', 'diterima')->count();

        $daftarUlang = PpdbPendaftaran::withoutTenantScope()
            ->where('tenant_id', $tenantId)->where('status', 'daftar_ulang')->count();

        return compact('totalGelombang', 'gelombangAktif', 'totalPendaftar', 'menunggu', 'diterima', 'daftarUlang');
    }

    public function statPerGelombang(int $tenantId): array
    {
        return PpdbGelombang::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->withCount(['pendaftarans'])
            ->orderBy('tanggal_mulai')
            ->get()
            ->toArray();
    }
}
