<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the clinic_visits table.
     *
     * Each row represents a single clinic encounter/visit tied to a clinic_health_record.
     */
    public function up(): void
    {
        Schema::create('clinic_visits', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('record_id'); // FK to clinic_health_records.id (no hard FK to avoid legacy constraints)
            $table->dateTime('visit_date')->nullable(); // defaults handled at app level if null

            $table->string('reason', 255)->nullable();

            // JSON payloads for triage and coded fields
            // triage: { bp?:string, hr?:int, rr?:int, temp_c?:decimal, spo2?:int, pain?:int }
            $table->json('triage')->nullable();

            $table->text('assessment')->nullable();

            // diagnosis_codes: array of strings (free-form or ICD codes)
            $table->json('diagnosis_codes')->nullable();

            $table->text('treatment')->nullable();

            // medications_dispensed: array of { name, dose?, qty?, instructions? }
            $table->json('medications_dispensed')->nullable();

            $table->text('follow_up')->nullable();

            $table->unsignedBigInteger('campus_id')->nullable();

            $table->integer('attachments_count')->default(0);

            // staff attribution
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('record_id', 'idx_clinic_visits_record');
            $table->index('visit_date', 'idx_clinic_visits_date');
            $table->index('campus_id', 'idx_clinic_visits_campus');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_visits');
    }
};
