<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('previous_schools')) {
            Schema::create('previous_schools', function (Blueprint $table) {
                // Legacy-style primary key naming used across the project
                $table->increments('intID');
                $table->string('name', 255);
                $table->string('city', 128)->nullable();
                $table->string('province', 128)->nullable();
                $table->string('country', 128)->nullable();
                // grade is numeric (integer), may be null
                $table->integer('grade')->nullable();
                $table->timestamps();

                // Uniqueness constraint: name + city
                $table->unique(['name', 'city'], 'uq_previous_schools_name_city');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('previous_schools');
    }
};
