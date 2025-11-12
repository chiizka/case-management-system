<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * @property int $id
 * @property string $fname
 * @property string $lname
 * @property string $email
 * @property string $role
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $password
 * @property \Illuminate\Support\Carbon|null $password_reset_sent_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $otp_code
 * @property \Illuminate\Support\Carbon|null $otp_expires_at
 * @property bool $two_factor_enabled
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOtpCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOtpExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePasswordResetSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'fname',
        'lname',
        'email',
        'role',
        'password',
        'two_factor_enabled',
        'otp_code',
        'otp_expires_at',
        'password_reset_sent_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'password_reset_sent_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
    ];

    // Role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';
    const ROLE_PROVINCE = 'province';
    const ROLE_MALSU = 'malsu';
    const ROLE_CASE_MANAGEMENT = 'case_management';
    const ROLE_RECORDS = 'records';

    // REMOVED: Password mutator (it was causing issues)
    // We'll handle password hashing manually where needed

    // Check if user needs to set password
    public function needsPasswordSetup()
    {
        return is_null($this->password);
    }

    // Generate and store OTP
    public function generateOTP()
    {
        $this->otp_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->otp_expires_at = Carbon::now()->addMinutes(10);
        $this->save();
        
        return $this->otp_code;
    }

    // Verify OTP
    public function verifyOTP($code)
    {
        if ($this->otp_code === $code && $this->otp_expires_at && $this->otp_expires_at->isFuture()) {
            // Clear OTP after successful verification
            $this->otp_code = null;
            $this->otp_expires_at = null;
            $this->save();
            return true;
        }
        return false;
    }

    // Check if OTP has expired
    public function isOTPExpired()
    {
        return $this->otp_expires_at && $this->otp_expires_at->isPast();
    }

    // Role helper methods
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isUser()
    {
        return $this->role === self::ROLE_USER;
    }

    public function isProvince()
    {
        return $this->role === self::ROLE_PROVINCE;
    }

    public function isMalsu()
    {
        return $this->role === self::ROLE_MALSU;
    }

    public function isCaseManagement()
    {
        return $this->role === self::ROLE_CASE_MANAGEMENT;
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function isRecords()
    {
        return $this->role === self::ROLE_RECORDS;
    }
}