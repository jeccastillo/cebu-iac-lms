<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_sy')) {
            return;
        }

        Schema::table('tb_mas_sy', function (Blueprint $table) {
            // Academic timeline
            if (!Schema::hasColumn('tb_mas_sy', 'start_of_classes')) {
                $table->dateTime('start_of_classes')->nullable()->after('end_of_submission');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'final_exam_start')) {
                $table->dateTime('final_exam_start')->nullable()->after('start_of_classes');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'final_exam_end')) {
                $table->dateTime('final_exam_end')->nullable()->after('final_exam_start');
            }

            // Viewing windows
            if (!Schema::hasColumn('tb_mas_sy', 'viewing_midterm_start')) {
                $table->dateTime('viewing_midterm_start')->nullable()->after('final_exam_end');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'viewing_midterm_end')) {
                $table->dateTime('viewing_midterm_end')->nullable()->after('viewing_midterm_start');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'viewing_final_start')) {
                $table->dateTime('viewing_final_start')->nullable()->after('viewing_midterm_end');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'viewing_final_end')) {
                $table->dateTime('viewing_final_end')->nullable()->after('viewing_final_start');
            }

            // Application / reconciliation
            if (!Schema::hasColumn('tb_mas_sy', 'endOfApplicationPeriod')) {
                $table->dateTime('endOfApplicationPeriod')->nullable()->after('viewing_final_end');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'reconf_start')) {
                $table->dateTime('reconf_start')->nullable()->after('endOfApplicationPeriod');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'reconf_end')) {
                $table->dateTime('reconf_end')->nullable()->after('reconf_start');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'ar_report_date_generation')) {
                $table->dateTime('ar_report_date_generation')->nullable()->after('reconf_end');
            }

            // Installments
            if (!Schema::hasColumn('tb_mas_sy', 'installment1')) {
                $table->dateTime('installment1')->nullable()->after('ar_report_date_generation');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'installment2')) {
                $table->dateTime('installment2')->nullable()->after('installment1');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'installment3')) {
                $table->dateTime('installment3')->nullable()->after('installment2');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'installment4')) {
                $table->dateTime('installment4')->nullable()->after('installment3');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'installment5')) {
                $table->dateTime('installment5')->nullable()->after('installment4');
            }

            // Operational flags
            if (!Schema::hasColumn('tb_mas_sy', 'classType')) {
                $table->string('classType', 16)->nullable()->after('installment5');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'pay_student_visa')) {
                $table->unsignedTinyInteger('pay_student_visa')->nullable()->after('classType');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'is_locked')) {
                $table->unsignedTinyInteger('is_locked')->nullable()->after('pay_student_visa');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'enumGradingPeriod')) {
                $table->string('enumGradingPeriod', 16)->nullable()->after('is_locked');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'enumMGradingPeriod')) {
                $table->string('enumMGradingPeriod', 16)->nullable()->after('enumGradingPeriod');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'enumFGradingPeriod')) {
                $table->string('enumFGradingPeriod', 16)->nullable()->after('enumMGradingPeriod');
            }
        });

        // Sanitize invalid zero datetimes for new date-like columns (defensive)
        $dateCols = [
            'start_of_classes',
            'final_exam_start',
            'final_exam_end',
            'viewing_midterm_start',
            'viewing_midterm_end',
            'viewing_final_start',
            'viewing_final_end',
            'endOfApplicationPeriod',
            'reconf_start',
            'reconf_end',
            'ar_report_date_generation',
            'installment1',
            'installment2',
            'installment3',
            'installment4',
            'installment5',
        ];
        foreach ($dateCols as $col) {
            try {
                DB::table('tb_mas_sy')->where($col, '0000-00-00 00:00:00')->update([$col => null]);
            } catch (\Throwable $e) { /* ignore */ }
        }

        // Composite uniqueness (strYearStart, strYearEnd, enumSem, campus_id)
        // Note: MySQL allows multiple NULLs for campus_id under unique index.
        try {
            Schema::table('tb_mas_sy', function (Blueprint $table) {
                $table->unique(['strYearStart', 'strYearEnd', 'enumSem', 'campus_id'], 'ux_sy_year_sem_campus');
            });
        } catch (\Throwable $e) {
            // ignore if index already exists or engine limitations
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tb_mas_sy')) {
            return;
        }

        // Drop unique index (if exists)
        try {
            Schema::table('tb_mas_sy', function (Blueprint $table) {
                $table->dropUnique('ux_sy_year_sem_campus');
            });
        } catch (\Throwable $e) {
            // ignore if not exists
        }

        // Drop columns, guarded
        $cols = [
            'start_of_classes',
            'final_exam_start',
            'final_exam_end',
            'viewing_midterm_start',
            'viewing_midterm_end',
            'viewing_final_start',
            'viewing_final_end',
            'endOfApplicationPeriod',
            'reconf_start',
            'reconf_end',
            'ar_report_date_generation',
            'installment1',
            'installment2',
            'installment3',
            'installment4',
            'installment5',
            'classType',
            'pay_student_visa',
            'is_locked',
            'enumGradingPeriod',
            'enumMGradingPeriod',
            'enumFGradingPeriod',
        ];

        Schema::table('tb_mas_sy', function (Blueprint $table) use ($cols) {
            foreach ($cols as $col) {
                if (Schema::hasColumn('tb_mas_sy', $col)) {
                    try {
                        $table->dropColumn($col);
                    } catch (\Throwable $e) {
                        // ignore if not droppable in this environment
                    }
                }
            }
        });
    }
};
