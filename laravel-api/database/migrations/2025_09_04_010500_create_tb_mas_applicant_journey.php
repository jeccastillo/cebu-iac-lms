<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create tb_mas_applicant_journey table:
     * - id (bigIncrements)
     * - applicant_data_id (unsigned int) -> links to tb_mas_applicant_data.id
     * - remarks (text)
     * - log_date (datetime) - no timezone columns; no created_at/updated_at
     * - indexes on (applicant_data_id) and (log_date)
     * - guarded FK (best-effort; ignore failures in some legacy environments)
     */
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_applicant_journey')) {
            Schema::create('tb_mas_applicant_journey', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('applicant_data_id');
                $table->text('remarks');
                $table->dateTime('log_date');

                $table->index('applicant_data_id', 'idx_applicant_journey_applicant_data_id');
                $table->index('log_date', 'idx_applicant_journey_log_date');
            });

            // Guarded foreign key: ignore if environment doesn't allow adding FKs
            try {
                if (Schema::hasTable('tb_mas_applicant_data')) {
                    Schema::table('tb_mas_applicant_journey', function (Blueprint $table) {
                        $table->foreign('applicant_data_id', 'fk_applicant_journey_applicant_data')
                            ->references('id')
                            ->on('tb_mas_applicant_data')
                            ->onUpdate('cascade')
                            ->onDelete('cascade');
                    });
                }
            } catch (\Throwable $e) {
                // ignore FK setup failures
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_applicant_journey')) {
            // Drop FK if present (guarded)
            try {
                Schema::table('tb_mas_applicant_journey', function (Blueprint $table) {
                    $table->dropForeign('fk_applicant_journey_applicant_data');
                });
            } catch (\Throwable $e) {
                // ignore
            }

            Schema::drop('tb_mas_applicant_journey');
        }
    }
};
