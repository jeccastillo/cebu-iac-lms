<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_information_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_information_id');
            $table->unsignedBigInteger('admission_upload_type_id');
            $table->unsignedBigInteger('admission_file_id')->nullable();
            $table->timestamps();

            $table->unique(['student_information_id', 'admission_upload_type_id'], 'sir_unique_student_upload_type');

            // Use short FK names to avoid MySQL 64-char identifier limit
            $table->foreign('student_information_id', 'sir_student_info_fk')
                ->references('id')->on('admission_student_informations')
                ->onDelete('cascade');

            $table->foreign('admission_upload_type_id', 'sir_upload_type_fk')
                ->references('id')->on('admission_upload_types')
                ->onDelete('cascade');

            $table->foreign('admission_file_id', 'sir_file_fk')
                ->references('id')->on('admission_files')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_information_requirements');
    }
};
