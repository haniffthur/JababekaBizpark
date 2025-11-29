<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            // Relasi ke truk yang akan menggunakan QR ini
            $table->foreignId('truck_id')->constrained('trucks')->onDelete('cascade');
            $table->string('code')->unique(); // Kode unik untuk QR
            // Status QR sesuai flowchart
            $table->enum('status', ['baru', 'aktif', 'selesai'])->default('baru');
            $table->timestamp('expired_at')->nullable(); // Opsional
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};