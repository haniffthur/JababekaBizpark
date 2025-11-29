<?php

// app/Models/QrCode.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'truck_id',
        'code',
        'status',
        'expired_at',
        'is_approved',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'status' => 'string',
    ];

    // --- RELASI ---

    /**
     * QR Code ini milik satu Truk
     */
    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }
}
