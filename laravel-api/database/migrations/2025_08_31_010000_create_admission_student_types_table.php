<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_student_types', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g., UG - Freshman
            $table->string('type');  // e.g., ug_freshman
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_student_types');
    }
};
