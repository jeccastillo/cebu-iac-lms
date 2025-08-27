<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected string $table = 'tb_student_checklist_items';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        Schema::table($this->table, function (Blueprint $table) {
            // 1) Add intSem if not present
            if (!Schema::hasColumn($this->table, 'intSem')) {
                $table->integer('intSem')->nullable()->after('intYearLevel');
                $table->index('intSem');
            }

            // 2) Adjust composite index to use intSem instead of strSem
            // Drop previous composite index if it exists (we used this short name earlier)
            try {
                $table->dropIndex('idx_check_items_list_year_sem');
            } catch (\Throwable $e) {
                // ignore if it doesn't exist
            }

            // Recreate composite index with intSem
            $table->index(['intChecklistID', 'intYearLevel', 'intSem'], 'idx_check_items_list_year_sem');

            // 3) Drop old strSem column if present (and its standalone index will fall with column)
            if (Schema::hasColumn($this->table, 'strSem')) {
                $table->dropColumn('strSem');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        Schema::table($this->table, function (Blueprint $table) {
            // Drop composite index using intSem
            try {
                $table->dropIndex('idx_check_items_list_year_sem');
            } catch (\Throwable $e) {
                // ignore
            }

            // Recreate strSem (string) and its index
            if (!Schema::hasColumn($this->table, 'strSem')) {
                $table->string('strSem', 8)->nullable()->after('intYearLevel');
                $table->index('strSem');
            }

            // Recreate composite index with strSem
            $table->index(['intChecklistID', 'intYearLevel', 'strSem'], 'idx_check_items_list_year_sem');

            // Drop intSem column
            if (Schema::hasColumn($this->table, 'intSem')) {
                $table->dropColumn('intSem');
            }
        });
    }
};
