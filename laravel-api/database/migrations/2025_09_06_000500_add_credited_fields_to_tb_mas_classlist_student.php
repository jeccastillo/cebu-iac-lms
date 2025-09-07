<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add credited_subject_name and is_credited_subject to tb_mas_classlist_student.
     */
    public function up(): void
    {
        Schema::table('tb_mas_classlist_student', function (Blueprint $table) {
            // Add after common columns when possible; positions are best-effort.
            if (!Schema::hasColumn('tb_mas_classlist_student', 'is_credited_subject')) {
                $table->boolean('is_credited_subject')->default(0)->after('strRemarks');
            }
            if (!Schema::hasColumn('tb_mas_classlist_student', 'credited_subject_name')) {
                $table->string('credited_subject_name', 255)->nullable()->after('is_credited_subject');
            }
        });
    }

    /**
     * Drop credited_subject_name and is_credited_subject columns.
     */
    public function down(): void
    {
        Schema::table('tb_mas_classlist_student', function (Blueprint $table) {
            if (Schema::hasColumn('tb_mas_classlist_student', 'credited_subject_name')) {
                $table->dropColumn('credited_subject_name');
            }
            if (Schema::hasColumn('tb_mas_classlist_student', 'is_credited_subject')) {
                $table->dropColumn('is_credited_subject');
            }
        });
    }
};
