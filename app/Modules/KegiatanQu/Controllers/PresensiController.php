<?php

namespace App\Modules\KegiatanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KegiatanQu\Models\KegiatanPertemuan;
use App\Modules\KegiatanQu\Models\KegiatanPresensi;
use App\Modules\KegiatanQu\Requests\StorePresensiRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PresensiController extends Controller
{
    public function store(StorePresensiRequest $request, KegiatanPertemuan $kegiatanPertemuan): RedirectResponse
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        if ($kegiatanPertemuan->tenant_id !== $tenantId) {
            abort(403);
        }

        foreach ($request->presensi as $item) {
            KegiatanPresensi::withoutTenantScope()->updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'pertemuan_id' => $kegiatanPertemuan->id,
                    'santri_id' => $item['santri_id'],
                ],
                [
                    'status' => $item['status'],
                    'catatan' => $item['catatan'] ?? null,
                    'diisi_oleh' => $user->id,
                ]
            );
        }

        activity()->log('Mengisi presensi pertemuan: ' . $kegiatanPertemuan->id);

        return redirect()->route('kegiatan.pertemuan.show', $kegiatanPertemuan)
            ->with('success', 'Presensi berhasil disimpan.');
    }

    public function update(StorePresensiRequest $request, KegiatanPertemuan $kegiatanPertemuan): RedirectResponse
    {
        return $this->store($request, $kegiatanPertemuan);
    }
}
