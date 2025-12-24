<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyCharge extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment).
     * Wajib ada agar fungsi DailyCharge::create() di Job berjalan.
     */
    protected $fillable = [
        'user_id',
        'truck_id',
        'amount',
        'charge_date',
        'is_billed',
        'ipl_bill_id',
    ];

    /**
     * Mengubah format data saat diambil dari database.
     */
    protected $casts = [
        'charge_date' => 'date',   // Agar otomatis jadi objek Carbon
        'is_billed' => 'boolean',  // Agar jadi true/false, bukan 1/0
        'amount' => 'float',       // Agar mudah dihitung
    ];

    // =========================================================================
    // RELASI ANTAR TABEL
    // =========================================================================

    /**
     * Relasi: Tagihan harian ini milik siapa?
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Truk mana yang menyebabkan biaya ini?
     */
    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }

    /**
     * Relasi: Masuk ke tagihan bulanan (Billing) nomor berapa?
     * (Akan terisi nanti setelah tanggal 1)
     */
    public function billing()
    {
        return $this->belongsTo(Billing::class, 'ipl_bill_id');
    }
}