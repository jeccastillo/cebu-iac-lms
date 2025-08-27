<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected string $table = 'tb_student_checklists';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        Schema::table($this->table, function (Blueprint $table) {
            // Drop checklist-level Year/Sem columns if present.
            if (Schema::hasColumn($this->table, 'intYearLevel')) {
                $table->dropColumn('intYearLevel');
            }
            if (Schema::hasColumn($this->table, 'strSem')) {
                $table->dropColumn('strSem');
            }
            // Any indexes involving these columns will be dropped automatically with the column drops on MySQL.
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
            // Restore columns (without re-adding compound index to avoid long index name issues)
            if (!Schema::hasColumn($this->table, 'intYearLevel')) {
                $table->integer('intYearLevel')->nullable();
            }
            if (!Schema::hasColumn($this->table, 'strSem')) {
                $table->string('strSem', 8)->nullable();
            }
        });
    }
};
