<?php

namespace App\Modules\InventarisQu\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aset extends Model
{
    use BelongsToTenant;

    protected $table = 'asets';

    protected $fillable = [
        'tenant_id', 'kategori_id', 'lokasi_id', 'kode_aset',
        'name', 'merk', 'tahun_perolehan', 'harga_perolehan',
        'kondisi', 'qr_code', 'deskripsi', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tahun_perolehan' => 'integer',
            'harga_perolehan' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    const KONDISI = [
        'baik' => 'Baik',
        'rusak_ringan' => 'Rusak Ringan',
        'rusak_berat' => 'Rusak Berat',
        'hilang' => 'Hilang',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriAset::class, 'kategori_id');
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(LokasiAset::class, 'lokasi_id');
    }

    public function peminjaman(): HasMany
    {
        return $this->hasMany(PeminjamanAset::class, 'aset_id');
    }

    public static function generateKodeAset(int $tenantId): string
    {
        $prefix = 'INV-' . $tenantId . '-';
        $last = self::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('kode_aset', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('kode_aset');

        $nextNumber = $last ? (int) substr($last, -5) + 1 : 1;
        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public static function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('kode_aset', 'like', "%{$search}%")
                  ->orWhere('merk', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    public static function scopeKondisi($query, $kondisi)
    {
        if ($kondisi) {
            return $query->where('kondisi', $kondisi);
        }
        return $query;
    }

    public static function scopeLokasi($query, $lokasiId)
    {
        if ($lokasiId) {
            return $query->where('lokasi_id', $lokasiId);
        }
        return $query;
    }

    public static function scopeKategori($query, $kategoriId)
    {
        if ($kategoriId) {
            return $query->where('kategori_id', $kategoriId);
        }
        return $query;
    }
}
