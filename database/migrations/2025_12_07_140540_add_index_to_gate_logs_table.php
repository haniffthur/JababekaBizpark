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
    Schema::table('gate_logs', function (Blueprint $table) {
        $table->index('check_in_at');
        $table->index('check_out_at');
        $table->index('license_plate');
        $table->index(['truck_id', 'check_in_at']);
    });

    Schema::table('billings', function (Blueprint $table) {
        $table->index(['user_id', 'status']);
        $table->index('due_date');
    });

    Schema::table('trucks', function (Blueprint $table) {
        $table->index('is_inside');
        $table->index(['user_id', 'is_inside']);
    });
}

public function down(): void
{
    Schema::table('gate_logs', function (Blueprint $table) {
        $table->dropIndex(['check_in_at']);
        $table->dropIndex(['check_out_at']);
        $table->dropIndex(['license_plate']);
        $table->dropIndex(['truck_id', 'check_in_at']);
    });

    Schema::table('billings', function (Blueprint $table) {
        $table->dropIndex(['user_id', 'status']);
        $table->dropIndex(['due_date']);
    });

    Schema::table('trucks', function (Blueprint $table) {
        $table->dropIndex(['is_inside']);
        $table->dropIndex(['user_id', 'is_inside']);
    });
}

};
