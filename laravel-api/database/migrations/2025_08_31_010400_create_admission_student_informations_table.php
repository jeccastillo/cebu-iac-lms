<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_student_informations', function (Blueprint $table) {
            $table->id();
            // Personal info
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('email');
            $table->string('school')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('tel_number')->nullable();

            // FK references
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('program_id')->nullable();

            // Status/remarks
            $table->string('status')->nullable();
            $table->text('interview_remarks')->nullable();

            // Acceptance letter fields
            $table->longText('acceptance_letter')->nullable();
            $table->date('acceptance_letter_sent_date')->nullable();

            // Slug for public access
            $table->uuid('slug')->unique();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('type_id')
                ->references('id')->on('admission_student_types')
                ->onDelete('set null');

            $table->foreign('program_id')
                ->references('id')->on('admission_desired_programs')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_student_informations');
    }
};
