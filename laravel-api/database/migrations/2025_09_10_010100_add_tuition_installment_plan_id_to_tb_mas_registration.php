<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_registration') && !Schema::hasColumn('tb_mas_registration', 'tuition_installment_plan_id')) {
            Schema::table('tb_mas_registration', function (Blueprint $table) {
                // Nullable, we won't add a strict FK to avoid legacy constraint issues
                $table->unsignedInteger('tuition_installment_plan_id')->nullable()->after('tuition_year');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_registration') && Schema::hasColumn('tb_mas_registration', 'tuition_installment_plan_id')) {
            Schema::table('tb_mas_registration', function (Blueprint $table) {
                $table->dropColumn('tuition_installment_plan_id');
            });
        }
    }
};
