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
    Schema::create('daily_charges', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('truck_id')->constrained()->onDelete('cascade'); // Referensi truk mana
        $table->decimal('amount', 15, 2); // Biaya inap
        $table->date('charge_date'); // Tanggal kejadian
        $table->boolean('is_billed')->default(false); // Apakah sudah masuk tagihan bulanan?
        $table->foreignId('ipl_bill_id')->nullable(); // Nanti diisi ID tagihan bulanan
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_charges');
    }
};
