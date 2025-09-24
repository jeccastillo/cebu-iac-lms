<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_users') && Schema::hasColumn('tb_mas_users', 'dteCreated')) {
            // Prefer Schema builder with change() (requires doctrine/dbal). Fallback to raw SQL attempts.
            try {
                Schema::table('tb_mas_users', function (Blueprint $table) {
                    $table->dateTime('dteCreated')->nullable()->change();
                });
            } catch (\Throwable $e) {
                // Fallback: try common SQL type variants
                $attempts = [
                    'ALTER TABLE tb_mas_users MODIFY COLUMN dteCreated DATETIME NULL',
                    'ALTER TABLE tb_mas_users MODIFY COLUMN dteCreated TIMESTAMP NULL',
                    'ALTER TABLE tb_mas_users MODIFY COLUMN dteCreated DATE NULL',
                ];
                $ok = false;
                foreach ($attempts as $sql) {
                    try {
                        DB::statement($sql);
                        $ok = true;
                        break;
                    } catch (\Throwable $e2) {
                        // try next
                    }
                }
                if (!$ok) {
                    throw $e; // rethrow original to surface failure
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_users') && Schema::hasColumn('tb_mas_users', 'dteCreated')) {
            try {
                Schema::table('tb_mas_users', function (Blueprint $table) {
                    // Revert to NOT NULL (assume datetime as most common)
                    $table->dateTime('dteCreated')->nullable(false)->change();
                });
            } catch (\Throwable $e) {
                // Fallback: attempt common NOT NULL forms
                $attempts = [
                    'ALTER TABLE tb_mas_users MODIFY COLUMN dteCreated DATETIME NOT NULL',
                    'ALTER TABLE tb_mas_users MODIFY COLUMN dteCreated TIMESTAMP NOT NULL',
                    'ALTER TABLE tb_mas_users MODIFY COLUMN dteCreated DATE NOT NULL',
                ];
                foreach ($attempts as $sql) {
                    try {
                        DB::statement($sql);
                        break;
                    } catch (\Throwable $e2) {
                        // swallow
                    }
                }
            }
        }
    }
};
