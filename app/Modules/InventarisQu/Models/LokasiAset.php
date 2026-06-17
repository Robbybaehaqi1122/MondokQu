<?php

namespace App\Modules\InventarisQu\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LokasiAset extends Model
{
    use BelongsToTenant;

    protected $table = 'lokasi_asets';

    protected $fillable = [
        'tenant_id', 'name', 'building', 'floor', 'description',
    ];

    public function asets(): HasMany
    {
        return $this->hasMany(Aset::class, 'lokasi_id');
    }
}
