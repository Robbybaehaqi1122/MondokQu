<?php

namespace App\Modules\Pelanggaran\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pelanggaran;
use App\Models\PelanggaranKategori;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelanggaranLaporanController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $selectedSantriId = trim((string) $request->string('santri'));
        $selectedKategoriId = trim((string) $request->string('kategori'));
        $dateFrom = trim((string) $request->string('date_from', now()->startOfMonth()->toDateString()));
        $dateTo = trim((string) $request->string('date_to', now()->toDateString()));

        $baseQuery = Pelanggaran::query()
            ->visibleTo($currentUser)
            ->with(['santri', 'kategori'])
            ->when($selectedSantriId !== '', fn ($q) => $q->where('santri_id', $selectedSantriId))
            ->when($selectedKategoriId !== '', fn ($q) => $q->where('kategori_id', $selectedKategoriId))
            ->whereDate('tanggal', '>=', $dateFrom)
            ->whereDate('tanggal', '<=', $dateTo);

        $totalPelanggaran = (clone $baseQuery)->count();
        $totalPoin = (clone $baseQuery)->sum('poin');

        $perSantri = (clone $baseQuery)
            ->selectRaw('santri_id, COUNT(*) as jumlah, SUM(poin) as total_poin')
            ->with('santri')
            ->groupBy('santri_id')
            ->orderBy('total_poin', 'desc')
            ->get();

        $perKategori = (clone $baseQuery)
            ->selectRaw('kategori_id, COUNT(*) as jumlah, SUM(poin) as total_poin')
            ->with('kategori')
            ->groupBy('kategori_id')
            ->orderBy('jumlah', 'desc')
            ->get();

        $dailyStats = (clone $baseQuery)
            ->selectRaw('DATE(tanggal) as tgl, COUNT(*) as jumlah, SUM(poin) as total_poin')
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get();

        $santriOptions = Santri::query()
            ->visibleTo($currentUser)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nis']);

        $kategoriOptions = PelanggaranKategori::query()
            ->visibleTo($currentUser)
            ->orderBy('nama')
            ->get();

        return view('modules.pelanggaran.laporan.index', [
            'santriOptions' => $santriOptions,
            'kategoriOptions' => $kategoriOptions,
            'perSantri' => $perSantri,
            'perKategori' => $perKategori,
            'dailyStats' => $dailyStats,
            'totalPelanggaran' => $totalPelanggaran,
            'totalPoin' => $totalPoin,
            'filters' => [
                'santri' => $selectedSantriId,
                'kategori' => $selectedKategoriId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }
}
