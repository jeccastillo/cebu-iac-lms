<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_faculty_departments')) {
            Schema::create('tb_mas_faculty_departments', function (Blueprint $table) {
                $table->increments('intID');
                $table->integer('intFacultyID')->index(); // references faculty primary key (no FK to avoid cross-env failures)
                $table->string('department_code', 64)->index();
                $table->integer('campus_id')->nullable()->index();
                $table->timestamps();

                $table->unique(['intFacultyID', 'department_code', 'campus_id'], 'uniq_faculty_dept_campus');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_faculty_departments');
    }
};
