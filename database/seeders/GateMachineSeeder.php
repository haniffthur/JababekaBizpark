<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GateMachine;

class GateMachineSeeder extends Seeder
{
    public function run(): void
    {
        // Cukup termno dan lokasi
        GateMachine::create(['termno' => '001', 'location' => 'Pintu Masuk Utama']);
        GateMachine::create(['termno' => '002', 'location' => 'Pintu Keluar Utama']);
    
        
        // Jika mau tambah mesin lagi
        // GateMachine::create(['termno' => '003', 'location' => 'Pintu Masuk Belakang']);
        // GateMachine::create(['termno' => '004', 'location' => 'Pintu Keluar Belakang']);
    }
}