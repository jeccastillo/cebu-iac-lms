<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_sy') && !Schema::hasColumn('tb_mas_sy', 'campus_id')) {
            Schema::table('tb_mas_sy', function (Blueprint $table) {
                $table->unsignedInteger('campus_id')->nullable()->after('intID');
                $table->index('campus_id', 'idx_sy_campus_id');
            });
        }

        if (Schema::hasTable('tb_mas_sy') && Schema::hasTable('tb_mas_campuses') && Schema::hasColumn('tb_mas_sy', 'campus_id')) {
            Schema::table('tb_mas_sy', function (Blueprint $table) {
                try {
                    $table->foreign('campus_id', 'fk_sy_campus_id')
                          ->references('id')
                          ->on('tb_mas_campuses')
                          ->nullOnDelete();
                } catch (\Throwable $e) {
                    // ignore if FK already exists or engine limitations
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_sy')) {
            Schema::table('tb_mas_sy', function (Blueprint $table) {
                try {
                    $table->dropForeign('fk_sy_campus_id');
                } catch (\Throwable $e) {
                    // ignore if not exists
                }
                try {
                    $table->dropIndex('idx_sy_campus_id');
                } catch (\Throwable $e) {
                    // ignore if not exists
                }
            });

            if (Schema::hasColumn('tb_mas_sy', 'campus_id')) {
                Schema::table('tb_mas_sy', function (Blueprint $table) {
                    try {
                        $table->dropColumn('campus_id');
                    } catch (\Throwable $e) {
                        // ignore
                    }
                });
            }
        }
    }
};
