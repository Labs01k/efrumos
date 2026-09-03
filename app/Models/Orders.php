<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'basket_id', 'type', 'admin_comment', 'active', 'delete', 'paid', 'fast_order', 'delivery_method', 'pickup_shop_id', 'pay_method', 'discount', 'was_sent', 'front_user_id', 'payment_status', 'payment_status_changed_at'
    ];

    protected $casts = [
        'payment_status' => PaymentStatus::class,
        'payment_status_changed_at' => 'datetime',
    ];

    /** Магазин самовывоза: разовый выбор на заказ, в профиле не хранится. */
    public function pickupShop()
    {
        return $this->belongsTo('App\Models\ShopsId', 'pickup_shop_id', 'id');
    }

    public function paymentStatusLogs()
    {
        return $this->hasMany(OrderPaymentStatusLog::class, 'orders_id', 'id')->orderBy('created_at', 'desc');
    }

    public function ordersData()
    {
        return $this->hasOne('App\Models\OrdersData', 'orders_id', 'id')->withDefault();
    }

    public function ordersUsers()
    {
        return $this->hasOne('App\Models\OrdersUsers', 'orders_id', 'id')->withDefault();
    }

    public function ordersFrontUser()
    {
        return $this->hasOne('App\Models\FrontUser', 'id', 'front_user_id');
    }

    public function basket()
    {
        return $this->hasMany('App\Models\Basket', 'basket_id', 'basket_id')->orderBy('created_at','desc');
    }
}
