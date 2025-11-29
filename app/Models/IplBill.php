<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IplBill extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'period', 'amount', 'status', 'paid_at', 'proof_image'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
