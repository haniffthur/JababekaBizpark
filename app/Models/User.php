<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany; // Import


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Tambahkan 'role'
        'ipl_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => 'string', // Definisikan casting untuk 'role'
    ];

    // --- RELASI ---

    /**
     * Relasi ke Truk (User Tipe 'Member' memiliki banyak Truk)
     */
    public function trucks(): HasMany
    {
        return $this->hasMany(Truck::class);
    }

    /**
     * Relasi ke Tagihan (User Tipe 'Member' memiliki banyak Tagihan)
     */
    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class);
    }

    // --- Helper ---
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    public function personalQrs(): HasMany
    {
        return $this->hasMany(PersonalQr::class);
    }
}