<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Legacy table mapping
    protected $table = 'tb_mas_roles';
    protected $primaryKey = 'intRoleID';
    public $timestamps = false;

    protected $fillable = [
        'strCode',
        'strName',
        'strDescription',
        'intActive',
    ];

    /**
     * Users that belong to this role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'tb_mas_user_roles', 'intRoleID', 'intUserID');
    }
}
