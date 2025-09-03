<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_applicant_types')) {
            Schema::create('tb_mas_applicant_types', function (Blueprint $table) {
                // Legacy-style primary key naming used across the project
                $table->increments('intID');
                $table->string('name', 255);
                $table->enum('type', ['college', 'shs', 'grad']);
                $table->timestamps();

                // Uniqueness constraint: name + type
                $table->unique(['name', 'type'], 'uq_tb_mas_applicant_types_name_type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_applicant_types');
    }
};
