<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PaymentAccount extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'account_name',
        'account_number',
        'bank_name',
        'qris_image',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function qrisImageUrl(): ?string
    {
        return $this->qris_image ? Storage::url($this->qris_image) : null;
    }

    public function displayLabel(): string
    {
        if ($this->type === 'bank') {
            return $this->bank_name . ' - ' . $this->account_number;
        }
        if ($this->type === 'e_wallet') {
            return $this->name . ' (' . $this->account_number . ')';
        }
        return $this->name;
    }
}
