<?php

namespace App\Modules\KegiatanQu\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanNilai extends Model
{
    use BelongsToTenant;

    protected $table = 'kegiatan_nilais';

    protected $fillable = [
        'tenant_id',
        'kegiatan_id',
        'santri_id',
        'aspek',
        'nilai',
        'catatan',
        'dinilai_oleh',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'integer',
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

    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }
}
