<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Problem:
     * The original schema created a UNIQUE index on (intStudentID, is_active).
     * This prevents keeping multiple history rows with is_active=0 for the same student.
     * During a switch or re-assign, updating the active row to is_active=0 collides with an existing
     * inactive row, causing: Duplicate entry '{studentId}-0' for key 'uniq_active_advisor_per_student'.
     *
     * Fix:
     * - Drop the old unique index.
     * - Add a generated column active_student_id = CASE WHEN is_active=1 THEN intStudentID ELSE NULL END.
     * - Add a UNIQUE index on active_student_id. MySQL allows multiple NULLs in a UNIQUE index,
     *   so only active rows (non-NULL) are restricted to be unique per student.
     */
    public function up(): void
    {
        // 1) Drop the old unique index on (intStudentID, is_active)
        try {
            DB::statement('ALTER TABLE `tb_mas_student_advisor` DROP INDEX `uniq_active_advisor_per_student`');
        } catch (\Throwable $e) {
            // ignore if it does not exist
        }

        // 2) Add generated column active_student_id (stored)
        try {
            DB::statement("
                ALTER TABLE `tb_mas_student_advisor`
                ADD COLUMN `active_student_id` INT
                GENERATED ALWAYS AS (CASE WHEN `is_active` = 1 THEN `intStudentID` ELSE NULL END) STORED
            ");
        } catch (\Throwable $e) {
            // If column already exists or server doesn't support generated columns, ignore.
            // (Given project context uses MySQL with generated columns enabled.)
        }

        // 3) Add UNIQUE index on active_student_id (enforces "only one active per student")
        try {
            DB::statement('ALTER TABLE `tb_mas_student_advisor` ADD UNIQUE INDEX `uniq_active_student` (`active_student_id`)');
        } catch (\Throwable $e) {
            // ignore if already exists
        }

        // Optional: keep a non-unique composite index to help lookups by student+active flag (only if helpful)
        // Not strictly necessary since there is already idx_studentid, but harmless if added.
        try {
            DB::statement('CREATE INDEX `idx_student_active_flag` ON `tb_mas_student_advisor` (`intStudentID`, `is_active`)');
        } catch (\Throwable $e) {
            // ignore if exists or not supported
        }
    }

    public function down(): void
    {
        // Drop new unique and column
        try {
            DB::statement('ALTER TABLE `tb_mas_student_advisor` DROP INDEX `uniq_active_student`');
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            DB::statement('ALTER TABLE `tb_mas_student_advisor` DROP COLUMN `active_student_id`');
        } catch (\Throwable $e) {
            // ignore
        }

        // Drop helper index if present
        try {
            DB::statement('ALTER TABLE `tb_mas_student_advisor` DROP INDEX `idx_student_active_flag`');
        } catch (\Throwable $e) {
            // ignore
        }

        // Restore the original unique index (note: will reintroduce the prior limitation)
        try {
            DB::statement('ALTER TABLE `tb_mas_student_advisor` ADD UNIQUE INDEX `uniq_active_advisor_per_student` (`intStudentID`, `is_active`)');
        } catch (\Throwable $e) {
            // ignore if cannot restore
        }
    }
};
