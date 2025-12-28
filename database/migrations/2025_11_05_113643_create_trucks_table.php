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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('license_plate')->unique();
            $table->string('driver_name')->nullable();
            $table->boolean('is_inside')->default(false);
            $table->timestamps();

            // Indexes (Optimasi performa)
            $table->index('is_inside');
            $table->index(['user_id', 'is_inside']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trucks');
    }
};