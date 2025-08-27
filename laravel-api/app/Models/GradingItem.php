<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradingItem extends Model
{
    protected $table = 'tb_mas_grading_item';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'grading_id',
        'value',
        'remarks',
    ];

    /**
     * Parent grading system.
     */
    public function gradingSystem(): BelongsTo
    {
        return $this->belongsTo(GradingSystem::class, 'grading_id', 'id');
    }
}
