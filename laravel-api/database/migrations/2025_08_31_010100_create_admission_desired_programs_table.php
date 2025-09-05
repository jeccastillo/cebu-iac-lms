<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_desired_programs', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g., BS Computer Science
            $table->string('type');  // e.g., undergraduate, others
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_desired_programs');
    }
};
