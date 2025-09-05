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

        if (!Schema::hasColumn('tb_mas_applicant_data', 'syid')) {
            // Try to place syid after status -> data -> user_id (if present), else omit AFTER
            $afterBase = null;
            if (Schema::hasColumn('tb_mas_applicant_data', 'status')) {
                $afterBase = 'status';
            } elseif (Schema::hasColumn('tb_mas_applicant_data', 'data')) {
                $afterBase = 'data';
            } elseif (Schema::hasColumn('tb_mas_applicant_data', 'user_id')) {
                $afterBase = 'user_id';
            }

            Schema::table('tb_mas_applicant_data', function (Blueprint $table) use ($afterBase) {
                $col = $table->unsignedInteger('syid')->nullable()->index('idx_applicant_data_syid');
                if ($afterBase) {
                    $col->after($afterBase);
                }
            });

            // NOTE: Intentionally NOT adding a foreign key to tb_mas_sy here due to prior FK issues (errno 150).
            // This keeps the column flexible across environments. If needed, FK can be added later via a separate migration.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tb_mas_applicant_data')) {
            return;
        }

        if (Schema::hasColumn('tb_mas_applicant_data', 'syid')) {
            // Drop FK if present (guarded)
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                try {
                    $table->dropForeign('fk_applicant_data_syid');
                } catch (\Throwable $e) {
                    try {
                        $table->dropForeign(['syid']);
                    } catch (\Throwable $e2) {
                        // ignore
                    }
                }
            });

            // Drop index if present (ignore failures)
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_applicant_data_syid');
                } catch (\Throwable $e) {
                    // ignore
                }
            });

            // Drop column
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                $table->dropColumn('syid');
            });
        }
    }
};
