<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use BelongsToTenant, HasFactory, HasRoles, MustVerifyEmail, Notifiable;

    /**
     * User queries must remain available during authentication and superadmin operations.
     */
    protected bool $usesTenantGlobalScope = false;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_SUSPENDED = 'suspended';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'username',
        'email',
        'phone_number',
        'status',
        'email_verified_at',
        'created_by',
        'password_change_required',
        'avatar_path',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password_change_required' => 'boolean',
            'password' => 'hashed',
            'phone_number' => 'encrypted',
        ];
    }

    /**
     * Get the user that created this account.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    /**
     * Get the tenant that owns this user.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the tenant records created by this user.
     */
    public function ownedTenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'owner_id');
    }

    /**
     * Get guardian link records owned by this user.
     */
    public function guardianLinks(): HasMany
    {
        return $this->hasMany(SantriGuardian::class);
    }

    /**
     * Get santri records visible through the guardian portal.
     */
    public function guardianSantris(): BelongsToMany
    {
        return $this->belongsToMany(Santri::class, 'santri_guardians')
            ->withPivot(['tenant_id', 'relationship', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * Get santri records created by this user.
     */
    public function createdSantris(): HasMany
    {
        return $this->hasMany(Santri::class, 'created_by');
    }

    /**
     * Get rooms created by this user.
     */
    public function createdRooms(): HasMany
    {
        return $this->hasMany(Room::class, 'created_by');
    }

    /**
     * Get invoices created by this user.
     */
    public function createdSantriInvoices(): HasMany
    {
        return $this->hasMany(SantriInvoice::class, 'created_by');
    }

    /**
     * Get payment confirmations submitted by this user.
     */
    public function submittedPaymentConfirmations(): HasMany
    {
        return $this->hasMany(SantriPaymentConfirmation::class, 'submitted_by');
    }

    /**
     * Get payment confirmations reviewed by this user.
     */
    public function reviewedPaymentConfirmations(): HasMany
    {
        return $this->hasMany(SantriPaymentConfirmation::class, 'reviewed_by');
    }

    /**
     * Get attendance sessions created by this user.
     */
    public function createdAttendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class, 'created_by');
    }

    /**
     * Get attendance records recorded by this user.
     */
    public function recordedAttendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'recorded_by');
    }

    /**
     * Get attendance activities where this user is responsible.
     */
    public function responsibleAttendanceActivities(): HasMany
    {
        return $this->hasMany(AttendanceActivity::class, 'responsible_user_id');
    }

    /**
     * Get attendance activities created by this user.
     */
    public function createdAttendanceActivities(): HasMany
    {
        return $this->hasMany(AttendanceActivity::class, 'created_by');
    }

    /**
     * Get leave requests approved by this user.
     */
    public function approvedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    /**
     * Get leave requests created by this user.
     */
    public function createdLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'created_by');
    }

    /**
     * Get data exports requested by this user.
     */
    public function dataExports(): HasMany
    {
        return $this->hasMany(DataExport::class, 'user_id');
    }

    /**
     * Get payments recorded by this user.
     */
    public function recordedPayments(): HasMany
    {
        return $this->hasMany(SantriPayment::class, 'recorded_by');
    }

    /**
     * Get room transfers moved by this user.
     */
    public function movedRoomTransfers(): HasMany
    {
        return $this->hasMany(RoomTransfer::class, 'moved_by');
    }

    /**
     * Get activity logs where this user is the actor.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'actor_id');
    }

    /**
     * Get billing notes recorded by this user.
     */
    public function recordedBillingNotes(): HasMany
    {
        return $this->hasMany(TenantBillingNote::class, 'recorded_by');
    }

    /**
     * Get subscription history entries changed by this user.
     */
    public function changedSubscriptionHistories(): HasMany
    {
        return $this->hasMany(TenantSubscriptionHistory::class, 'changed_by');
    }

    /**
     * Get users created by this user.
     */
    public function createdUsers(): HasMany
    {
        return $this->hasMany(self::class, 'created_by');
    }

    /**
     * Get the available user statuses.
     *
     * @return array<int, string>
     */
    public static function availableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_SUSPENDED,
        ];
    }

    /**
     * Determine whether the user is allowed to sign in.
     */
    public function canAuthenticate(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Determine whether the user is the internal superadmin account.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Superadmin');
    }

    /**
     * Resolve a usable tenant ID, falling back to the first tenant for superadmins.
     */
    public function effectiveTenantId(): ?int
    {
        if ($this->tenant_id) {
            return $this->tenant_id;
        }

        if ($this->isSuperAdmin()) {
            return Tenant::query()->value('id');
        }

        return null;
    }

    /**
     * Determine whether the user belongs to the selected tenant.
     */
    public function belongsToTenant(?Tenant $tenant): bool
    {
        if (! $tenant || ! $this->tenant_id) {
            return false;
        }

        return $this->tenant_id === $tenant->id;
    }

    /**
     * Resolve the avatar URL for presentation.
     */
    public function avatarUrl(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        if (filter_var($this->avatar_path, FILTER_VALIDATE_URL)) {
            return $this->avatar_path;
        }

        if (str_starts_with($this->avatar_path, '/')) {
            return $this->avatar_path;
        }

        if (! Storage::disk('public')->exists($this->avatar_path)) {
            return null;
        }

        return asset('storage/'.$this->avatar_path);
    }
}
