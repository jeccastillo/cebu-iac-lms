<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['campus_name' => 'Main Campus', 'description' => 'Primary campus'],
            ['campus_name' => 'North Campus', 'description' => 'Annex campus'],
        ];

        foreach ($rows as $r) {
            DB::table('tb_mas_campuses')->updateOrInsert(
                ['campus_name' => $r['campus_name']],
                ['description' => $r['description']]
            );
        }
    }
}
