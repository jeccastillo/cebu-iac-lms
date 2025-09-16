<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('tb_mas_faculty_departments')) {
            return;
        }

        // Seed overlap tags for actor (13) and advisor (1188) on department 'soc' with global campus (null)
        try {
            DB::table('tb_mas_faculty_departments')->updateOrInsert(
                ['intFacultyID' => 13, 'department_code' => 'soc', 'campus_id' => null],
                ['intFacultyID' => 13, 'department_code' => 'soc', 'campus_id' => null]
            );
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            DB::table('tb_mas_faculty_departments')->updateOrInsert(
                ['intFacultyID' => 1188, 'department_code' => 'soc', 'campus_id' => null],
                ['intFacultyID' => 1188, 'department_code' => 'soc', 'campus_id' => null]
            );
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('tb_mas_faculty_departments')) {
            return;
        }

        DB::table('tb_mas_faculty_departments')
            ->where('department_code', 'soc')
            ->whereNull('campus_id')
            ->whereIn('intFacultyID', [13, 1188])
            ->delete();
    }
};
