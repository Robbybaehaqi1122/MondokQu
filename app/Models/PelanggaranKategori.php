<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PelanggaranKategori extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'nama',
        'poin',
        'deskripsi',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'poin' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pelanggarans(): HasMany
    {
        return $this->hasMany(Pelanggaran::class, 'kategori_id');
    }
}
