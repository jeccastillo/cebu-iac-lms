<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExcessPaymentApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('excess_payment_applications')) {
            Schema::create('excess_payment_applications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedInteger('source_term_id');
                $table->unsignedInteger('target_term_id');
                $table->decimal('amount', 12, 2);
                $table->enum('status', ['applied', 'reverted'])->default('applied');
                $table->timestamps();

                $table->index('student_id');
                $table->index('source_term_id');
                $table->index('target_term_id');

                $table->foreign('student_id')->references('intID')->on('tb_mas_users')->onDelete('cascade');
                $table->foreign('source_term_id')->references('intID')->on('tb_mas_sy')->onDelete('cascade');
                $table->foreign('target_term_id')->references('intID')->on('tb_mas_sy')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excess_payment_applications');
    }
}
