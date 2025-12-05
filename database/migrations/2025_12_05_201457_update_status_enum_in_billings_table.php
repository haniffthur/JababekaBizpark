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
    // Kita ubah kolom status agar support value baru
    // Catatan: Mengubah ENUM di MySQL butuh raw statement
    DB::statement("ALTER TABLE billings MODIFY COLUMN status ENUM('unpaid', 'pending_verification', 'paid', 'rejected') DEFAULT 'unpaid'");
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            //
        });
    }
};
