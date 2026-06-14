<?php

namespace Database\Seeders\Pelanggaran;

use App\Models\PelanggaranKategori;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class PelanggaranKategoriSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['nama' => 'Terlambat', 'poin' => 5, 'deskripsi' => 'Datang terlambat dari waktu yang ditentukan.'],
            ['nama' => 'Tidak Berseragam', 'poin' => 5, 'deskripsi' => 'Tidak memakai seragam yang telah ditentukan.'],
            ['nama' => 'Meninggalkan Kegiatan', 'poin' => 10, 'deskripsi' => 'Meninggalkan kegiatan tanpa izin.'],
            ['nama' => 'Membuang Sampah Sembarangan', 'poin' => 5, 'deskripsi' => 'Membuang sampah tidak pada tempatnya.'],
            ['nama' => 'Merokok', 'poin' => 20, 'deskripsi' => 'Kedapatan merokok di lingkungan pondok.'],
            ['nama' => 'Berkelahi', 'poin' => 30, 'deskripsi' => 'Terlibat perkelahian dengan santri lain.'],
            ['nama' => 'Membawa HP', 'poin' => 15, 'deskripsi' => 'Kedapatan membawa handphone tanpa izin.'],
            ['nama' => 'Bolos Kegiatan', 'poin' => 10, 'deskripsi' => 'Tidak mengikuti kegiatan tanpa keterangan.'],
            ['nama' => 'Mencontek', 'poin' => 10, 'deskripsi' => 'Kedapatan mencontek saat ujian atau evaluasi.'],
            ['nama' => 'Keluar Tanpa Izin', 'poin' => 25, 'deskripsi' => 'Keluar dari lingkungan pondok tanpa izin.'],
        ];

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            foreach ($defaults as $data) {
                PelanggaranKategori::query()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'nama' => $data['nama']],
                    array_merge($data, ['tenant_id' => $tenant->id])
                );
            }
        }
    }
}
