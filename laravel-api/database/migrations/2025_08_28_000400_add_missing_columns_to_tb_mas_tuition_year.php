<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing columns to tb_mas_tuition_year with guards to avoid duplicate additions.
     */
    public function up(): void
    {
        Schema::table('tb_mas_tuition_year', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_mas_tuition_year', 'installmentFixed')) {
                // Fixed down payment amount (if null or 0, use percentage DP)
                $table->decimal('installmentFixed', 10, 2)->nullable()->after('installmentDP');
            }
            if (!Schema::hasColumn('tb_mas_tuition_year', 'freeElectiveCount')) {
                // SHS: number of free elective subjects
                $table->integer('freeElectiveCount')->nullable()->after('installmentFixed');
            }
            if (!Schema::hasColumn('tb_mas_tuition_year', 'final')) {
                // UI lock flag; when 1, disable edits in UI
                $table->tinyInteger('final')->default(0)->after('freeElectiveCount');
            }
        });
    }

    /**
     * Drop added columns if present.
     */
    public function down(): void
    {
        Schema::table('tb_mas_tuition_year', function (Blueprint $table) {
            if (Schema::hasColumn('tb_mas_tuition_year', 'final')) {
                $table->dropColumn('final');
            }
            if (Schema::hasColumn('tb_mas_tuition_year', 'freeElectiveCount')) {
                $table->dropColumn('freeElectiveCount');
            }
            if (Schema::hasColumn('tb_mas_tuition_year', 'installmentFixed')) {
                $table->dropColumn('installmentFixed');
            }
        });
    }
};
