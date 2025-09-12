<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'fname',        // Changed from 'name' to match your database
        'lname',        // Added lname
        'email',
        'password',
        // Removed 'two_factor_enabled' since we're not using it
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        // Removed 'two_factor_enabled' since we're not using it
    ];

    // Override the password mutator to handle hashing
    public function setPasswordAttribute($value)
    {
        if ($value !== null) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    // Check if user needs to set password
    public function needsPasswordSetup()
    {
        return is_null($this->password);
    }
}