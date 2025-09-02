<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acceptance_letter_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_information_id');
            $table->string('filename');       // stored filename without extension (timestamp)
            $table->string('orig_filename')->nullable(); // original client filename
            $table->string('filetype');       // extension
            $table->timestamps();

            $table->foreign('student_information_id')
                ->references('id')->on('admission_student_informations')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acceptance_letter_attachments');
    }
};
