<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_shifting')) {
            Schema::create('tb_mas_shifting', function (Blueprint $table) {
                $table->increments('intID');
                $table->integer('student_id')->index();     // FK to tb_mas_users.intID (not enforced)
                $table->integer('term_id')->nullable()->index(); // SYID (tb_mas_sy.intID) when provided
                $table->integer('program_from')->nullable();
                $table->integer('program_to')->nullable();
                $table->integer('curriculum_from')->nullable();
                $table->integer('curriculum_to')->nullable();
                $table->timestamp('date_shifted')->useCurrent();

                // Optional helper indexes
                $table->index(['student_id', 'term_id'], 'idx_shifting_student_term');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_shifting')) {
            Schema::drop('tb_mas_shifting');
        }
    }
};
