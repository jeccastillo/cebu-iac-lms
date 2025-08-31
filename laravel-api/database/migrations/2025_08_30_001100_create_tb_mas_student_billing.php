<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_student_billing')) {
            Schema::create('tb_mas_student_billing', function (Blueprint $table) {
                $table->increments('intID');
                $table->unsignedInteger('intStudentID');
                $table->unsignedInteger('syid');
                $table->string('description', 255);
                $table->decimal('amount', 12, 2); // positive=charge, negative=credit
                $table->dateTime('posted_at')->nullable();
                $table->text('remarks')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamps();

                // Helpful indexes
                $table->index('intStudentID', 'idx_student_billing_student');
                $table->index('syid', 'idx_student_billing_syid');
                $table->index(['intStudentID', 'syid'], 'idx_student_billing_student_syid');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_student_billing');
    }
};
