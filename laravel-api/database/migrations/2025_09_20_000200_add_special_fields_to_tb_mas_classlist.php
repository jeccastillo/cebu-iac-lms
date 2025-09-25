<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_classlist')) {
            return;
        }

        Schema::table('tb_mas_classlist', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_mas_classlist', 'special_class')) {
                $table->boolean('special_class')->default(0)->after('intFacultyID');
            }
            if (!Schema::hasColumn('tb_mas_classlist', 'special_multiplier')) {
                // decimal(8,4) default 1.0000; nullable for flexibility but default provided
                $table->decimal('special_multiplier', 8, 4)->nullable()->default(1.0000)->after('special_class');
            }
        });

        // Best-effort guard: normalize any invalid multipliers (<=0) to 1.0000 where special_class=1
        try {
            DB::statement("
                UPDATE tb_mas_classlist
                SET special_multiplier = 1.0000
                WHERE special_class = 1 AND (special_multiplier IS NULL OR special_multiplier <= 0)
            ");
        } catch (\Throwable $e) {
            // ignore if DB driver does not support this exact SQL
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tb_mas_classlist')) {
            return;
        }

        Schema::table('tb_mas_classlist', function (Blueprint $table) {
            if (Schema::hasColumn('tb_mas_classlist', 'special_multiplier')) {
                $table->dropColumn('special_multiplier');
            }
            if (Schema::hasColumn('tb_mas_classlist', 'special_class')) {
                $table->dropColumn('special_class');
            }
        });
    }
};
