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
    Schema::table('personal_qrs', function (Blueprint $table) {
        // Default true agar data lama tidak error
        $table->boolean('is_approved')->default(true)->after('status'); 
    });
}

public function down(): void
{
    Schema::table('personal_qrs', function (Blueprint $table) {
        $table->dropColumn('is_approved');
    });
}
};
