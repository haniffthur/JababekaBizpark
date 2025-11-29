<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trucks', function (Blueprint $table) {
            $table->id();
            // Relasi ke pemilik truk (Member)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('license_plate')->unique(); // Plat Nomor
            $table->string('driver_name')->nullable();
            // Status untuk melacak truk ada di dalam atau di luar
            $table->boolean('is_inside')->default(false); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trucks');
    }
};