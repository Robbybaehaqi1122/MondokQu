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
        'payment_account_id',
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
            'amount' => 'integer',
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
            'cash',
            'transfer',
            'qris',
        ];
    }

    public static function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'cash' => 'Cash',
            'transfer' => 'Transfer',
            'qris' => 'QRIS',
            'transfer bank' => 'Transfer Bank',
            'e-wallet' => 'E-Wallet',
            'lainnya' => 'Lainnya',
            default => str($method)->headline(),
        };
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class, 'payment_account_id');
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
