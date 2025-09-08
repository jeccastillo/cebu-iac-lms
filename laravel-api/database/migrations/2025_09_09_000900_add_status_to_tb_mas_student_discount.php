<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected string $table = 'tb_mas_student_discount';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable($this->table)) {
            // Table is expected to exist based on current codebase usage.
            return;
        }

        // Add status column (default 'pending') if missing.
        if (!Schema::hasColumn($this->table, 'status')) {
            Schema::table($this->table, function (Blueprint $table) {
                // Keep column size modest; statuses: 'pending' | 'applied'
                $table->string('status', 20)->default('pending')->index('idx_student_discount_status');
            });
        }

        // Add helpful composite index (student_id, syid, discount_id) to prevent duplicate assignments and speed lookups.
        // Guard by attempting to create; if it already exists, catch and ignore.
        try {
            Schema::table($this->table, function (Blueprint $table) {
                // Index name kept explicit for safe down() handling
                $table->index(['student_id', 'syid', 'discount_id'], 'idx_student_discount_student_syid_discount');
            });
        } catch (\Throwable $e) {
            // ignore if index already exists or DB engine rejects duplicate creation
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        // Drop composite index if present
        try {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropIndex('idx_student_discount_student_syid_discount');
            });
        } catch (\Throwable $e) {
            // ignore
        }

        // Drop status single-column index and column if present
        if (Schema::hasColumn($this->table, 'status')) {
            try {
                Schema::table($this->table, function (Blueprint $table) {
                    // Drop index if present (named above)
                    try {
                        $table->dropIndex('idx_student_discount_status');
                    } catch (\Throwable $e) {
                        // ignore
                    }
                });
            } catch (\Throwable $e) {
                // ignore
            }

            try {
                Schema::table($this->table, function (Blueprint $table) {
                    $table->dropColumn('status');
                });
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }
};
