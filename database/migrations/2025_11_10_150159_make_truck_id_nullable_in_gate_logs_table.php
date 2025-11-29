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
            // Ubah kolom truck_id agar boleh null (nullable)
            $table->foreignId('truck_id')->nullable()->change();
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
