<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_applicant_types')) {
            Schema::table('tb_mas_applicant_types', function (Blueprint $table) {
                if (!Schema::hasColumn('tb_mas_applicant_types', 'sub_type')) {
                    $table->string('sub_type', 255)->nullable()->after('type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_applicant_types')) {
            Schema::table('tb_mas_applicant_types', function (Blueprint $table) {
                if (Schema::hasColumn('tb_mas_applicant_types', 'sub_type')) {
                    $table->dropColumn('sub_type');
                }
            });
        }
    }
};
