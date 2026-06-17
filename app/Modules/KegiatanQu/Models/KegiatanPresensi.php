<?php

namespace App\Modules\KegiatanQu\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanPresensi extends Model
{
    use BelongsToTenant;

    protected $table = 'kegiatan_presensis';

    protected $fillable = [
        'tenant_id',
        'pertemuan_id',
        'santri_id',
        'status',
        'catatan',
        'diisi_oleh',
    ];

    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(KegiatanPertemuan::class, 'pertemuan_id');
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diisi_oleh');
    }
}
