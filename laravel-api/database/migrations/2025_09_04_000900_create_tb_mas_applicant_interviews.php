<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create tb_mas_applicant_interviews:
     * - One interview per applicant_data_id (unique)
     * - Guarded foreign keys to avoid legacy FK failures on some environments
     */
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_applicant_interviews')) {
            Schema::create('tb_mas_applicant_interviews', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('applicant_data_id'); // links to tb_mas_applicant_data.id
                $table->dateTime('scheduled_at');             // scheduled interview datetime
                $table->unsignedInteger('interviewer_user_id')->nullable(); // tb_mas_users.intID (optional)

                $table->text('remarks')->nullable();
                $table->enum('assessment', ['Passed', 'Failed'])->nullable();
                $table->string('reason_for_failing', 255)->nullable();
                $table->dateTime('completed_at')->nullable(); // set upon result submission

                $table->timestamps();

                // Uniqueness: exactly one interview per applicant_data_id
                $table->unique('applicant_data_id', 'uq_applicant_interview_applicant_data_id');

                // Index to support lookups by interviewer
                $table->index('interviewer_user_id', 'idx_applicant_interview_interviewer_user_id');
            });

            // Guarded FK: applicant_data_id -> tb_mas_applicant_data(id)
            try {
                Schema::table('tb_mas_applicant_interviews', function (Blueprint $table) {
                    if (Schema::hasTable('tb_mas_applicant_data')) {
                        $table->foreign('applicant_data_id', 'fk_applicant_interviews_applicant_data')
                            ->references('id')
                            ->on('tb_mas_applicant_data')
                            ->onUpdate('cascade')
                            ->onDelete('cascade');
                    }
                });
            } catch (\Throwable $e) {
                // ignore FK creation failure (legacy environments)
            }

            // Guarded FK: interviewer_user_id -> tb_mas_users(intID)
            try {
                Schema::table('tb_mas_applicant_interviews', function (Blueprint $table) {
                    if (Schema::hasTable('tb_mas_users')) {
                        $table->foreign('interviewer_user_id', 'fk_applicant_interviews_interviewer')
                            ->references('intID')
                            ->on('tb_mas_users')
                            ->onUpdate('cascade')
                            ->onDelete('set null');
                    }
                });
            } catch (\Throwable $e) {
                // ignore FK creation failure (legacy environments)
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_applicant_interviews')) {
            // Drop FKs and indexes defensively
            try {
                Schema::table('tb_mas_applicant_interviews', function (Blueprint $table) {
                    $table->dropForeign('fk_applicant_interviews_applicant_data');
                });
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('tb_mas_applicant_interviews', function (Blueprint $table) {
                    $table->dropForeign('fk_applicant_interviews_interviewer');
                });
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('tb_mas_applicant_interviews', function (Blueprint $table) {
                    $table->dropUnique('uq_applicant_interview_applicant_data_id');
                });
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('tb_mas_applicant_interviews', function (Blueprint $table) {
                    $table->dropIndex('idx_applicant_interview_interviewer_user_id');
                });
            } catch (\Throwable $e) {
            }

            Schema::dropIfExists('tb_mas_applicant_interviews');
        }
    }
};
