<?php

namespace App\Modules\InventarisQu\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriAset extends Model
{
    use BelongsToTenant;

    protected $table = 'kategori_asets';

    protected $fillable = [
        'tenant_id', 'name', 'icon', 'description',
    ];

    public function asets(): HasMany
    {
        return $this->hasMany(Aset::class, 'kategori_id');
    }
}
