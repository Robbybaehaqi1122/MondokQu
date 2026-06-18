<?php

namespace App\Modules\PerpustakaanQu\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerpustakaanKitab extends Model
{
    use BelongsToTenant;

    protected $table = 'perpustakaan_kitabs';

    protected $fillable = [
        'tenant_id',
        'kategori_id',
        'judul',
        'pengarang',
        'penerbit',
        'tahun_terbit',
        'isbn',
        'lokasi_rak',
        'jumlah_eksemplar',
        'tersedia',
        'kondisi',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'tahun_terbit' => 'integer',
            'jumlah_eksemplar' => 'integer',
            'tersedia' => 'integer',
        ];
    }

    const KONDISI = ['baik', 'rusak_ringan', 'rusak_berat', 'hilang'];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(PerpustakaanKategori::class, 'kategori_id');
    }

    public function peminjamans(): HasMany
    {
        return $this->hasMany(PerpustakaanPeminjaman::class, 'kitab_id');
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('pengarang', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }
    }

    public function scopeKategori($query, $kategoriId)
    {
        if ($kategoriId) {
            $query->where('kategori_id', $kategoriId);
        }
    }

    public function scopeKondisi($query, $kondisi)
    {
        if ($kondisi) {
            $query->where('kondisi', $kondisi);
        }
    }
}
