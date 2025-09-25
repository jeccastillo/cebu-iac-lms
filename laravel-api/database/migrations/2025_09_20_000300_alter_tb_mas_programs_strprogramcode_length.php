<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_programs')) {
            try {
                // Increase length to 50; keep column nullable to avoid strict constraint mismatches
                DB::statement("ALTER TABLE tb_mas_programs MODIFY strProgramCode VARCHAR(50) NULL");
            } catch (\Throwable $e) {
                // Fallback syntax for environments that prefer CHANGE
                DB::statement("ALTER TABLE tb_mas_programs CHANGE strProgramCode strProgramCode VARCHAR(50) NULL");
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_programs')) {
            try {
                // Revert to VARCHAR(30) as requested
                DB::statement("ALTER TABLE tb_mas_programs MODIFY strProgramCode VARCHAR(30) NULL");
            } catch (\Throwable $e) {
                // Fallback syntax
                try {
                    DB::statement("ALTER TABLE tb_mas_programs CHANGE strProgramCode strProgramCode VARCHAR(30) NULL");
                } catch (\Throwable $e2) {
                    // Leave as-is if both syntaxes fail
                }
            }
        }
    }
};
