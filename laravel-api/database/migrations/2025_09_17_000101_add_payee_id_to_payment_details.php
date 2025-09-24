<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('payment_details')) {
            return;
        }

        // Add nullable payee_id column (indexed) if missing
        if (!Schema::hasColumn('payment_details', 'payee_id')) {
            Schema::table('payment_details', function (Blueprint $table) {
                // Place next to student_information_id for clarity
                $table->unsignedInteger('payee_id')->nullable()->after('student_information_id')->index();
            });
        }

        // Best-effort foreign key (skip if legacy engine or table missing)
        try {
            if (Schema::hasTable('tb_mas_payee') && Schema::hasColumn('payment_details', 'payee_id')) {
                Schema::table('payment_details', function (Blueprint $table) {
                    // Use conventional FK name or let Laravel generate one
                    // Use nullOnDelete() to set payee_id null if payee is removed
                    $table->foreign('payee_id')
                        ->references('id')
                        ->on('tb_mas_payee')
                        ->nullOnDelete();
                });
            }
        } catch (\Throwable $e) {
            // Ignore FK errors in legacy environments (MyISAM, missing privileges, etc.)
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('payment_details')) {
            return;
        }

        // Drop FK then column if present
        try {
            Schema::table('payment_details', function (Blueprint $table) {
                // Attempt to drop FK by column; ignore if not present
                try { $table->dropForeign(['payee_id']); } catch (\Throwable $e) {}
                if (Schema::hasColumn('payment_details', 'payee_id')) {
                    $table->dropColumn('payee_id');
                }
            });
        } catch (\Throwable $e) {
            // Best effort
        }
    }
};
