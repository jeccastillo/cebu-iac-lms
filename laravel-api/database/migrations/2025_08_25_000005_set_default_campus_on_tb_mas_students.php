<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $table = 'tb_mas_students';

        if (!Schema::hasTable($table)) {
            // Legacy table not present in this environment; nothing to do.
            return;
        }

        // If campus_id is missing, add it with DEFAULT 1, index, and optional FK.
        if (!Schema::hasColumn($table, 'campus_id')) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedInteger('campus_id')->nullable()->default(1);
                $t->index('campus_id', 'idx_students_campus_id');
            });

            if (Schema::hasTable('tb_mas_campuses')) {
                // Add FK (best-effort; ignore if engine/rows prevent it)
                try {
                    Schema::table($table, function (Blueprint $t) {
                        $t->foreign('campus_id', 'fk_students_campus_id')
                          ->references('id')
                          ->on('tb_mas_campuses')
                          ->nullOnDelete();
                    });
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            return;
        }

        // Column exists: set DEFAULT 1 without requiring doctrine/dbal by using raw SQL.
        try {
            DB::statement('ALTER TABLE `tb_mas_students` MODIFY COLUMN `campus_id` INT UNSIGNED NULL DEFAULT 1');
        } catch (\Throwable $e) {
            // Fallback syntax (MySQL supports ALTER ... ALTER COLUMN ... SET DEFAULT)
            try {
                DB::statement('ALTER TABLE `tb_mas_students` ALTER COLUMN `campus_id` SET DEFAULT 1');
            } catch (\Throwable $e2) {
                // ignore
            }
        }

        // Ensure index exists (best-effort).
        try {
            Schema::table($table, function (Blueprint $t) {
                $t->index('campus_id', 'idx_students_campus_id');
            });
        } catch (\Throwable $e) {
            // ignore
        }

        // Optionally ensure FK exists if campuses table is present (best-effort).
        if (Schema::hasTable('tb_mas_campuses')) {
            try {
                Schema::table($table, function (Blueprint $t) {
                    $t->foreign('campus_id', 'fk_students_campus_id')
                      ->references('id')
                      ->on('tb_mas_campuses')
                      ->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    public function down(): void
    {
        $table = 'tb_mas_students';

        if (!Schema::hasTable($table)) {
            return;
        }

        // Drop FK if present (best-effort).
        try {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign('fk_students_campus_id');
            });
        } catch (\Throwable $e) {
            // ignore
        }

        // Drop index if present (best-effort).
        try {
            Schema::table($table, function (Blueprint $t) {
                $t->dropIndex('idx_students_campus_id');
            });
        } catch (\Throwable $e) {
            // ignore
        }

        if (Schema::hasColumn($table, 'campus_id')) {
            // Revert default to NULL (do not drop the column unconditionally since it may be legacy).
            try {
                DB::statement('ALTER TABLE `tb_mas_students` MODIFY COLUMN `campus_id` INT UNSIGNED NULL DEFAULT NULL');
            } catch (\Throwable $e) {
                try {
                    DB::statement('ALTER TABLE `tb_mas_students` ALTER COLUMN `campus_id` DROP DEFAULT');
                } catch (\Throwable $e2) {
                    // ignore
                }
            }
        }
    }
};
