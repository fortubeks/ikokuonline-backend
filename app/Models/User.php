<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Support\Carbon;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email_verification_code',
        'email_verification_expires_at',
        'password_reset_otp',
        'password_reset_expires_at',
        'email',
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
            'password' => 'hashed',
        ];
    }

    public function generateEmailVerificationOtp(): string
    {
        $otp = rand(100000, 999999);

        $this->update([
            'email_verification_code' => $otp,
            //'email_verification_expires_at' => now()->addMinutes(15),
            'email_verification_expires_at' => date("Y-m-d h:i:s",strtotime(" + 15 minutes")),
        ]);

        return $otp;
    }

    public function generatePasswordResetOtp(): string
    {
        $otp = rand(100000, 999999);

        $this->update([
            'password_reset_otp' => $otp,
            'password_reset_expires_at' => now()->addMinutes(15),
        ]);

        return $otp;
    }
}
