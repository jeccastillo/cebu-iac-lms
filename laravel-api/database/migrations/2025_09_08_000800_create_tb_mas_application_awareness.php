<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Create tb_mas_application_awareness table to capture multi-select
     * "How did you find out about iACADEMY?" responses per application.
     *
     * Columns:
     * - id (bigIncrements)
     * - applicant_data_id (unsigned int) -> links to tb_mas_applicant_data.id
     * - name (varchar 100) e.g. Google, Facebook, Instagram, Tiktok, News,
     *   School Fair/Orientation, Billboard, Event, Referral, Others
     * - sub_name (varchar 255 nullable) for event name or others specify
     * - referral (tinyint(1) default 0)
     * - name_of_referee (varchar 255 nullable)
     * - timestamps
     *
     * Indexes:
     * - idx_app_awareness_applicant_data_id (applicant_data_id)
     *
     * Foreign key (best-effort; ignore on legacy envs where FK may fail):
     * - fk_app_awareness_applicant_data_id → tb_mas_applicant_data(id) ON DELETE CASCADE
     */
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_application_awareness')) {
            Schema::create('tb_mas_application_awareness', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('applicant_data_id');
                $table->string('name', 100);
                $table->string('sub_name', 255)->nullable();
                $table->boolean('referral')->default(false);
                $table->string('name_of_referee', 255)->nullable();
                $table->timestamps();

                $table->index('applicant_data_id', 'idx_app_awareness_applicant_data_id');
            });

            // Best-effort foreign key add (guard against legacy environments)
            try {
                Schema::table('tb_mas_application_awareness', function (Blueprint $table) {
                    // Only add the FK if referenced table exists and column is present
                    if (Schema::hasTable('tb_mas_applicant_data')) {
                        $table->foreign('applicant_data_id', 'fk_app_awareness_applicant_data_id')
                              ->references('id')->on('tb_mas_applicant_data')
                              ->onDelete('cascade');
                    }
                });
            } catch (\Throwable $e) {
                // Log and continue without halting migrations
                Log::warning('FK add failed for tb_mas_application_awareness.applicant_data_id: ' . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        // Drop table safely
        if (Schema::hasTable('tb_mas_application_awareness')) {
            // Try to drop FK first if present
            try {
                Schema::table('tb_mas_application_awareness', function (Blueprint $table) {
                    try {
                        $table->dropForeign('fk_app_awareness_applicant_data_id');
                    } catch (\Throwable $e) {
                        // Ignore if not present
                    }
                });
            } catch (\Throwable $e) {
                // Ignore FK drop errors
            }

            Schema::dropIfExists('tb_mas_application_awareness');
        }
    }
};
