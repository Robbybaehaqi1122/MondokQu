<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KesehatanImunisasi extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_SUDAH = 'sudah';

    public const STATUS_BELUM = 'belum';

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'jenis_imunisasi',
        'tanggal',
        'status',
        'catatan',
        'diberikan_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function pemberi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diberikan_oleh');
    }
}
