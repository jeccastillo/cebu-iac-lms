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
            // Add nullable per-item Year Level and Semester if not present
            if (!Schema::hasColumn($this->table, 'intYearLevel')) {
                $table->integer('intYearLevel')->nullable()->after('intSubjectID');
                $table->index('intYearLevel');
            }
            if (!Schema::hasColumn($this->table, 'strSem')) {
                $table->string('strSem', 8)->nullable()->after('intYearLevel');
                $table->index('strSem');
            }
            // Helpful composite for filtering per item if needed
            if (Schema::hasColumn($this->table, 'intChecklistID')) {
                // Use a short, explicit index name to avoid MySQL 64-char limit
                $table->index(['intChecklistID', 'intYearLevel', 'strSem'], 'idx_check_items_list_year_sem');
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
            // Drop composite index if it exists (name unknown; safe to attempt column drops only)
            if (Schema::hasColumn($this->table, 'strSem')) {
                // Some drivers auto-create index names; just drop column (drops index)
                $table->dropColumn('strSem');
            }
            if (Schema::hasColumn($this->table, 'intYearLevel')) {
                $table->dropColumn('intYearLevel');
            }
        });
    }
};
