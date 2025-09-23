<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_student_advisor')) {
            Schema::create('tb_mas_student_advisor', function (Blueprint $table) {
                $table->increments('intID');

                // References tb_mas_users.intID (no FK to avoid cross-env failures)
                $table->integer('intStudentID')->index();

                // References tb_mas_faculty.intID (no FK to avoid cross-env failures)
                $table->integer('intAdvisorID')->index();

                // Active flag (only one active row per student via unique index)
                $table->tinyInteger('is_active')->default(1)->index();

                // Temporal fields
                $table->dateTime('started_at')->useCurrent();
                $table->dateTime('ended_at')->nullable();

                // Audit and scoping
                $table->integer('assigned_by')->nullable()->index(); // acting faculty admin ID
                $table->string('department_code', 64)->nullable()->index(); // canonical lowercase department code
                $table->integer('campus_id')->nullable()->index();

                // Timestamps
                $table->timestamps();

                // Invariants and performance
                $table->unique(['intStudentID', 'is_active'], 'uniq_active_advisor_per_student');
                $table->index(['intAdvisorID', 'is_active'], 'idx_advisor_active');
                $table->index('intStudentID', 'idx_studentid');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_student_advisor');
    }
};
