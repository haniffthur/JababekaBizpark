<?php

// app/Models/Truck.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import
use Illuminate\Database\Eloquent\Relations\HasMany;   // Import

class Truck extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_plate',
        'driver_name',
        'is_inside',
    ];

    protected $casts = [
        'is_inside' => 'boolean', // Otomatis konversi ke true/false
    ];

    // --- RELASI ---

    /**
     * Relasi ke Pemilik (User Tipe 'Member')
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Truk ini bisa punya banyak QR Code
     */
    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }

    /**
     * Truk ini punya banyak histori Gate Log
     */
    public function gateLogs(): HasMany
    {
        return $this->hasMany(GateLog::class);
    }
}