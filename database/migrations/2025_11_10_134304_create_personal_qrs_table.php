<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_qrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name'); // Label: "Mobil Pribadi"
            $table->string('license_plate')->unique();
            $table->string('code')->unique();
            $table->enum('status', ['baru', 'aktif'])->default('baru');
            $table->boolean('is_approved')->default(true); // Default auto-approve atau false
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_qrs');
    }
};