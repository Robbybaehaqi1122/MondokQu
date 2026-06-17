<?php

namespace App\Modules\KegiatanQu\Services;

use App\Modules\KegiatanQu\Models\Kegiatan;
use App\Modules\KegiatanQu\Models\KegiatanNilai;
use App\Modules\KegiatanQu\Models\KegiatanPendaftaran;
use App\Modules\KegiatanQu\Models\KegiatanPresensi;
use App\Modules\KegiatanQu\Models\KegiatanPertemuan;

class KegiatanReportService
{
    public function summary(int $tenantId): array
    {
        $totalKegiatan = Kegiatan::withoutTenantScope()
            ->where('tenant_id', $tenantId)->count();

        $kegiatanAktif = Kegiatan::withoutTenantScope()
            ->where('tenant_id', $tenantId)->where('status', 'aktif')->count();

        $totalPendaftar = KegiatanPendaftaran::withoutTenantScope()
            ->where('tenant_id', $tenantId)->count();

        $totalPertemuan = KegiatanPertemuan::withoutTenantScope()
            ->where('tenant_id', $tenantId)->count();

        $totalPresensi = KegiatanPresensi::withoutTenantScope()
            ->where('tenant_id', $tenantId)->where('status', 'hadir')->count();

        return compact('totalKegiatan', 'kegiatanAktif', 'totalPendaftar', 'totalPertemuan', 'totalPresensi');
    }

    public function kehadiranPerKegiatan(int $tenantId, int $kegiatanId): array
    {
        $pertemuans = KegiatanPertemuan::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('kegiatan_id', $kegiatanId)
            ->with('presensis')
            ->orderBy('tanggal')
            ->get();

        $totalHadir = 0;
        $totalSakit = 0;
        $totalIzin = 0;
        $totalAlpha = 0;
        $totalPertemuan = $pertemuans->count();

        foreach ($pertemuans as $pertemuan) {
            foreach ($pertemuan->presensis as $p) {
                match ($p->status) {
                    'hadir' => $totalHadir++,
                    'sakit' => $totalSakit++,
                    'izin' => $totalIzin++,
                    'alpha' => $totalAlpha++,
                    default => null,
                };
            }
        }

        $grandTotalPresensi = $totalHadir + $totalSakit + $totalIzin + $totalAlpha;

        return compact('pertemuans', 'totalHadir', 'totalSakit', 'totalIzin', 'totalAlpha', 'totalPertemuan', 'grandTotalPresensi');
    }

    public function rekapNilai(int $tenantId, ?int $kegiatanId = null): array
    {
        $query = KegiatanNilai::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with(['santri', 'kegiatan']);

        if ($kegiatanId) {
            $query->where('kegiatan_id', $kegiatanId);
        }

        $nilais = $query->orderBy('aspek')->get()->groupBy(fn ($item) => $item->kegiatan?->nama ?? 'Tanpa Kegiatan');

        return compact('nilais');
    }
}
