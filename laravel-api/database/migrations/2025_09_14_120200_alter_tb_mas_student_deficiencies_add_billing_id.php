<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_student_deficiencies') && !Schema::hasColumn('tb_mas_student_deficiencies', 'billing_id')) {
            Schema::table('tb_mas_student_deficiencies', function (Blueprint $table) {
                // Add after payment_description_id if present, else after department
                if (Schema::hasColumn('tb_mas_student_deficiencies', 'payment_description_id')) {
                    $table->integer('billing_id')->index()->after('payment_description_id');
                } else {
                    $table->integer('billing_id')->index()->after('department');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_student_deficiencies') && Schema::hasColumn('tb_mas_student_deficiencies', 'billing_id')) {
            Schema::table('tb_mas_student_deficiencies', function (Blueprint $table) {
                $table->dropColumn('billing_id');
            });
        }
    }
};
