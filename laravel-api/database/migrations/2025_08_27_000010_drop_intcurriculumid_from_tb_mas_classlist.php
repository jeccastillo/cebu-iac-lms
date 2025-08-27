<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_classlist') && Schema::hasColumn('tb_mas_classlist', 'intCurriculumID')) {
            // Best-effort: drop FK by column name if it exists
            try {
                Schema::table('tb_mas_classlist', function (Blueprint $table) {
                    $table->dropForeign(['intCurriculumID']);
                });
            } catch (\Throwable $e) {
                // ignore
            }

            // Best-effort: drop common FK names if present
            try {
                Schema::table('tb_mas_classlist', function (Blueprint $table) {
                    $table->dropForeign('fk_classlist_intcurriculumid');
                });
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                Schema::table('tb_mas_classlist', function (Blueprint $table) {
                    $table->dropForeign('fk_classlist_curriculum_id');
                });
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                Schema::table('tb_mas_classlist', function (Blueprint $table) {
                    $table->dropForeign('tb_mas_classlist_intCurriculumID_foreign');
                });
            } catch (\Throwable $e) {
                // ignore
            }

            // Best-effort: drop indexes on the column
            try {
                Schema::table('tb_mas_classlist', function (Blueprint $table) {
                    $table->dropIndex(['intCurriculumID']);
                });
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                Schema::table('tb_mas_classlist', function (Blueprint $table) {
                    $table->dropIndex('idx_classlist_intcurriculumid');
                });
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                Schema::table('tb_mas_classlist', function (Blueprint $table) {
                    $table->dropIndex('tb_mas_classlist_intCurriculumID_index');
                });
            } catch (\Throwable $e) {
                // ignore
            }

            // Finally, drop the column
            Schema::table('tb_mas_classlist', function (Blueprint $table) {
                $table->dropColumn('intCurriculumID');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_classlist') && !Schema::hasColumn('tb_mas_classlist', 'intCurriculumID')) {
            Schema::table('tb_mas_classlist', function (Blueprint $table) {
                // Restore as nullable and indexed to avoid blocking inserts on rollback
                $table->integer('intCurriculumID')->nullable()->after('intSubjectID');
                $table->index('intCurriculumID', 'idx_classlist_intcurriculumid');
            });
        }
    }
};
