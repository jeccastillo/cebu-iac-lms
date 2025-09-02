<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_student_applying_and_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_information_id');
            $table->unsignedInteger('ref_id');
            $table->string('ref_type'); // 'student_type' | 'desired_program'
            $table->timestamps();

            $table->index(['student_information_id', 'ref_type'], 'asiap_student_ref_type_idx');

            // Use a short FK name to avoid MySQL 64-char identifier limit
            $table->foreign('student_information_id', 'asiap_student_info_fk')
                ->references('id')->on('admission_student_informations')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_student_applying_and_programs');
    }
};
