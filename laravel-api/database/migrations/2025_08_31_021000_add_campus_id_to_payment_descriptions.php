<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_descriptions') && !Schema::hasColumn('payment_descriptions', 'campus_id')) {
            Schema::table('payment_descriptions', function (Blueprint $table) {
                $table->unsignedInteger('campus_id')->nullable()->index()->after('amount');
            });

            // Add FK if campuses table exists; ignore errors to avoid blocking migration
            try {
                if (Schema::hasTable('tb_mas_campuses') && Schema::hasColumn('tb_mas_campuses', 'id')) {
                    Schema::table('payment_descriptions', function (Blueprint $table) {
                        $table->foreign('campus_id', 'fk_payment_descriptions_campus_id')
                            ->references('id')
                            ->on('tb_mas_campuses')
                            ->onDelete('set null');
                    });
                }
            } catch (\Throwable $e) {
                // Intentionally ignore FK creation failure (environment differences)
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_descriptions') && Schema::hasColumn('payment_descriptions', 'campus_id')) {
            // Drop FK if exists; ignore errors
            try {
                Schema::table('payment_descriptions', function (Blueprint $table) {
                    $table->dropForeign('fk_payment_descriptions_campus_id');
                });
            } catch (\Throwable $e) {
                // ignore
            }

            Schema::table('payment_descriptions', function (Blueprint $table) {
                $table->dropColumn('campus_id');
            });
        }
    }
};
