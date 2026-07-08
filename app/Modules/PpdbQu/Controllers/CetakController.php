<?php

namespace App\Modules\PpdbQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PpdbQu\Models\PpdbPendaftaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class CetakController extends Controller
{
    public function formulir(PpdbPendaftaran $ppdbPendaftaran): Response
    {
        if ($ppdbPendaftaran->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $ppdbPendaftaran->loadMissing('gelombang');
        $tenant = auth()->user()->tenant;

        $pdf = Pdf::loadView('modules.ppdb-qu.cetak.formulir', [
            'pendaftaran' => $ppdbPendaftaran,
            'tenant' => $tenant,
        ]);

        return $pdf->download('formulir-ppdb-' . $ppdbPendaftaran->nomor_pendaftaran . '.pdf');
    }

    public function kartuPeserta(PpdbPendaftaran $ppdbPendaftaran): Response
    {
        if ($ppdbPendaftaran->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $ppdbPendaftaran->loadMissing('gelombang');
        $tenant = auth()->user()->tenant;

        $pdf = Pdf::loadView('modules.ppdb-qu.cetak.kartu-peserta', [
            'pendaftaran' => $ppdbPendaftaran,
            'tenant' => $tenant,
        ]);

        return $pdf->download('kartu-peserta-ppdb-' . $ppdbPendaftaran->nomor_pendaftaran . '.pdf');
    }

    public function suratTerima(PpdbPendaftaran $ppdbPendaftaran): Response
    {
        if ($ppdbPendaftaran->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($ppdbPendaftaran->status !== 'diterima' && $ppdbPendaftaran->status !== 'daftar_ulang') {
            abort(403, 'Pendaftar belum diterima.');
        }

        $ppdbPendaftaran->loadMissing('gelombang');
        $tenant = auth()->user()->tenant;

        $pdf = Pdf::loadView('modules.ppdb-qu.cetak.surat-terima', [
            'pendaftaran' => $ppdbPendaftaran,
            'tenant' => $tenant,
        ]);

        return $pdf->download('surat-keterangan-diterima-' . $ppdbPendaftaran->nomor_pendaftaran . '.pdf');
    }
}
