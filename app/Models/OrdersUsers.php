<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdersUsers extends Model
{
    use HasFactory;

    protected $table = 'orders_users';

    protected $fillable = [
        'orders_id', 'user_ip', 'name', 'last_name', 'email', 'phone', 'descr', 'address', 'country', 'city', 'zip_code', 'apartment', 'city_area', 'birth', 'district_id'
    ];
}
