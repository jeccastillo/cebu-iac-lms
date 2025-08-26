<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_classlist') && Schema::hasColumn('tb_mas_classlist', 'sectionCode')) {
            // Backfill sectionCode by concatenating strClassName + year + strSection + sub_section (in this order)
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

    public function down(): void
    {
        // Revert backfill by nulling out sectionCode values we previously set (best-effort)
        if (Schema::hasTable('tb_mas_classlist') && Schema::hasColumn('tb_mas_classlist', 'sectionCode')) {
            DB::statement("
                UPDATE tb_mas_classlist
                SET sectionCode = NULL
                WHERE sectionCode = CONCAT(
                    COALESCE(TRIM(strClassName), ''),
                    COALESCE(TRIM(CAST(`year` AS CHAR)), ''),
                    COALESCE(TRIM(strSection), ''),
                    COALESCE(TRIM(sub_section), '')
                )
            ");
        }
    }
};
