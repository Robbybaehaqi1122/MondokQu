<?php

namespace App\Modules\KitabQu\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kitab extends Model
{
    use BelongsToTenant;

    protected $table = 'kitab_kitabs';

    protected $fillable = [
        'tenant_id',
        'kategori_id',
        'nama',
        'pengarang',
        'keterangan',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KitabKategori::class, 'kategori_id');
    }

    public function setorans(): HasMany
    {
        return $this->hasMany(KitabSetoran::class, 'kitab_id');
    }

    public function scopeSearch($query, ?string $search)
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('pengarang', 'like', "%{$search}%");
            });
        }
    }

    public function scopeKategori($query, ?string $kategoriId)
    {
        if ($kategoriId) {
            $query->where('kategori_id', $kategoriId);
        }
    }
}
