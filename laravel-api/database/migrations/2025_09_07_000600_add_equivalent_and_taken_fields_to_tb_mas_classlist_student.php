<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add equivalent_subject, term_taken, and school_taken to tb_mas_classlist_student.
     *
     * Notes:
     * - equivalent_subject references tb_mas_subjects.intID (soft FK: indexed, nullable).
     * - term_taken and school_taken capture provenance of credited subjects.
     * - This migration does not enforce a hard foreign key to preserve legacy compatibility.
     */
    public function up(): void
    {
        Schema::table('tb_mas_classlist_student', function (Blueprint $table) {
            // Insert after credited_subject_name when possible; position is best-effort.
            if (!Schema::hasColumn('tb_mas_classlist_student', 'equivalent_subject')) {
                // tb_mas_subjects.intID is typically INT; use unsignedInteger for compatibility.
                $table->unsignedInteger('equivalent_subject')->nullable()->after('credited_subject_name');
                $table->index('equivalent_subject', 'idx_cls_equivalent_subject');
            }

            if (!Schema::hasColumn('tb_mas_classlist_student', 'term_taken')) {
                $table->string('term_taken', 100)->nullable()->after('equivalent_subject');
            }

            if (!Schema::hasColumn('tb_mas_classlist_student', 'school_taken')) {
                $table->string('school_taken', 255)->nullable()->after('term_taken');
            }
        });
    }

    /**
     * Drop equivalent_subject, term_taken, and school_taken.
     */
    public function down(): void
    {
        Schema::table('tb_mas_classlist_student', function (Blueprint $table) {
            if (Schema::hasColumn('tb_mas_classlist_student', 'school_taken')) {
                $table->dropColumn('school_taken');
            }

            if (Schema::hasColumn('tb_mas_classlist_student', 'term_taken')) {
                $table->dropColumn('term_taken');
            }

            if (Schema::hasColumn('tb_mas_classlist_student', 'equivalent_subject')) {
                // Drop index if exists (Laravel will ignore if missing on some drivers)
                try {
                    $table->dropIndex('idx_cls_equivalent_subject');
                } catch (\Throwable $e) {
                    // no-op
                }
                $table->dropColumn('equivalent_subject');
            }
        });
    }
};
