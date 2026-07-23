<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Labels extends Model
{
    use HasFactory;

    protected $table = 'labels';

    protected $fillable = [
        'labels_id', 'lang_id', 'name'
    ];

    public function labelsId()
    {
        return $this->hasOne('App\Models\LabelsId', 'id', 'labels_id');
    }

}
