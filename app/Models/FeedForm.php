<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class FeedForm extends Model
{
    use HasFactory;

    protected $table = 'feedform';

    protected $fillable = [
        'name', 'ip', 'active', 'comment', 'phone', 'subject', 'agree', 'active', 'seen', 'email', 'goods_item_id'
    ];

}
