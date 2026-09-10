<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'payments/bank/callback',
        // VictoriaBank возвращает браузер на BACKREF POST-запросом со своей
        // страницы (форма без нашего CSRF-токена). backref() только читает
        // статус заказа из БД и редиректит дальше — ничего не меняет.
        'payments/bank/backref/*',
    ];
}
