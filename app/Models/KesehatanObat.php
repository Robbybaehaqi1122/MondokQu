<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KesehatanObat extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'nama_obat',
        'jenis',
        'stok',
        'satuan',
        'expired_date',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'expired_date' => 'date',
            'stok' => 'integer',
        ];
    }

    public function pemakaian(): HasMany
    {
        return $this->hasMany(KesehatanPemakaianObat::class, 'obat_id');
    }
}
