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
            
            // Truk (Nullable, karena bisa jadi kendaraan pribadi)
            $table->foreignId('truck_id')->nullable()->constrained('trucks')->onDelete('set null');
            
            // User Pribadi (Nullable, karena bisa jadi truk)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Plat Nomor (Disimpan sebagai teks untuk history, walau truk dihapus log tetap ada platnya)
            $table->string('license_plate')->nullable();
            
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->string('status'); 
            $table->text('notes')->nullable();
            $table->decimal('billing_amount', 15, 2)->nullable();
            $table->timestamps();

            // Indexes
            $table->index('check_in_at');
            $table->index('check_out_at');
            $table->index('license_plate');
            $table->index(['truck_id', 'check_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gate_logs');
    }
};