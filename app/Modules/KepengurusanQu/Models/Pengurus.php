<?php

namespace App\Modules\KepengurusanQu\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Pengurus extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'nama',
        'jabatan',
        'no_telp',
        'alamat',
        'foto',
        'status',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch($query, ?string $search)
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }
    }

    public function scopeActive($query)
    {
        $query->where('status', true);
    }
}
