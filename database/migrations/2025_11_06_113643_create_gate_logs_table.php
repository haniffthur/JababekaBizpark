<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gate_logs', function (Blueprint $table) {
            $table->id();
            // Relasi ke truk yang di-scan
            $table->foreignId('truck_id')->constrained('trucks');
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->string('status'); // Misal: "Berhasil Masuk", "Berhasil Keluar", "Gagal: Plat Tidak Cocok"
            $table->text('notes')->nullable();
            // Nominal tagihan jika menginap (sesuai flowchart)
            $table->decimal('billing_amount', 15, 2)->nullable(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gate_logs');
    }
};
