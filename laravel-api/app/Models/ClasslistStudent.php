<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClasslistStudent extends Model
{
    protected $table = 'tb_mas_classlist_student';
    protected $primaryKey = 'intCSID';
    public $timestamps = false;

    protected $guarded = [];
    protected $casts = [
        'is_credited_subject' => 'boolean',
    ];

    public function classlist(): BelongsTo
    {
        return $this->belongsTo(Classlist::class, 'intClassListID', 'intID');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'intStudentID', 'intID');
    }
}
