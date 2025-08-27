<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradingSystem extends Model
{
    protected $table = 'tb_mas_grading';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    /**
     * Items (grade mappings) for this grading system.
     */
    public function items(): HasMany
    {
        return $this->hasMany(GradingItem::class, 'grading_id', 'id');
    }
}
