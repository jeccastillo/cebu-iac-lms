<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking curriculum-related tables...\n";

try {
    // Check if tb_mas_curriculum table exists
    $curriculumExists = DB::select("SHOW TABLES LIKE 'tb_mas_curriculum'");
    echo "tb_mas_curriculum table exists: " . (count($curriculumExists) > 0 ? "YES" : "NO") . "\n";
    
    if (count($curriculumExists) > 0) {
        $curriculumCount = DB::table('tb_mas_curriculum')->count();
        echo "tb_mas_curriculum records: $curriculumCount\n";
        
        if ($curriculumCount > 0) {
            $sample = DB::table('tb_mas_curriculum')->first();
            echo "Sample curriculum: " . json_encode($sample) . "\n";
        }
    }
    
    // Check if tb_mas_curriculum_subject table exists
    $curriculumSubjectExists = DB::select("SHOW TABLES LIKE 'tb_mas_curriculum_subject'");
    echo "tb_mas_curriculum_subject table exists: " . (count($curriculumSubjectExists) > 0 ? "YES" : "NO") . "\n";
    
    if (count($curriculumSubjectExists) > 0) {
        $curriculumSubjectCount = DB::table('tb_mas_curriculum_subject')->count();
        echo "tb_mas_curriculum_subject records: $curriculumSubjectCount\n";
    }
    
    // If tables don't exist, let's create some test data
    if (count($curriculumExists) == 0) {
        echo "\nCreating tb_mas_curriculum table...\n";
        DB::statement("
            CREATE TABLE tb_mas_curriculum (
                intID int(11) NOT NULL AUTO_INCREMENT,
                strCurriculum varchar(255) NOT NULL,
                intProgramID int(11) NOT NULL DEFAULT 0,
                intYearLevel int(11) NOT NULL DEFAULT 1,
                intSem int(11) NOT NULL DEFAULT 1,
                isActive tinyint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (intID)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "tb_mas_curriculum table created.\n";
        
        // Insert some test data
        DB::table('tb_mas_curriculum')->insert([
            [
                'strCurriculum' => 'Computer Science 2023',
                'intProgramID' => 1,
                'intYearLevel' => 1,
                'intSem' => 1,
                'isActive' => 1
            ],
            [
                'strCurriculum' => 'Information Technology 2023',
                'intProgramID' => 2,
                'intYearLevel' => 1,
                'intSem' => 1,
                'isActive' => 1
            ]
        ]);
        echo "Test curriculum data inserted.\n";
    }
    
    if (count($curriculumSubjectExists) == 0) {
        echo "\nCreating tb_mas_curriculum_subject table...\n";
        DB::statement("
            CREATE TABLE tb_mas_curriculum_subject (
                intID int(11) NOT NULL AUTO_INCREMENT,
                intCurriculumID int(11) NOT NULL,
                intSubjectID int(11) NOT NULL,
                intYearLevel int(11) NOT NULL DEFAULT 1,
                intSem int(11) NOT NULL DEFAULT 1,
                PRIMARY KEY (intID),
                KEY idx_curriculum (intCurriculumID),
                KEY idx_subject (intSubjectID)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "tb_mas_curriculum_subject table created.\n";
    }
    
    echo "\nDone!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
