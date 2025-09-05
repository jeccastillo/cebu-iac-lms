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

        // Decide base column for positioning using AFTER (...), prefer 'status', fallback to 'data', else omit AFTER
        $afterBase = null;
        if (Schema::hasColumn('tb_mas_applicant_data', 'status')) {
            $afterBase = 'status';
        } elseif (Schema::hasColumn('tb_mas_applicant_data', 'data')) {
            $afterBase = 'data';
        }

        // Add paid_application_fee (boolean, default false)
        if (!Schema::hasColumn('tb_mas_applicant_data', 'paid_application_fee')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) use ($afterBase) {
                $col = $table->boolean('paid_application_fee')->default(false);
                if ($afterBase) {
                    $col->after($afterBase);
                }
            });
        }

        // Add paid_reservation_fee (boolean, default false)
        if (!Schema::hasColumn('tb_mas_applicant_data', 'paid_reservation_fee')) {
            // Try to place after paid_application_fee if it now exists; otherwise use $afterBase or omit
            $afterSecond = Schema::hasColumn('tb_mas_applicant_data', 'paid_application_fee') ? 'paid_application_fee' : $afterBase;

            Schema::table('tb_mas_applicant_data', function (Blueprint $table) use ($afterSecond) {
                $col = $table->boolean('paid_reservation_fee')->default(false);
                if ($afterSecond) {
                    $col->after($afterSecond);
                }
            });
        }

        // Add applicant_type (unsigned integer, nullable)
        if (!Schema::hasColumn('tb_mas_applicant_data', 'applicant_type')) {
            // Try to place after paid_reservation_fee if it now exists; otherwise use $afterBase or omit
            $afterThird = Schema::hasColumn('tb_mas_applicant_data', 'paid_reservation_fee') ? 'paid_reservation_fee' : $afterBase;

            Schema::table('tb_mas_applicant_data', function (Blueprint $table) use ($afterThird) {
                $col = $table->unsignedInteger('applicant_type')->nullable();
                if ($afterThird) {
                    $col->after($afterThird);
                }
            });
        }

        // Add foreign key to tb_mas_applicant_types(intID)
        if (
            Schema::hasTable('tb_mas_applicant_types') &&
            Schema::hasColumn('tb_mas_applicant_data', 'applicant_type')
        ) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                // Name the constraint explicitly to keep consistency and allow easier rollback
                $table->foreign('applicant_type', 'fk_applicant_data_applicant_type')
                    ->references('intID')
                    ->on('tb_mas_applicant_types')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tb_mas_applicant_data')) {
            return;
        }

        // Drop FK first if column exists
        if (Schema::hasColumn('tb_mas_applicant_data', 'applicant_type')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                // Attempt to drop by named key; ignore if not present
                try {
                    $table->dropForeign('fk_applicant_data_applicant_type');
                } catch (\Throwable $e) {
                    // Fallback: try dropping by column
                    try {
                        $table->dropForeign(['applicant_type']);
                    } catch (\Throwable $e2) {
                        // ignore
                    }
                }
            });
        }

        // Drop columns if present (reverse order)
        if (Schema::hasColumn('tb_mas_applicant_data', 'applicant_type')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                $table->dropColumn('applicant_type');
            });
        }

        if (Schema::hasColumn('tb_mas_applicant_data', 'paid_reservation_fee')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                $table->dropColumn('paid_reservation_fee');
            });
        }

        if (Schema::hasColumn('tb_mas_applicant_data', 'paid_application_fee')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                $table->dropColumn('paid_application_fee');
            });
        }
    }
};
