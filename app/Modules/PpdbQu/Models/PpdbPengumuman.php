<?php

namespace App\Modules\PpdbQu\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbPengumuman extends Model
{
    use BelongsToTenant;

    protected $table = 'ppdb_pengumumans';

    protected $fillable = [
        'tenant_id',
        'gelombang_id',
        'judul',
        'deskripsi',
        'tanggal_pengumuman',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengumuman' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function gelombang(): BelongsTo
    {
        return $this->belongsTo(PpdbGelombang::class, 'gelombang_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
