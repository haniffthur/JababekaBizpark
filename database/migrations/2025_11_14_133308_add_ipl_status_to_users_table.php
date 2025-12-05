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
    Schema::table('users', function (Blueprint $table) {
        // Tambah kolom status IPL. Default-nya 'unpaid' (Belum Bayar)
        $table->enum('ipl_status', ['paid', 'unpaid','pending'])
              ->default('unpaid')
              ->after('role');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('ipl_status');
    });
}
};
