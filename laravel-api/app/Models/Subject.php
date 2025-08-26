<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'tb_mas_subjects';
    protected $primaryKey = 'intID';
    public $timestamps = false;

    protected $fillable = [
        'strCode',
        'strDescription',
        'strUnits',
        'strTuitionUnits',
        'strLabClassification',
        'intLab',
        'strDepartment',
        'intLectHours',
        'intPrerequisiteID',
        'intEquivalentID1',
        'intEquivalentID2',
        'intProgramID',
        'isNSTP',
        'isThesisSubject',
        'isInternshipSubject',
        'include_gwa',
        'grading_system_id',
        'grading_system_id_midterm',
        'isElective',
        'isSelectableElective',
        'strand',
        'intBridging',
        'intMajor',
    ];
}
