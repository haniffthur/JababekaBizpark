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
       Schema::create('personal_qrs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Pemilik QR
    $table->string('name'); // Misal: "Pribadi 1", "Pribadi 2"
    $table->string('license_plate')->unique(); // Plat nomor yang terikat
    $table->string('code')->unique(); // Kode QR unik
    $table->enum('status', ['baru', 'aktif'])->default('baru'); // Status (Reusable: baru/aktif)
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_qrs');
    }
};
