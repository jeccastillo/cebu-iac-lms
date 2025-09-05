<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_mas_tuition_saved', function (Blueprint $table) {
            $table->increments('intID');
            $table->unsignedInteger('intStudentID');
            $table->unsignedInteger('intRegistrationID');
            $table->unsignedInteger('syid');
            $table->json('payload');
            $table->unsignedInteger('saved_by')->nullable();
            $table->timestamps();

            // Enforce one saved snapshot per registration
            $table->unique(['intStudentID', 'intRegistrationID'], 'unique_saved_tuition');

            // Helpful indexes for lookups
            $table->index('syid', 'idx_tuition_saved_syid');
            $table->index('intStudentID', 'idx_tuition_saved_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_tuition_saved');
    }
};
