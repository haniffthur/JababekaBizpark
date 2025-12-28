<?php

// app/Models/Billing.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import

class Billing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'status' => 'string',
    ];

    // --- RELASI ---

    /**
     * Tagihan ini milik satu User (Member)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function dailyCharges()
    {
        // Hubungkan ke DailyCharge lewat kolom 'ipl_bill_id'
        return $this->hasMany(DailyCharge::class, 'ipl_bill_id');
    }
}
