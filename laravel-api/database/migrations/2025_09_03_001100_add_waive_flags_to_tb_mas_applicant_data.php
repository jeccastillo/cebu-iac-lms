<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_applicant_data')) {
            return;
        }

        // Try to position after existing logical columns for readability
        $afterBase = null;
        if (Schema::hasColumn('tb_mas_applicant_data', 'status')) {
            $afterBase = 'status';
        } elseif (Schema::hasColumn('tb_mas_applicant_data', 'data')) {
            $afterBase = 'data';
        }

        // Add waive_application_fee (boolean, default false)
        if (!Schema::hasColumn('tb_mas_applicant_data', 'waive_application_fee')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) use ($afterBase) {
                $col = $table->boolean('waive_application_fee')->default(false);
                if ($afterBase) {
                    $col->after($afterBase);
                }
            });
        }

        // Add waive_reason (string, nullable)
        $afterSecond = Schema::hasColumn('tb_mas_applicant_data', 'waive_application_fee') ? 'waive_application_fee' : $afterBase;
        if (!Schema::hasColumn('tb_mas_applicant_data', 'waive_reason')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) use ($afterSecond) {
                $col = $table->string('waive_reason', 255)->nullable();
                if ($afterSecond) {
                    $col->after($afterSecond);
                }
            });
        }

        // Add waived_at (timestamp, nullable)
        $afterThird = Schema::hasColumn('tb_mas_applicant_data', 'waive_reason') ? 'waive_reason' : $afterSecond;
        if (!Schema::hasColumn('tb_mas_applicant_data', 'waived_at')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) use ($afterThird) {
                $col = $table->timestamp('waived_at')->nullable();
                if ($afterThird) {
                    $col->after($afterThird);
                }
            });
        }

        // Add waived_by_user_id (unsigned integer, nullable) — no FK to avoid prior FK issues on this table
        $afterFourth = Schema::hasColumn('tb_mas_applicant_data', 'waived_at') ? 'waived_at' : $afterThird;
        if (!Schema::hasColumn('tb_mas_applicant_data', 'waived_by_user_id')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) use ($afterFourth) {
                $col = $table->unsignedInteger('waived_by_user_id')->nullable();
                if ($afterFourth) {
                    $col->after($afterFourth);
                }
                // Index for potential auditing/reporting queries
                $table->index('waived_by_user_id', 'idx_applicant_waived_by_user_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tb_mas_applicant_data')) {
            return;
        }

        if (Schema::hasColumn('tb_mas_applicant_data', 'waived_by_user_id')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_applicant_waived_by_user_id');
                } catch (\Throwable $e) {
                    // ignore index drop failures
                }
                $table->dropColumn('waived_by_user_id');
            });
        }

        if (Schema::hasColumn('tb_mas_applicant_data', 'waived_at')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                $table->dropColumn('waived_at');
            });
        }

        if (Schema::hasColumn('tb_mas_applicant_data', 'waive_reason')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                $table->dropColumn('waive_reason');
            });
        }

        if (Schema::hasColumn('tb_mas_applicant_data', 'waive_application_fee')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                $table->dropColumn('waive_application_fee');
            });
        }
    }
};
