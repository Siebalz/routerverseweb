<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
            'otp_enabled' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Cek apakah user ini admin (boleh kelola produk).
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Admin wajib memakai kode OTP saat login (selama fitur OTP menyala). */
    public function otpRequiredByRole(): bool
    {
        return (bool) config('security.otp.enabled')
            && (bool) config('security.otp.admin_required')
            && $this->isAdmin();
    }

    /** Apakah login akun ini harus melewati kode OTP dari email. */
    public function requiresOtp(): bool
    {
        if (! config('security.otp.enabled')) {
            return false;
        }

        return $this->otpRequiredByRole() || (bool) $this->otp_enabled;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function topups()
    {
        return $this->hasMany(Topup::class);
    }

    public function pointLedgers()
    {
        return $this->hasMany(PointLedger::class);
    }

    public function vpnAccounts()
    {
        return $this->hasMany(VpnAccount::class);
    }
}
