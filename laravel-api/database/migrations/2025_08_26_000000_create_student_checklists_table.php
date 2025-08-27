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
        if (Schema::hasTable($this->table)) {
            return;
        }

        Schema::create($this->table, function (Blueprint $table) {
            // Primary Key
            $table->increments('intID');

            // Foreign references (no FK constraints to match existing schema style)
            $table->integer('intStudentID')->index();     // FK to tb_mas_users.intID
            $table->integer('intCurriculumID')->index();  // FK to tb_mas_curriculum.intID

            // Checklist-level identifiers per confirmation:
            $table->integer('intYearLevel');              // 1,2,3,4 (or up to 10 if needed)
            $table->string('strSem', 8);                  // '1st', '2nd', '3rd'

            // Optional fields
            $table->string('remarks', 255)->nullable();
            $table->integer('created_by')->nullable()->index();

            // Timestamps
            $table->timestamps();

            // Helpful compound index for retrieval
            $table->index(['intStudentID', 'intCurriculumID']);
            $table->index(['intStudentID', 'intYearLevel', 'strSem']);
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
