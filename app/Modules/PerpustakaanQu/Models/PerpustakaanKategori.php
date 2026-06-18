<?php

namespace App\Modules\PerpustakaanQu\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PerpustakaanKategori extends Model
{
    use BelongsToTenant;

    protected $table = 'perpustakaan_kategoris';

    protected $fillable = [
        'tenant_id',
        'nama',
        'slug',
        'deskripsi',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $kategori) {
            if (! $kategori->slug) {
                $kategori->slug = Str::slug($kategori->nama);
            }
        });
    }

    public function kitabs(): HasMany
    {
        return $this->hasMany(PerpustakaanKitab::class, 'kategori_id');
    }
}
