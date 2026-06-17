<?php

namespace App\Modules\KegiatanQu\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanPendaftaran extends Model
{
    use BelongsToTenant;

    protected $table = 'kegiatan_pendaftarans';

    protected $fillable = [
        'tenant_id',
        'kegiatan_id',
        'santri_id',
        'status',
        'catatan',
        'confirmed_at',
        'confirmed_by',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
        ];
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
