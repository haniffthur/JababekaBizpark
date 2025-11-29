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
    Schema::create('gate_machines', function (Blueprint $table) {
        $table->id();
        $table->string('termno')->unique(); // "001", "002"
        $table->string('location')->nullable(); // "Gerbang Depan"
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gate_machines');
    }
};
