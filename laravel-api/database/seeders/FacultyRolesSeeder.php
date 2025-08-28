<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacultyRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facultyId = 13;

        $exists = DB::table('tb_mas_faculty_roles')->where('intFacultyID', $facultyId)->exists();

        if (!$exists) {
            DB::table('tb_mas_faculty_roles')->insert([
                'intFacultyID' => $facultyId,
                'intRoleID'    => 1,
            ]);
        } else {
            DB::table('tb_mas_faculty_roles')
                ->where('intFacultyID', $facultyId)
                ->update(['intRoleID' => 1]);
        }
    }
}
