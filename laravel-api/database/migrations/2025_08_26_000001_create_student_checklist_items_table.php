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
        if (Schema::hasTable($this->table)) {
            return;
        }

        Schema::create($this->table, function (Blueprint $table) {
            // Primary Key
            $table->increments('intID');

            // Foreign references (no explicit FK constraints to match existing style)
            $table->integer('intChecklistID')->index();  // FK -> tb_student_checklists.intID
            $table->integer('intSubjectID')->index();    // FK -> tb_mas_subjects.intID

            // Status fields
            // Allowed values: planned | in-progress | passed | failed | waived
            $table->string('strStatus', 20)->default('planned');

            // Completion date (nullable)
            $table->date('dteCompleted')->nullable();

            // Whether this subject is required for graduation
            $table->boolean('isRequired')->default(true);

            // Timestamps
            $table->timestamps();

            // Indexes to speed up common lookups
            $table->index(['intChecklistID', 'strStatus']);
            $table->index(['intSubjectID', 'strStatus']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
};
