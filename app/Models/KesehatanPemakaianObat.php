<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KesehatanPemakaianObat extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'pemeriksaan_id',
        'obat_id',
        'jumlah',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

    public function pemeriksaan(): BelongsTo
    {
        return $this->belongsTo(KesehatanPemeriksaan::class, 'pemeriksaan_id');
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(KesehatanObat::class, 'obat_id');
    }
}
