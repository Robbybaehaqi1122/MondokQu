<?php

namespace App\Modules\KesehatanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KesehatanImunisasi;
use App\Models\KesehatanObat;
use App\Models\KesehatanPemeriksaan;
use App\Models\KesehatanRekamMedis;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KesehatanQuLaporanController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $dateFrom = trim((string) $request->string('date_from', now()->startOfMonth()->toDateString()));
        $dateTo = trim((string) $request->string('date_to', now()->toDateString()));

        $totalPemeriksaan = KesehatanPemeriksaan::query()
            ->visibleTo($currentUser)
            ->whereBetween('tanggal_pemeriksaan', [$dateFrom, $dateTo])
            ->count();

        $totalRujukan = KesehatanPemeriksaan::query()
            ->visibleTo($currentUser)
            ->whereHas('rujukan')
            ->whereBetween('tanggal_pemeriksaan', [$dateFrom, $dateTo])
            ->count();

        $santriDenganKondisiKhusus = Santri::query()
            ->visibleTo($currentUser)
            ->whereHas('rekamMedis', function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('riwayat_penyakit')
                        ->where('riwayat_penyakit', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('alergi_obat')
                        ->where('alergi_obat', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('alergi_makanan')
                        ->where('alergi_makanan', '!=', '');
                });
            })
            ->with('rekamMedis')
            ->orderBy('full_name')
            ->get();

        $imunisasiPerSantri = KesehatanImunisasi::query()
            ->visibleTo($currentUser)
            ->with('santri')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(fn ($item) => $item->santri?->full_name ?? 'Tanpa Nama');

        $obatExpired = KesehatanObat::query()
            ->visibleTo($currentUser)
            ->whereNotNull('expired_date')
            ->where('expired_date', '<=', now()->addMonth())
            ->orderBy('expired_date')
            ->get();

        return view('modules.kesehatan-qu.laporan.index', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalPemeriksaan' => $totalPemeriksaan,
            'totalRujukan' => $totalRujukan,
            'santriDenganKondisiKhusus' => $santriDenganKondisiKhusus,
            'imunisasiPerSantri' => $imunisasiPerSantri,
            'obatExpired' => $obatExpired,
        ]);
    }
}
