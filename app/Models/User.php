<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    public const ROLES = ['administrator', 'supervisor', 'admin', 'jamaah'];

    public const ROLE_LABELS = [
        'administrator' => 'Super Admin',
        'supervisor'    => 'Supervisor',
        'admin'         => 'Admin',
        'jamaah'        => 'Jamaah',
    ];

    protected $fillable = [
        'name',
        'email',
        'nomor_telepon',
        'role',
        'status_akun',
        'password',
        'must_change_password',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Role checking & routing helpers
     */
    public function isAdministrator(): bool
    {
        return $this->role === 'administrator';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isJamaah(): bool
    {
        return $this->role === 'jamaah';
    }

    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            'administrator' => 'administrator.dashboard',
            'admin'         => 'admin.dashboard',
            'supervisor'    => 'supervisor.dashboard',
            default         => 'jamaah.dashboard',
        };
    }

    public function roleLabel(): string
    {
        return self::ROLE_LABELS[$this->role] ?? ucfirst((string) $this->role);
    }

    public function isActive(): bool
    {
        return $this->status_akun === 'active';
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'id_pengguna');
    }

    public function notifikasis()
    {
        return $this->hasMany(Notifikasi::class, 'id_pengguna');
    }

    public function verifiedDokumens()
    {
        return $this->hasMany(Dokumen::class, 'verified_by');
    }

    public function verifiedPembayarans()
    {
        return $this->hasMany(RiwayatPembayaran::class, 'verified_by');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
