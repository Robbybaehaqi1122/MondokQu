<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Santri extends Model
{
    use BelongsToTenant, HasFactory;

    public const GENDER_MALE = 'male';

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public const GENDER_FEMALE = 'female';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_LEAVE = 'leave';

    public const STATUS_EXITED = 'exited';

    public const STATUS_ALUMNI = 'alumni';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'barcode',
        'nis',
        'nisn',
        'full_name',
        'gender',
        'birth_place',
        'birth_date',
        'address',
        'guardian_name',
        'father_name',
        'father_phone',
        'father_education',
        'father_job',
        'mother_name',
        'mother_phone',
        'mother_education',
        'mother_job',
        'guardian_phone_number',
        'guardian_relation',
        'guardian_address',
        'emergency_contact',
        'entry_date',
        'entry_year',
        'room_id',
        'notes',
        'status',
        'photo_path',
        'created_by',
    ];

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'entry_date' => 'date',
            'entry_year' => 'integer',
            'father_phone' => 'encrypted',
            'mother_phone' => 'encrypted',
            'guardian_phone_number' => 'encrypted',
            'emergency_contact' => 'encrypted',
            'address' => 'encrypted',
            'guardian_address' => 'encrypted',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Santri $santri): void {
            if (! $santri->uuid) {
                $santri->uuid = (string) Str::uuid();
            }

            if (! $santri->barcode) {
                $santri->barcode = static::generateUniqueBarcode();
            }
        });
    }

    public static function generateUniqueBarcode(): string
    {
        do {
            $barcode = strtoupper(Str::random(8));
        } while (static::query()->where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Get the user that created this santri record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the tenant that owns this santri record.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the structured room assigned to this santri.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get room transfer history for this santri.
     */
    public function roomTransfers(): HasMany
    {
        return $this->hasMany(RoomTransfer::class);
    }

    /**
     * Get invoices issued to this santri.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(SantriInvoice::class);
    }

    /**
     * Get payments recorded for this santri.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SantriPayment::class);
    }

    /**
     * Get attendance records for this santri.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function tahfidzSessions(): HasMany
    {
        return $this->hasMany(TahfidzSession::class);
    }

    public function tahfidzTargets(): HasMany
    {
        return $this->hasMany(TahfidzTarget::class);
    }

    /**
     * Get guardian link records for this santri.
     */
    public function guardianLinks(): HasMany
    {
        return $this->hasMany(SantriGuardian::class);
    }

    /**
     * Get wali user accounts attached to this santri.
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'santri_guardians')
            ->withPivot(['tenant_id', 'relationship', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * Get payment confirmations submitted for this santri.
     */
    public function paymentConfirmations(): HasMany
    {
        return $this->hasMany(SantriPaymentConfirmation::class);
    }

    public function nilaiSantris(): HasMany
    {
        return $this->hasMany(NilaiSantri::class);
    }

    public function nilaiSikaps(): HasMany
    {
        return $this->hasMany(NilaiSikap::class);
    }

    public function attitudeGrades(): HasMany
    {
        return $this->hasMany(AttitudeGrade::class);
    }

    public function pelanggarans(): HasMany
    {
        return $this->hasMany(Pelanggaran::class);
    }

    public function sanctionLogs(): HasMany
    {
        return $this->hasMany(SanctionLog::class);
    }

    public function communications(): HasMany
    {
        return $this->hasMany(Communication::class);
    }

    /**
     * Get leave requests for this santri.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get the medical record for this santri.
     */
    public function rekamMedis(): HasOne
    {
        return $this->hasOne(KesehatanRekamMedis::class, 'santri_id');
    }

    /**
     * Scope to only active santri (status = 'active').
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Apply filters used by the santri management list and export.
     */
    public function scopeWithFilters(Builder $query, string $search = '', string $status = '', string $gender = ''): Builder
    {
        $search = trim($search);
        $status = trim($status);
        $gender = trim($gender);

        return $query
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $santriQuery) use ($search): void {
                    $santriQuery
                        ->where('nis', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('guardian_name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('mother_name', 'like', "%{$search}%")
                        ->orWhereHas('room', fn (Builder $roomQuery) => $roomQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('guardians', function (Builder $guardianQuery) use ($search): void {
                            $guardianQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($gender !== '', fn (Builder $query) => $query->where('gender', $gender));
    }

    /**
     * Get the available santri statuses.
     *
     * @return array<int, string>
     */
    public static function availableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_LEAVE,
            self::STATUS_EXITED,
            self::STATUS_ALUMNI,
        ];
    }

    /**
     * Get the available santri genders.
     *
     * @return array<int, string>
     */
    public static function availableGenders(): array
    {
        return [
            self::GENDER_MALE,
            self::GENDER_FEMALE,
        ];
    }

    /**
     * Resolve the photo URL for presentation.
     */
    public function photoUrl(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        if (filter_var($this->photo_path, FILTER_VALIDATE_URL)) {
            return $this->photo_path;
        }

        if (str_starts_with($this->photo_path, '/')) {
            return $this->photo_path;
        }

        if (! Storage::disk('public')->exists($this->photo_path)) {
            return null;
        }

        return asset('storage/'.$this->photo_path);
    }

    /**
     * Resolve a human-friendly gender label.
     */
    public function genderLabel(): string
    {
        return match ($this->gender) {
            self::GENDER_MALE => 'Laki-laki',
            self::GENDER_FEMALE => 'Perempuan',
            default => '-',
        };
    }

    /**
     * Resolve a human-friendly status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_LEAVE => 'Libur',
            self::STATUS_EXITED => 'Keluar',
            self::STATUS_ALUMNI => 'Alumni',
            default => '-',
        };
    }

    /**
     * Resolve the room name from the structured relationship, with legacy room_name as fallback.
     */
    public function displayRoomName(string $fallback = '-'): string
    {
        return $this->room?->name ?? $fallback;
    }

    /**
     * Resolve guardian name from linked user account, fallback to legacy column.
     */
    public function displayGuardianName(string $fallback = '-'): string
    {
        $primaryGuardian = $this->guardians?->firstWhere('pivot.is_primary', true)
            ?? $this->guardians?->first();

        return $primaryGuardian?->name ?? ($this->guardian_name ?: $fallback);
    }

    /**
     * Resolve guardian phone from linked user account, fallback to legacy column.
     */
    public function displayGuardianPhone(string $fallback = '-'): string
    {
        $primaryGuardian = $this->guardians?->firstWhere('pivot.is_primary', true)
            ?? $this->guardians?->first();

        return $primaryGuardian?->phone_number ?? ($this->guardian_phone_number ?: $fallback);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SantriDocument::class);
    }

    public function totalPoin(): int
    {
        return (int) $this->pelanggarans()->sum('poin');
    }

    public function currentSanctionThreshold(): ?SanctionThreshold
    {
        $total = $this->totalPoin();

        return SanctionThreshold::query()
            ->where('tenant_id', $this->tenant_id)
            ->where('min_points', '<=', $total)
            ->where(function ($q) use ($total) {
                $q->where('max_points', '>=', $total)
                    ->orWhereNull('max_points');
            })
            ->orderBy('min_points', 'desc')
            ->first();
    }

    public function nextSanctionThreshold(): ?SanctionThreshold
    {
        $total = $this->totalPoin();

        return SanctionThreshold::query()
            ->where('tenant_id', $this->tenant_id)
            ->where('min_points', '>', $total)
            ->orderBy('min_points')
            ->first();
    }

    public function isDocumentComplete(): bool
    {
        $uploadedTypes = $this->documents->pluck('type')->unique()->values()->toArray();

        return empty(array_diff(SantriDocument::requiredTypes(), $uploadedTypes));
    }

    public function missingDocumentTypes(): array
    {
        $uploadedTypes = $this->documents->pluck('type')->unique()->values()->toArray();

        return array_values(array_diff(SantriDocument::requiredTypes(), $uploadedTypes));
    }
}
