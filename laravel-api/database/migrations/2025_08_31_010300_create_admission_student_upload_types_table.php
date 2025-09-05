<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_student_upload_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_student_type_id');
            $table->unsignedBigInteger('admission_upload_type_id');
            $table->timestamps();

            $table->unique(['admission_student_type_id', 'admission_upload_type_id'], 'admission_student_upload_types_unique');

            $table->foreign('admission_student_type_id')
                ->references('id')->on('admission_student_types')
                ->onDelete('cascade');

            $table->foreign('admission_upload_type_id')
                ->references('id')->on('admission_upload_types')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_student_upload_types');
    }
};
