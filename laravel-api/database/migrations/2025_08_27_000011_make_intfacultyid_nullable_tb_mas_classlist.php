<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_classlist') && Schema::hasColumn('tb_mas_classlist', 'intFacultyID')) {
            // Make intFacultyID nullable (allow NULL)
            try {
                DB::statement('ALTER TABLE `tb_mas_classlist` MODIFY COLUMN `intFacultyID` INT NULL');
            } catch (\Throwable $e) {
                // Fallback for unsigned/signed differences
                try {
                    DB::statement('ALTER TABLE `tb_mas_classlist` MODIFY COLUMN `intFacultyID` INT(11) NULL');
                } catch (\Throwable $e2) {
                    // Last resort: try unsigned
                    try {
                        DB::statement('ALTER TABLE `tb_mas_classlist` MODIFY COLUMN `intFacultyID` INT UNSIGNED NULL');
                    } catch (\Throwable $e3) {
                        throw $e3;
                    }
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_classlist') && Schema::hasColumn('tb_mas_classlist', 'intFacultyID')) {
            // Clean up NULLs to avoid NOT NULL failure on rollback
            try {
                DB::statement('UPDATE `tb_mas_classlist` SET `intFacultyID` = 0 WHERE `intFacultyID` IS NULL');
            } catch (\Throwable $e) {
                // ignore best-effort
            }

            // Revert to NOT NULL
            try {
                DB::statement('ALTER TABLE `tb_mas_classlist` MODIFY COLUMN `intFacultyID` INT NOT NULL');
            } catch (\Throwable $e) {
                try {
                    DB::statement('ALTER TABLE `tb_mas_classlist` MODIFY COLUMN `intFacultyID` INT(11) NOT NULL');
                } catch (\Throwable $e2) {
                    try {
                        DB::statement('ALTER TABLE `tb_mas_classlist` MODIFY COLUMN `intFacultyID` INT UNSIGNED NOT NULL');
                    } catch (\Throwable $e3) {
                        throw $e3;
                    }
                }
            }
        }
    }
};
