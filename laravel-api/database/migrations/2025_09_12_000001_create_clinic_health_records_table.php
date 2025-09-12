<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the clinic_health_records table.
     *
     * Fields:
     * - person_type: 'student' or 'faculty'
     * - person_student_id: FK reference id to tb_mas_users.intID (nullable)
     * - person_faculty_id: FK reference id to faculty table PK (nullable)
     * - blood_type: ABO/Rh (e.g., O+, AB-)
     * - height_cm / weight_kg: decimal measurements
     * - allergies / medications / immunizations / conditions: JSON arrays of objects
     * - notes: free-form text
     * - campus_id: associated campus (nullable)
     * - last_updated_by: user id of staff making the latest change
     */
    public function up(): void
    {
        Schema::create('clinic_health_records', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->enum('person_type', ['student', 'faculty']);
            $table->unsignedBigInteger('person_student_id')->nullable();
            $table->unsignedBigInteger('person_faculty_id')->nullable();

            $table->string('blood_type', 3)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();

            $table->json('allergies')->nullable();
            $table->json('medications')->nullable();
            $table->json('immunizations')->nullable();
            $table->json('conditions')->nullable();

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('campus_id')->nullable();
            $table->unsignedBigInteger('last_updated_by')->nullable();

            $table->timestamps();

            // Indexes for frequent lookups and filtering
            $table->index(['person_type', 'person_student_id', 'person_faculty_id'], 'idx_clinic_hr_subject');
            $table->index('campus_id', 'idx_clinic_hr_campus');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_health_records');
    }
};
