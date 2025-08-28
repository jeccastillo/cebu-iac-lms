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
            if (!Schema::hasColumn('tb_mas_sy', 'term_label')) {
                $table->string('term_label', 32)->nullable()->after('strYearEnd');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'term_student_type')) {
                $table->string('term_student_type', 32)->nullable()->after('term_label');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'midterm_start')) {
                $table->dateTime('midterm_start')->nullable()->after('term_student_type');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'midterm_end')) {
                $table->dateTime('midterm_end')->nullable()->after('midterm_start');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'final_start')) {
                $table->dateTime('final_start')->nullable()->after('midterm_end');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'final_end')) {
                $table->dateTime('final_end')->nullable()->after('final_start');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'end_of_submission')) {
                $table->dateTime('end_of_submission')->nullable()->after('final_end');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'intProcessing')) {
                $table->unsignedTinyInteger('intProcessing')->nullable()->after('end_of_submission');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'enumStatus')) {
                $table->string('enumStatus', 16)->nullable()->after('intProcessing');
            }
            if (!Schema::hasColumn('tb_mas_sy', 'enumFinalized')) {
                $table->string('enumFinalized', 8)->nullable()->after('enumStatus');
            }
        });

        // Sanitize invalid zero datetimes if present
        try {
            DB::table('tb_mas_sy')->where('end_of_submission', '0000-00-00 00:00:00')->update(['end_of_submission' => null]);
        } catch (\Throwable $e) { /* ignore */ }
        try {
            DB::table('tb_mas_sy')->where('midterm_start', '0000-00-00 00:00:00')->update(['midterm_start' => null]);
        } catch (\Throwable $e) { /* ignore */ }
        try {
            DB::table('tb_mas_sy')->where('midterm_end', '0000-00-00 00:00:00')->update(['midterm_end' => null]);
        } catch (\Throwable $e) { /* ignore */ }
        try {
            DB::table('tb_mas_sy')->where('final_start', '0000-00-00 00:00:00')->update(['final_start' => null]);
        } catch (\Throwable $e) { /* ignore */ }
        try {
            DB::table('tb_mas_sy')->where('final_end', '0000-00-00 00:00:00')->update(['final_end' => null]);
        } catch (\Throwable $e) { /* ignore */ }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tb_mas_sy')) {
            return;
        }

        Schema::table('tb_mas_sy', function (Blueprint $table) {
            // Drop columns if they exist
            $cols = [
                'term_label',
                'term_student_type',
                'midterm_start',
                'midterm_end',
                'final_start',
                'final_end',
                'end_of_submission',
                'intProcessing',
                'enumStatus',
                'enumFinalized',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('tb_mas_sy', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
