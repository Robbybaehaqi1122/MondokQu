<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiSikap extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'semester',
        'sikap_spiritual',
        'sikap_sosial',
        'deskripsi_spiritual',
        'deskripsi_sosial',
        'catatan_wali',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function sikapSpiritualLabel(): string
    {
        return match ($this->sikap_spiritual) {
            'SB' => 'Sangat Baik',
            'B' => 'Baik',
            'C' => 'Cukup',
            'K' => 'Kurang',
            default => '-',
        };
    }

    public function sikapSosialLabel(): string
    {
        return match ($this->sikap_sosial) {
            'SB' => 'Sangat Baik',
            'B' => 'Baik',
            'C' => 'Cukup',
            'K' => 'Kurang',
            default => '-',
        };
    }
}
