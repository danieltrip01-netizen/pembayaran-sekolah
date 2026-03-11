<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nama_lengkap',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relasi
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function setoran()
    {
        return $this->hasMany(Setoran::class);
    }

    // Helper methods
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdminJenjang(string $jenjang): bool
    {
        return $this->role === 'admin_' . strtolower($jenjang);
    }

    public function isAdminYayasan(): bool
    {
        return $this->hasRole('admin_yayasan');
    }

    public function isAdminTK(): bool
    {
        return $this->hasRole('admin_tk');
    }

    public function isAdminSD(): bool
    {
        return $this->hasRole('admin_sd');
    }

    public function isAdminSMP(): bool
    {
        return $this->hasRole('admin_smp');
    }

    public function getJenjangAttribute(): ?string
    {
        return match($this->role) {
            'admin_tk'  => 'TK',
            'admin_sd'  => 'SD',
            'admin_smp' => 'SMP',
            default     => null,
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'admin_yayasan' => 'Admin Yayasan',
            'admin_tk'      => 'Admin TK/PAUD',
            'admin_sd'      => 'Admin SD',
            'admin_smp'     => 'Admin SMP',
            default         => 'Unknown',
        };
    }
}