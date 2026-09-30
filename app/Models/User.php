<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'username',
        'student_id',
        'program',
        'college',
        'role_type',
        'year_level',
        'section',
        'suspended_at',
        'suspension_reason',
        'must_change_password',
        'mfa_enabled',
        'mfa_otp',
        'mfa_otp_expires_at',
        'mfa_otp_attempts',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_otp',
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
            'password' => 'hashed',
            'suspended_at' => 'datetime',
            'must_change_password' => 'boolean',
            'mfa_enabled' => 'boolean',
            'mfa_otp_expires_at' => 'datetime',
        ];
    }

    /**
     * Generate a username from first and last name (e.g., "jsalvador").
     */
    public static function generateUsername(string $firstName, string $lastName): string
    {
        $base = Str::lower(Str::substr($firstName, 0, 1).Str::ascii($lastName));
        $base = preg_replace('/[^a-z0-9]/', '', $base);
        $username = $base;
        $counter = 1;

        while (self::where('username', $username)->exists()) {
            $username = $base.$counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Generate a 6-digit email OTP with 10-minute expiry.
     */
    public function generateMfaOtp(): string
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'mfa_otp' => $otp,
            'mfa_otp_expires_at' => now()->addMinutes(10),
            'mfa_otp_attempts' => 0,
        ]);

        return $otp;
    }

    /**
     * Verify an MFA OTP code. Returns true on success, false on failure.
     * Enforces max 3 retry attempts per OTP.
     */
    public function verifyMfaOtp(string $code): bool
    {
        if ($this->mfa_otp_attempts >= 3) {
            return false;
        }

        if ($this->mfa_otp === null || $this->mfa_otp_expires_at === null) {
            return false;
        }

        if ($this->mfa_otp_expires_at->isPast()) {
            return false;
        }

        if ($this->mfa_otp !== $code) {
            $this->increment('mfa_otp_attempts');

            return false;
        }

        // OTP is valid — clear it
        $this->update([
            'mfa_otp' => null,
            'mfa_otp_expires_at' => null,
            'mfa_otp_attempts' => 0,
        ]);

        return true;
    }

    /**
     * Determine if MFA is mandatory for this user based on role_type.
     */
    public function hasMfaRequired(): bool
    {
        $mandatoryRoleTypes = ['tribunal_panel', 'system_auditor'];

        if (in_array($this->role_type, $mandatoryRoleTypes, true)) {
            return true;
        }

        try {
            if ($this->hasRole('administrator')) {
                return true;
            }
        } catch (\Throwable) {
            // Fallback if roles cache/table is not initialized
        }

        return (bool) ($this->mfa_enabled ?? false);
    }

    public function canAccessTribunalModule(): bool
    {
        return in_array($this->role_type, ['tribunal_panel', 'admin'], true);
    }

    /**
     * Get all violation records where this user is the student.
     */
    public function violationRecords(): HasMany
    {
        return $this->hasMany(ViolationRecord::class, 'student_id');
    }

    /**
     * Get all violation records reported by this user (staff member).
     */
    public function reportedViolations(): HasMany
    {
        return $this->hasMany(ViolationRecord::class, 'reported_by');
    }

    /**
     * Get all audit logs for this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuthAuditLog::class);
    }

    /**
     * Get all notifications for this user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'recipient_id');
    }

    /**
     * Get the user's notification preference settings.
     */
    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    /**
     * Get the student's current standing.
     */
    public function standing(): HasOne
    {
        return $this->hasOne(StudentStanding::class, 'student_id');
    }

    /**
     * Get offenses where this user is the student.
     */
    public function offenses(): HasMany
    {
        return $this->hasMany(Offense::class, 'student_id');
    }
}
