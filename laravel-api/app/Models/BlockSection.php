<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockSection extends Model
{
    protected $table = 'tb_mas_block_sections';
    protected $primaryKey = 'intID';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Get the program that owns the block section.
     */
    public function program()
    {
        return $this->belongsTo(Program::class, 'intProgramID', 'intProgramID');
    }
}
