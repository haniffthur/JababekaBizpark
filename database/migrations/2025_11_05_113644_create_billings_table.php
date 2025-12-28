<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_amount', 15, 2);
            
            // Status lengkap
            $table->enum('status', ['unpaid', 'pending_verification', 'paid', 'rejected'])->default('unpaid');
            
            $table->date('due_date');
            $table->string('proof_image')->nullable(); // Bukti Transfer
            $table->text('description')->nullable(); // Keterangan tambahan (Opsional)
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};