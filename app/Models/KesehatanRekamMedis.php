<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KesehatanRekamMedis extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'kesehatan_rekam_medis';

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'golongan_darah',
        'riwayat_penyakit',
        'alergi_obat',
        'alergi_makanan',
        'tinggi_badan',
        'berat_badan',
        'catatan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tinggi_badan' => 'decimal:1',
            'berat_badan' => 'decimal:1',
        ];
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
