<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the clinic_attachments table.
     *
     * Each row represents a file associated with either a health record or a visit (or both).
     */
    public function up(): void
    {
        Schema::create('clinic_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Association (nullable to allow either record or visit linkage)
            $table->unsignedBigInteger('record_id')->nullable(); // clinic_health_records.id
            $table->unsignedBigInteger('visit_id')->nullable();  // clinic_visits.id

            // File metadata
            $table->string('original_name', 255);
            $table->string('path', 512);
            $table->string('mime', 128);
            $table->unsignedBigInteger('size_bytes');

            // Uploader attribution
            $table->unsignedBigInteger('uploaded_by');

            $table->timestamps();

            // Indexes for lookups
            $table->index('record_id', 'idx_clinic_attach_record');
            $table->index('visit_id', 'idx_clinic_attach_visit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_attachments');
    }
};
