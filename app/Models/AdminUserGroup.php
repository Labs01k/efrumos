<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminUserGroup extends Model
{
    use HasFactory;

    protected $table = 'admin_user_group';

    protected $fillable = [
        'active', 'deleted', 'name', 'alias'
    ];

    public function userPermission()
    {
        return $this->hasMany('App\Models\AdminUserActionPermision', 'admin_user_group_id', 'id');
    }




}
