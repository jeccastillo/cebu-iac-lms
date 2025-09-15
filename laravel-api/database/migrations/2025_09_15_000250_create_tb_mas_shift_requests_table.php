<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_shift_requests')) {
            Schema::create('tb_mas_shift_requests', function (Blueprint $table) {
                $table->bigIncrements('id');

                // Core identifiers
                $table->integer('student_id');               // FK to tb_mas_users.intID (not enforced)
                $table->string('student_number', 50)->nullable();
                $table->integer('term_id');                  // tb_mas_sy.intID (not enforced)

                // Program change
                $table->integer('program_from')->nullable(); // tb_mas_programs.intProgramID (snapshot)
                $table->integer('program_to');               // tb_mas_programs.intProgramID (requested target)

                // Optional reason from student
                $table->text('reason')->nullable();

                // Workflow/status
                $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
                $table->dateTime('requested_at')->nullable();
                $table->dateTime('processed_at')->nullable();
                $table->integer('processed_by_faculty_id')->nullable();

                // Context
                $table->integer('campus_id')->nullable();
                $table->json('meta')->nullable();

                $table->timestamps();

                // Indexes
                $table->unique(['student_id', 'term_id'], 'uq_shift_req_student_term');
                $table->index('status');
                $table->index('term_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_shift_requests');
    }
};
