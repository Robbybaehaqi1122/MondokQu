<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\SantriPaymentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class SantriPayment extends Model
{
    /** @use HasFactory<SantriPaymentFactory> */
    use BelongsToTenant, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'santri_invoice_id',
        'santri_id',
        'paid_at',
        'amount',
        'payment_method',
        'reference_number',
        'note',
        'recorded_by',
    ];

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get allowed payment methods.
     *
     * @return array<int, string>
     */
    public static function paymentMethods(): array
    {
        return [
            'transfer bank',
            'cash',
            'e-wallet',
            'qris',
            'lainnya',
        ];
    }

    /**
     * Get the tenant that owns this payment.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the invoice paid by this payment.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SantriInvoice::class, 'santri_invoice_id');
    }

    /**
     * Get the santri attached to this payment.
     */
    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    /**
     * Get the user that recorded this payment.
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Limit payments to a paid_at date range.
     */
    public function scopePaidBetween(Builder $query, Carbon $dateFrom, Carbon $dateTo): Builder
    {
        return $query->whereBetween('paid_at', [$dateFrom, $dateTo]);
    }
}
