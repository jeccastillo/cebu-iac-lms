<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates: tb_mas_corequisites
     * Columns:
     *  - intID (PK, AI)
     *  - intSubjectID (int, not null)
     *  - intCorequisiteID (int, not null)
     *  - program (varchar(50), nullable)
     * Indexes:
     *  - index on intSubjectID
     *  - index on intCorequisiteID
     *  - unique (intSubjectID, intCorequisiteID, program)
     */
    public function up(): void
    {
        Schema::create('tb_mas_corequisites', function (Blueprint $table) {
            $table->increments('intID');
            $table->unsignedInteger('intSubjectID');
            $table->unsignedInteger('intCorequisiteID');
            $table->string('program', 50)->nullable();

            $table->index('intSubjectID', 'idx_coreq_subject');
            $table->index('intCorequisiteID', 'idx_coreq_corequisite');

            // Note: In MySQL, UNIQUE with nullable column allows multiple NULLs, which is acceptable here.
            $table->unique(['intSubjectID', 'intCorequisiteID', 'program'], 'uq_subject_coreq_program');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_mas_corequisites');
    }
};
