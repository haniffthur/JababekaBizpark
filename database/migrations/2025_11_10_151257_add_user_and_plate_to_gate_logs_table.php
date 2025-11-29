<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('gate_logs', function (Blueprint $table) {
        // Kolom 'truck_id' sudah nullable (dari error sebelumnya)
        
        // Tambah user_id untuk log pribadi
        $table->foreignId('user_id')->nullable()->after('truck_id')
              ->constrained('users')->onDelete('set null');
              
        // Tambah license_plate untuk log pribadi
        $table->string('license_plate')->nullable()->after('user_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gate_logs', function (Blueprint $table) {
            //
        });
    }
};
