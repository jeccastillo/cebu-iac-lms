<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentChecklistItem extends Model
{
    protected $table = 'tb_student_checklist_items';
    protected $primaryKey = 'intID';
    protected $guarded = [];

    public $timestamps = true;

    /**
     * Parent checklist.
     */
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(StudentChecklist::class, 'intChecklistID', 'intID');
    }

    /**
     * Linked subject.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'intSubjectID', 'intID');
    }
}
