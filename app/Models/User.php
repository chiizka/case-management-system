<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

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
    const ROLE_MALSU = 'malsu';
    const ROLE_CASE_MANAGEMENT = 'case_management';
    const ROLE_RECORDS = 'records';
    
    // Province role constants
    const ROLE_PROVINCE_ALBAY = 'province_albay';
    const ROLE_PROVINCE_CAMARINES_SUR = 'province_camarines_sur';
    const ROLE_PROVINCE_CAMARINES_NORTE = 'province_camarines_norte';
    const ROLE_PROVINCE_CATANDUANES = 'province_catanduanes';
    const ROLE_PROVINCE_MASBATE = 'province_masbate';
    const ROLE_PROVINCE_SORSOGON = 'province_sorsogon';

    // All province roles array
    const PROVINCE_ROLES = [
        self::ROLE_PROVINCE_ALBAY,
        self::ROLE_PROVINCE_CAMARINES_SUR,
        self::ROLE_PROVINCE_CAMARINES_NORTE,
        self::ROLE_PROVINCE_CATANDUANES,
        self::ROLE_PROVINCE_MASBATE,
        self::ROLE_PROVINCE_SORSOGON,
    ];

    // All valid roles
    const VALID_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_USER,
        self::ROLE_MALSU,
        self::ROLE_CASE_MANAGEMENT,
        self::ROLE_RECORDS,
        self::ROLE_PROVINCE_ALBAY,
        self::ROLE_PROVINCE_CAMARINES_SUR,
        self::ROLE_PROVINCE_CAMARINES_NORTE,
        self::ROLE_PROVINCE_CATANDUANES,
        self::ROLE_PROVINCE_MASBATE,
        self::ROLE_PROVINCE_SORSOGON,
    ];

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

    public function isMalsu()
    {
        return $this->role === self::ROLE_MALSU;
    }

    public function isCaseManagement()
    {
        return $this->role === self::ROLE_CASE_MANAGEMENT;
    }

    public function isRecords()
    {
        return $this->role === self::ROLE_RECORDS;
    }

    /**
     * Check if user is from any province
     */
    public function isProvince()
    {
        return in_array($this->role, self::PROVINCE_ROLES);
    }

    /**
     * Check if user is from a specific province
     */
    public function isProvinceOf($province)
    {
        return $this->role === 'province_' . strtolower(str_replace(' ', '_', $province));
    }

    /**
     * Get the province name for province users
     */
    public function getProvinceName()
    {
        if (!$this->isProvince()) {
            return null;
        }

        $provinceNames = [
            self::ROLE_PROVINCE_ALBAY => 'Albay',
            self::ROLE_PROVINCE_CAMARINES_SUR => 'Camarines Sur',
            self::ROLE_PROVINCE_CAMARINES_NORTE => 'Camarines Norte',
            self::ROLE_PROVINCE_CATANDUANES => 'Catanduanes',
            self::ROLE_PROVINCE_MASBATE => 'Masbate',
            self::ROLE_PROVINCE_SORSOGON => 'Sorsogon',
        ];

        return $provinceNames[$this->role] ?? null;
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if the user has any of the given roles
     */
    public function hasAnyRole(array $roles)
    {
        return in_array($this->role, $roles);
    }
}