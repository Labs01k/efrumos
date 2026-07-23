<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModulesId extends Model
{
    use HasFactory;
    protected $table = 'modules_id';

    protected $fillable = [
        'p_id', 'level', 'alias', 'position', 'active', 'deleted', 'controller', 'models', 'view', 'root'
    ];


}
