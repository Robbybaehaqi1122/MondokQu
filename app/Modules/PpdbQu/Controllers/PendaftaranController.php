<?php

namespace App\Modules\PpdbQu\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Modules\PpdbQu\Models\PpdbGelombang;
use App\Modules\PpdbQu\Models\PpdbPendaftaran;
use App\Modules\PpdbQu\Requests\StorePendaftaranRequest;
use App\Modules\PpdbQu\Requests\UpdatePendaftaranRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendaftaranController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $pendaftarans = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $gelombangs = collect();
            return view('modules.ppdb-qu.pendaftaran.index', compact('pendaftarans', 'gelombangs'));
        }

        $search = trim((string) $request->string('q'));
        $gelombangId = $request->get('gelombang_id');
        $status = $request->get('status');

        $pendaftarans = PpdbPendaftaran::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with(['gelombang'])
            ->when($search !== '', fn ($q) => $q->where('nama_lengkap', 'like', "%{$search}%")
                ->orWhere('nomor_pendaftaran', 'like', "%{$search}%"))
            ->when($gelombangId, fn ($q) => $q->where('gelombang_id', $gelombangId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $gelombangs = PpdbGelombang::withoutTenantScope()
            ->where('tenant_id', $tenantId)->orderBy('nama')->get();

        return view('modules.ppdb-qu.pendaftaran.index', compact('pendaftarans', 'gelombangs'));
    }

    public function show(PpdbPendaftaran $ppdbPendaftaran): View
    {
        if ($ppdbPendaftaran->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $ppdbPendaftaran->load(['gelombang', 'seleksis.penguji']);

        return view('modules.ppdb-qu.pendaftaran.show', [
            'pendaftaran' => $ppdbPendaftaran,
        ]);
    }

    public function update(UpdatePendaftaranRequest $request, PpdbPendaftaran $ppdbPendaftaran): RedirectResponse
    {
        $user = auth()->user();
        if ($ppdbPendaftaran->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $data = $request->validated();

        if ($data['status'] === 'diterima') {
            $data['diterima_at'] = now();
            $data['diproses_oleh'] = $user->id;
        }

        $ppdbPendaftaran->update($data);

        activity()->log('Mengupdate status PPDB: ' . $ppdbPendaftaran->nomor_pendaftaran . ' -> ' . $data['status']);

        return redirect()->route('ppdb.pendaftaran.show', $ppdbPendaftaran)
            ->with('success', 'Status pendaftaran berhasil diperbarui.');
    }

    public function destroy(PpdbPendaftaran $ppdbPendaftaran): RedirectResponse
    {
        if ($ppdbPendaftaran->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $ppdbPendaftaran->seleksis()->delete();
        $ppdbPendaftaran->delete();

        return redirect()->route('ppdb.pendaftaran.index')
            ->with('success', 'Pendaftaran berhasil dihapus.');
    }

    public function daftarUlang(PpdbPendaftaran $ppdbPendaftaran): RedirectResponse
    {
        $user = auth()->user();
        if ($ppdbPendaftaran->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        if ($ppdbPendaftaran->status !== 'diterima') {
            return back()->with('error', 'Hanya pendaftar dengan status diterima yang bisa daftar ulang.');
        }

        $existingSantri = Santri::withoutTenantScope()
            ->where('tenant_id', $ppdbPendaftaran->tenant_id)
            ->where('full_name', $ppdbPendaftaran->nama_lengkap)
            ->first();

        if ($existingSantri) {
            $ppdbPendaftaran->update([
                'status' => 'daftar_ulang',
                'daftar_ulang_at' => now(),
            ]);

            return redirect()->route('ppdb.pendaftaran.show', $ppdbPendaftaran)
                ->with('success', 'Calon santri sudah terdaftar sebagai santri aktif. Status diperbarui.');
        }

        $santri = Santri::withoutTenantScope()->create([
            'tenant_id' => $ppdbPendaftaran->tenant_id,
            'full_name' => $ppdbPendaftaran->nama_lengkap,
            'nis' => Santri::generateNis($ppdbPendaftaran->tenant_id),
            'gender' => $ppdbPendaftaran->jenis_kelamin,
            'birth_place' => $ppdbPendaftaran->tempat_lahir,
            'birth_date' => $ppdbPendaftaran->tanggal_lahir,
            'address' => $ppdbPendaftaran->alamat,
            'phone' => $ppdbPendaftaran->no_hp,
            'email' => $ppdbPendaftaran->email,
            'school_origin' => $ppdbPendaftaran->asal_sekolah,
            'father_name' => $ppdbPendaftaran->nama_ayah,
            'mother_name' => $ppdbPendaftaran->nama_ibu,
            'parent_phone' => $ppdbPendaftaran->no_hp_orangtua,
            'is_active' => true,
        ]);

        $ppdbPendaftaran->update([
            'status' => 'daftar_ulang',
            'daftar_ulang_at' => now(),
        ]);

        activity()->log('PPDB daftar ulang: ' . $ppdbPendaftaran->nomor_pendaftaran . ' -> Santri ' . $santri->nis);

        return redirect()->route('ppdb.pendaftaran.show', $ppdbPendaftaran)
            ->with('success', 'Calon santri berhasil didaftarkan sebagai santri aktif (NIS: ' . $santri->nis . ').');
    }
}
