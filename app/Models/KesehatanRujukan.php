<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KesehatanRujukan extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'pemeriksaan_id',
        'tempat_rujukan',
        'diagnosis_dokter',
        'tanggal_rujuk',
        'tanggal_kembali',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_rujuk' => 'date',
            'tanggal_kembali' => 'date',
        ];
    }

    public function pemeriksaan(): BelongsTo
    {
        return $this->belongsTo(KesehatanPemeriksaan::class, 'pemeriksaan_id');
    }
}
