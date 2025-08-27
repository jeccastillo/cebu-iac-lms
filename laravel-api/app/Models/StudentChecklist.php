<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentChecklist extends Model
{
    protected $table = 'tb_student_checklists';
    protected $primaryKey = 'intID';
    protected $guarded = [];

    // Timestamps are present in migrations
    public $timestamps = true;

    /**
     * Items belonging to this checklist.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StudentChecklistItem::class, 'intChecklistID', 'intID');
    }
}
