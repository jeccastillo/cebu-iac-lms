<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_classlist')) {
            if (!Schema::hasColumn('tb_mas_classlist', 'sectionCode')) {
                Schema::table('tb_mas_classlist', function (Blueprint $table) {
                    $table->string('sectionCode', 50)->nullable()->after('strSection');
                });
            }

            // Backfill sectionCode by concatenating strClassName, year, strSection, sub_section (in this order)
            if (Schema::hasColumn('tb_mas_classlist', 'sectionCode')) {
                DB::statement("
                    UPDATE tb_mas_classlist
                    SET sectionCode = CONCAT(
                        COALESCE(TRIM(strClassName), ''),
                        COALESCE(TRIM(CAST(`year` AS CHAR)), ''),
                        COALESCE(TRIM(strSection), ''),
                        COALESCE(TRIM(sub_section), '')
                    )
                    WHERE sectionCode IS NULL OR sectionCode = ''
                ");
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_classlist') && Schema::hasColumn('tb_mas_classlist', 'sectionCode')) {
            Schema::table('tb_mas_classlist', function (Blueprint $table) {
                $table->dropColumn('sectionCode');
            });
        }
    }
};
