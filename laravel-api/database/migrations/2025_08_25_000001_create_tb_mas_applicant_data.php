<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_applicant_data')) {
            Schema::create('tb_mas_applicant_data', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                // Prefer JSON if supported, otherwise fallback to longText
                if (DB::getDriverName() === 'mysql') {
                    // Check MySQL version if needed; for simplicity assume JSON is supported on modern MySQL
                    $table->json('data')->nullable();
                } else {
                    $table->longText('data')->nullable();
                }
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->index('user_id', 'idx_applicant_user_id');
            });

            // Add FK separately to avoid issues on some MySQL versions
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                $table->foreign('user_id', 'fk_applicant_user_id')
                    ->references('intID')
                    ->on('tb_mas_users')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_applicant_data')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                try {
                    $table->dropForeign('fk_applicant_user_id');
                } catch (\Throwable $e) {
                    // ignore
                }
            });
            Schema::dropIfExists('tb_mas_applicant_data');
        }
    }
};
