<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminUserActionPermision extends Model
{
    use HasFactory;

    protected $table = 'admin_user_action_permision';

    protected $fillable = [
        'admin_user_group_id', 'new', 'save', 'active', 'del_to_rec', 'del_from_rec', 'php_code', 'modules_id', 'moderate'
    ];

    public function group(){
        return $this->hasOne('App\Models\AdminUserGroup', 'id', 'admin_user_group_id');
    }

    public function modules(){
        return $this->hasOne('App\Models\Modules', 'id', 'modules_id');
    }

}
