<?php

namespace App\Models;

use App\Models\Concerns\HasTenantSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Tenant extends Model
{
    use HasFactory, HasTenantSettings;

    public const SUBSCRIPTION_TRIAL = 'trial';

    public const SUBSCRIPTION_ACTIVE = 'active';

    public const SUBSCRIPTION_GRACE = 'grace';

    public const SUBSCRIPTION_EXPIRED = 'expired';

    public const SUBSCRIPTION_DELETING = 'deleting';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'contact_email',
        'contact_phone_number',
        'subscription_plan',
        'subscription_status',
        'trial_ends_at',
        'subscription_starts_at',
        'subscription_ends_at',
        'grace_ends_at',
        'owner_id',
        'settings',
    ];

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'subscription_starts_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'grace_ends_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    /**
     * Get the owner of the tenant.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the users that belong to the tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the santri that belong to the tenant.
     */
    public function santris(): HasMany
    {
        return $this->hasMany(Santri::class);
    }

    /**
     * Get rooms that belong to the tenant.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get the activity logs that belong to the tenant.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the subscription history entries that belong to the tenant.
     */
    public function subscriptionHistories(): HasMany
    {
        return $this->hasMany(TenantSubscriptionHistory::class);
    }

    /**
     * Get the billing notes that belong to the tenant.
     */
    public function billingNotes(): HasMany
    {
        return $this->hasMany(TenantBillingNote::class);
    }

    /**
     * Get the santri invoices that belong to the tenant.
     */
    public function santriInvoices(): HasMany
    {
        return $this->hasMany(SantriInvoice::class);
    }

    /**
     * Get the santri payments that belong to the tenant.
     */
    public function santriPayments(): HasMany
    {
        return $this->hasMany(SantriPayment::class);
    }

    /**
     * Get the payment confirmations that belong to the tenant.
     */
    public function santriPaymentConfirmations(): HasMany
    {
        return $this->hasMany(SantriPaymentConfirmation::class);
    }

    /**
     * Get the attendance activities that belong to the tenant.
     */
    public function attendanceActivities(): HasMany
    {
        return $this->hasMany(AttendanceActivity::class);
    }

    /**
     * Get the attendance sessions that belong to the tenant.
     */
    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    /**
     * Get the attendance records that belong to the tenant.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Get the leave requests that belong to the tenant.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get the data exports that belong to the tenant.
     */
    public function dataExports(): HasMany
    {
        return $this->hasMany(DataExport::class);
    }

    /**
     * Get the room transfers that belong to the tenant.
     */
    public function roomTransfers(): HasMany
    {
        return $this->hasMany(RoomTransfer::class);
    }

    /**
     * Get the guardian link records that belong to the tenant.
     */
    public function santriGuardians(): HasMany
    {
        return $this->hasMany(SantriGuardian::class);
    }

    /**
     * Determine whether the tenant is still in the trial period.
     */
    public function onTrial(): bool
    {
        return $this->subscription_status === self::SUBSCRIPTION_TRIAL
            && $this->trial_ends_at?->isFuture();
    }

    /**
     * Determine whether the tenant subscription is active.
     */
    public function hasPaidSubscription(): bool
    {
        return $this->subscription_status === self::SUBSCRIPTION_ACTIVE
            && $this->subscription_ends_at?->isFuture();
    }

    /**
     * Determine whether the tenant is in grace period after subscription expiry.
     */
    public function onGracePeriod(): bool
    {
        return $this->subscription_status === self::SUBSCRIPTION_GRACE
            && $this->grace_ends_at?->isFuture();
    }

    /**
     * Determine whether the tenant can still access the app.
     */
    public function hasAccess(): bool
    {
        return $this->onTrial() || $this->hasPaidSubscription() || $this->onGracePeriod();
    }

    /**
     * Determine whether the tenant is queued for permanent deletion.
     */
    public function isDeleting(): bool
    {
        return $this->subscription_status === self::SUBSCRIPTION_DELETING;
    }

    /**
     * Determine whether the tenant already requires payment.
     */
    public function requiresPayment(): bool
    {
        return ! $this->hasAccess();
    }

    /**
     * Get tenant subscription lifecycle statuses.
     *
     * @return array<int, string>
     */
    public static function subscriptionStatuses(): array
    {
        return [
            self::SUBSCRIPTION_TRIAL,
            self::SUBSCRIPTION_ACTIVE,
            self::SUBSCRIPTION_GRACE,
            self::SUBSCRIPTION_EXPIRED,
            self::SUBSCRIPTION_DELETING,
        ];
    }

    public function getMaxUsers(): int
    {
        return (int) ($this->getSetting('max_users') ?? config('saas.limits.max_users', 50));
    }

    public function getMaxSantri(): int
    {
        return (int) ($this->getSetting('max_santri') ?? config('saas.limits.max_santri', 200));
    }

    public function getMaxStorageMb(): int
    {
        return (int) ($this->getSetting('max_storage_mb') ?? config('saas.limits.max_storage_mb', 1024));
    }

    public function getCurrentUsersCount(): int
    {
        return $this->users()->count();
    }

    public function getCurrentSantriCount(): int
    {
        return $this->santris()->count();
    }

    public function getCurrentStorageBytes(): int
    {
        $tenantId = $this->id;

        $bytes = 0;

        $bytes += (int) SantriDocument::query()
            ->whereHas('santri', fn ($q) => $q->where('tenant_id', $tenantId))
            ->sum('file_size');

        $bytes += (int) Backup::query()
            ->where('tenant_id', $tenantId)
            ->sum('size_bytes');

        $userAvatarPaths = $this->users()
            ->whereNotNull('avatar_path')
            ->pluck('avatar_path');

        foreach ($userAvatarPaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                $bytes += Storage::disk('public')->size($path);
            }
        }

        $santriPhotoPaths = $this->santris()
            ->whereNotNull('photo_path')
            ->pluck('photo_path');

        foreach ($santriPhotoPaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                $bytes += Storage::disk('public')->size($path);
            }
        }

        $proofPaths = SantriPaymentConfirmation::query()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('proof_path')
            ->pluck('proof_path');

        foreach ($proofPaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                $bytes += Storage::disk('public')->size($path);
            }
        }

        $branding = $this->settings['logo_path'] ?? null;
        if ($branding && Storage::disk('public')->exists($branding)) {
            $bytes += Storage::disk('public')->size($branding);
        }

        $favicon = $this->settings['favicon_path'] ?? null;
        if ($favicon && Storage::disk('public')->exists($favicon)) {
            $bytes += Storage::disk('public')->size($favicon);
        }

        return $bytes;
    }

    public function getUsagePercentage(string $resource): int
    {
        return match ($resource) {
            'users' => $this->getMaxUsers() > 0
                ? (int) round(($this->getCurrentUsersCount() / $this->getMaxUsers()) * 100)
                : 0,
            'santri' => $this->getMaxSantri() > 0
                ? (int) round(($this->getCurrentSantriCount() / $this->getMaxSantri()) * 100)
                : 0,
            'storage' => $this->getMaxStorageMb() > 0
                ? (int) round(($this->getCurrentStorageBytes() / ($this->getMaxStorageMb() * 1024 * 1024)) * 100)
                : 0,
            default => 0,
        };
    }

    public function canCreateUser(): bool
    {
        return $this->getCurrentUsersCount() < $this->getMaxUsers();
    }

    public function canCreateSantri(): bool
    {
        return $this->getCurrentSantriCount() < $this->getMaxSantri();
    }

    public function capacityErrorHtml(string $resource): string
    {
        $max = match ($resource) {
            'users' => $this->getMaxUsers(),
            'santri' => $this->getMaxSantri(),
            default => 0,
        };
        $current = match ($resource) {
            'users' => $this->getCurrentUsersCount(),
            'santri' => $this->getCurrentSantriCount(),
            default => 0,
        };
        $label = match ($resource) {
            'users' => 'user',
            'santri' => 'santri',
            default => $resource,
        };

        $waUrl = $this->whatsappContactUrl(
            "Halo, saya ingin upgrade kapasitas {$label} tenant {$this->name}. Saat ini sudah {$current}/{$max} dan perlu ditambah.",
        );

        return "Kapasitas {$label} sudah penuh ({$current}/{$max}). "
            . 'Hubungi admin platform untuk upgrade kapasitas. '
            . '<br><a href="' . e($waUrl) . '" target="_blank" class="btn btn-sm btn-success mt-2">'
            . '<i class="ti ti-brand-whatsapp me-1"></i>Hubungi via WhatsApp</a>';
    }

    public function whatsappContactUrl(string $text = ''): string
    {
        $phone = config('saas.admin_whatsapp');
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $url = "https://wa.me/{$phone}";
        if ($text !== '') {
            $url .= '?text=' . rawurlencode($text);
        }

        return $url;
    }
}
