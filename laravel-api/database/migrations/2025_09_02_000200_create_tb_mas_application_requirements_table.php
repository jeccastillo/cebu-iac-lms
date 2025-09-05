<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_application_requirements')) {
            Schema::create('tb_mas_application_requirements', function (Blueprint $table) {
                $table->increments('intID');
                // Link to legacy users table tb_mas_users.intID (index only, no hard FK per project convention)
                $table->unsignedInteger('intStudentID');
                // Link to master requirements tb_mas_requirements.intID (index only)
                $table->unsignedInteger('tb_mas_requirements_id');
                $table->boolean('submitted_status')->default(false);
                $table->timestamps();

                // Indexes
                $table->index('intStudentID', 'idx_appreq_student');
                $table->index('tb_mas_requirements_id', 'idx_appreq_requirement');
                $table->unique(['intStudentID', 'tb_mas_requirements_id'], 'uq_appreq_student_requirement');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_application_requirements');
    }
};
