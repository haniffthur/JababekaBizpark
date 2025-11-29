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
    Schema::table('ipl_bills', function (Blueprint $table) {
        $table->string('proof_image')->nullable()->after('amount');
    });
}

public function down(): void
{
    Schema::table('ipl_bills', function (Blueprint $table) {
        $table->dropColumn('proof_image');
    });
}
};
