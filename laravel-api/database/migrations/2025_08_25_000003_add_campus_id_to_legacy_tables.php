<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'tb_mas_users' => 'users',
        'tb_mas_faculty' => 'faculty',
        'tb_mas_programs' => 'programs',
        'tb_mas_curriculum' => 'curriculum',
        'tb_mas_classrooms' => 'classrooms',
        'tb_mas_classlist' => 'classlist',
        'tb_mas_subjects' => 'subjects',
    ];

    public function up(): void
    {
        // Add nullable campus_id and index
        foreach ($this->tables as $tableName => $short) {
            if (Schema::hasTable($tableName)) {
                if (!Schema::hasColumn($tableName, 'campus_id')) {
                    Schema::table($tableName, function (Blueprint $table) use ($tableName, $short) {
                        $table->unsignedInteger('campus_id')->nullable();
                        $table->index('campus_id', "idx_{$short}_campus_id");
                    });
                }
            }
        }

        // Add foreign keys referencing tb_mas_campuses(id) with SET NULL on delete
        if (Schema::hasTable('tb_mas_campuses')) {
            foreach ($this->tables as $tableName => $short) {
                if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'campus_id')) {
                    Schema::table($tableName, function (Blueprint $table) use ($short) {
                        // Wrap FK creation in try/catch to avoid duplicate FK name errors on re-run
                        try {
                            $table->foreign('campus_id', "fk_{$short}_campus_id")
                                  ->references('id')
                                  ->on('tb_mas_campuses')
                                  ->nullOnDelete();
                        } catch (\Throwable $e) {
                            // ignore if already exists or engine limitations
                        }
                    });
                }
            }
        }
    }

    public function down(): void
    {
        // Drop FKs then columns
        foreach ($this->tables as $tableName => $short) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($short, $tableName) {
                    try {
                        $table->dropForeign("fk_{$short}_campus_id");
                    } catch (\Throwable $e) {
                        // ignore if does not exist
                    }
                    try {
                        $table->dropIndex("idx_{$short}_campus_id");
                    } catch (\Throwable $e) {
                        // ignore if does not exist
                    }
                    if (Schema::hasColumn($tableName, 'campus_id')) {
                        try {
                            $table->dropColumn('campus_id');
                        } catch (\Throwable $e) {
                            // ignore
                        }
                    }
                });
            }
        }
    }
};
