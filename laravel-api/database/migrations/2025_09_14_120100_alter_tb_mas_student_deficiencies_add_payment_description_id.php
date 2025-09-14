<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing column to align with current service code
        if (Schema::hasTable('tb_mas_student_deficiencies') && !Schema::hasColumn('tb_mas_student_deficiencies', 'payment_description_id')) {
            Schema::table('tb_mas_student_deficiencies', function (Blueprint $table) {
                // Place after 'department' which is present on the live table (not 'department_code')
                $table->integer('payment_description_id')->nullable()->index()->after('department');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_student_deficiencies') && Schema::hasColumn('tb_mas_student_deficiencies', 'payment_description_id')) {
            Schema::table('tb_mas_student_deficiencies', function (Blueprint $table) {
                $table->dropColumn('payment_description_id');
            });
        }
    }
};
