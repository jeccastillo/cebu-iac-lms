<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transcript_requests')) {
            Schema::create('transcript_requests', function (Blueprint $table) {
                $table->bigIncrements('id');

                // Student context
                $table->unsignedInteger('student_id')->index();
                $table->string('student_number', 64)->nullable()->index();

                // transcript|copy
                $table->string('type', 32)->index();

                // Payment Description reference (payment_descriptions.intID) and resolved amount
                $table->unsignedInteger('payment_description_id')->nullable()->index();
                $table->decimal('amount', 12, 2)->nullable();

                // Selected terms (IDs array) and campus context
                $table->json('term_ids');
                $table->unsignedInteger('campus_id')->nullable()->index();

                // Document metadata
                $table->dateTime('date_issued')->nullable();
                $table->string('prepared_by', 128)->nullable();
                $table->string('verified_by', 128)->nullable();
                $table->string('registrar_signatory', 128)->nullable();
                $table->string('signatory', 128)->nullable();
                $table->text('remarks')->nullable();

                // Audit
                $table->unsignedInteger('created_by_faculty_id')->nullable()->index();
                $table->timestamps();

                // Useful composite indexes
                $table->index(['student_id', 'type']);
                $table->index(['student_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transcript_requests')) {
            Schema::dropIfExists('transcript_requests');
        }
    }
};
