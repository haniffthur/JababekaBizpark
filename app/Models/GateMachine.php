<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateMachine extends Model
{
    use HasFactory;

    protected $fillable = [
        'termno',
        'location',
        // 'ip_address', // <-- HAPUS INI
    ];
}