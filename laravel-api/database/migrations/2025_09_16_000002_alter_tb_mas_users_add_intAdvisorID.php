<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tb_mas_users', 'intAdvisorID')) {
            Schema::table('tb_mas_users', function (Blueprint $table) {
                $table->integer('intAdvisorID')->nullable()->index()->after('intProgramID');
            });
        }

        // Optional safe backfill (no-op if advisor table empty or no active assignments)
        // This is safe and will not fail if tb_mas_student_advisor does not exist yet (guarded by hasTable)
        if (Schema::hasTable('tb_mas_student_advisor')) {
            // Backfill only when there is exactly one active assignment per student
            // Note: unique(intStudentID, is_active) already enforces at most one active row per student.
            try {
                DB::statement("
                    UPDATE tb_mas_users u
                    JOIN tb_mas_student_advisor sa
                      ON sa.intStudentID = u.intID
                     AND sa.is_active = 1
                    SET u.intAdvisorID = sa.intAdvisorID
                ");
            } catch (\Throwable $e) {
                // Log but ignore to keep migration resilient across envs
                // \Log::warning('Backfill intAdvisorID skipped: ' . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tb_mas_users', 'intAdvisorID')) {
            Schema::table('tb_mas_users', function (Blueprint $table) {
                $table->dropIndex(['intAdvisorID']);
                $table->dropColumn('intAdvisorID');
            });
        }
    }
};
