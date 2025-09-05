<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_cashiers')) {
            Schema::table('tb_mas_cashiers', function (Blueprint $table) {
                if (!Schema::hasColumn('tb_mas_cashiers', 'faculty_id')) {
                    $table->unsignedInteger('faculty_id')->nullable()->after('user_id')->index();
                }
            });

            // Create unique index on (campus_id, faculty_id) if not existing
            $this->ensureUniqueIndex();
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_cashiers')) {
            Schema::table('tb_mas_cashiers', function (Blueprint $table) {
                // Drop unique index if exists
                $this->dropUniqueIndexIfExists('tb_mas_cashiers', 'tb_mas_cashiers_campus_id_faculty_id_unique');
                $this->dropUniqueIndexIfExists('tb_mas_cashiers', 'cashiers_campus_faculty_unique'); // fallback name if used
                if (Schema::hasColumn('tb_mas_cashiers', 'faculty_id')) {
                    $table->dropColumn('faculty_id');
                }
            });
        }
    }

    private function ensureUniqueIndex(): void
    {
        $exists = false;
        // Check existing indexes for (campus_id, faculty_id)
        try {
            $connection = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $connection->listTableIndexes('tb_mas_cashiers');
            foreach ($indexes as $index) {
                $cols = $index->getColumns();
                if (count($cols) === 2 && in_array('campus_id', $cols, true) && in_array('faculty_id', $cols, true)) {
                    if ($index->isUnique()) {
                        $exists = true;
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fallback: attempt creating with a conventional name; ignore if duplicate
        }

        if (!$exists) {
            try {
                Schema::table('tb_mas_cashiers', function (Blueprint $table) {
                    $table->unique(['campus_id', 'faculty_id'], 'tb_mas_cashiers_campus_id_faculty_id_unique');
                });
            } catch (\Throwable $e) {
                // Ignore if already exists under different name
            }
        }
    }

    private function dropUniqueIndexIfExists(string $table, string $indexName): void
    {
        try {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        } catch (\Throwable $e) {
            // ignore if not exists
        }
    }
};
