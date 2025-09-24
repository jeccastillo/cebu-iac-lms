<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_users') && Schema::hasColumn('tb_mas_users', 'slug')) {
            // First try using Schema change() API. If Doctrine DBAL is not installed, fall back to raw SQL.
            try {
                Schema::table('tb_mas_users', function (Blueprint $table) {
                    $table->string('slug', 255)->nullable()->change();
                });
            } catch (\Throwable $e) {
                // Fallback for environments without doctrine/dbal
                try {
                    DB::statement('ALTER TABLE tb_mas_users MODIFY COLUMN slug VARCHAR(255) NULL');
                } catch (\Throwable $e2) {
                    // Surface a helpful error to aid debugging
                    throw $e2;
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_users') && Schema::hasColumn('tb_mas_users', 'slug')) {
            try {
                Schema::table('tb_mas_users', function (Blueprint $table) {
                    // Revert to NOT NULL (length assumed 255 as typical)
                    $table->string('slug', 255)->nullable(false)->change();
                });
            } catch (\Throwable $e) {
                try {
                    DB::statement('ALTER TABLE tb_mas_users MODIFY COLUMN slug VARCHAR(255) NOT NULL');
                } catch (\Throwable $e2) {
                    // Swallow silently in down to avoid breaking rollback in unknown schemas
                }
            }
        }
    }
};
