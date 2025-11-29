<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GateLog extends Model
{
    use HasFactory;

    /**
     * Atribut yang boleh diisi.
     */
    protected $fillable = [
        'truck_id',
        'user_id', // <-- WAJIB TAMBAH
        'license_plate', // <-- WAJIB TAMBAH
        'check_in_at',
        'check_out_at',
        'status',
        'notes',
        'billing_amount',
    ];

    /**
     * Tipe data (casting) untuk kolom.
     */
    protected $casts = [
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    /**
     * Relasi ke Truk (untuk log Truk)
     */
    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    /**
     * Relasi ke User (untuk log Pribadi)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}