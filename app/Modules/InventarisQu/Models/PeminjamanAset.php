<?php

namespace App\Modules\InventarisQu\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanAset extends Model
{
    use BelongsToTenant;

    protected $table = 'peminjaman_asets';

    protected $fillable = [
        'tenant_id', 'aset_id', 'peminjam', 'role_peminjam',
        'tanggal_pinjam', 'tanggal_kembali', 'tujuan', 'status', 'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'tanggal_kembali' => 'date',
        ];
    }

    const STATUS = [
        'dipinjam' => 'Dipinjam',
        'dikembalikan' => 'Dikembalikan',
    ];

    public function aset(): BelongsTo
    {
        return $this->belongsTo(Aset::class, 'aset_id');
    }

    public static function scopeActive($query)
    {
        return $query->where('status', 'dipinjam');
    }

    public static function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('peminjam', 'like', "%{$search}%")
                  ->orWhere('tujuan', 'like', "%{$search}%");
            });
        }
        return $query;
    }
}
