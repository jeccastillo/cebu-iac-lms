<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Seed baseline roles into tb_mas_roles.
     */
    public function run(): void
    {
        $now = date('Y-m-d H:i:s'); // kept for potential logging; table has no timestamps

        $roles = [
            ['strCode' => 'admin',          'strName' => 'Administrator',            'strDescription' => 'Full administrative access',                                         'intActive' => 1],
            ['strCode' => 'registrar',      'strName' => 'Registrar',                'strDescription' => 'Registrar operations',                                               'intActive' => 1],
            ['strCode' => 'finance',        'strName' => 'Finance',                  'strDescription' => 'Finance operations',                                                 'intActive' => 1],
            ['strCode' => 'faculty',        'strName' => 'Faculty',                  'strDescription' => 'Faculty member',                                                     'intActive' => 1],
            ['strCode' => 'student',        'strName' => 'Student',                  'strDescription' => 'Student role',                                                       'intActive' => 1],
            ['strCode' => 'scholarship',    'strName' => 'Scholarship',              'strDescription' => 'Scholarship management',                                             'intActive' => 1],
            ['strCode' => 'unity',          'strName' => 'Unity Operations',         'strDescription' => 'Unity advising/enlistment',                                          'intActive' => 1],
            // Additional roles referenced by routes/middleware
            ['strCode' => 'cashier_admin',  'strName' => 'Cashier Administration',   'strDescription' => 'Manage cashiers, OR/invoice ranges, and payments',                   'intActive' => 1],
            ['strCode' => 'faculty_admin',  'strName' => 'Faculty Administration',   'strDescription' => 'Manage faculty and grading systems',                                  'intActive' => 1],
            ['strCode' => 'building_admin', 'strName' => 'Building Administration',  'strDescription' => 'Manage classrooms and building resources',                            'intActive' => 1],
        ];

        foreach ($roles as $role) {
            $exists = DB::table('tb_mas_roles')->where('strCode', $role['strCode'])->exists();
            if (!$exists) {
                DB::table('tb_mas_roles')->insert($role);
            } else {
                // Ensure it's active and names stay updated for idempotency
                DB::table('tb_mas_roles')
                    ->where('strCode', $role['strCode'])
                    ->update([
                        'strName'        => $role['strName'],
                        'strDescription' => $role['strDescription'],
                        'intActive'      => 1,
                    ]);
            }
        }
    }
}
