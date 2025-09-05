<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add status column to tb_mas_applicant_data with default 'new'.
     */
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_applicant_data') && !Schema::hasColumn('tb_mas_applicant_data', 'status')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                $table->string('status', 20)->default('new')->after('data');
            });
        }
    }

    /**
     * Drop status column.
     */
    public function down(): void
    {
        if (Schema::hasTable('tb_mas_applicant_data') && Schema::hasColumn('tb_mas_applicant_data', 'status')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
