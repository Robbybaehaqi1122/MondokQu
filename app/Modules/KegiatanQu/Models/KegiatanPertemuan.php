<?php

namespace App\Modules\KegiatanQu\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KegiatanPertemuan extends Model
{
    use BelongsToTenant;

    protected $table = 'kegiatan_pertemuans';

    protected $fillable = [
        'tenant_id',
        'kegiatan_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'materi',
        'catatan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam_mulai' => 'string',
            'jam_selesai' => 'string',
        ];
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(KegiatanPresensi::class, 'pertemuan_id');
    }
}
