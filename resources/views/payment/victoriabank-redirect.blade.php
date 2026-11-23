<!DOCTYPE html>
{{--
    Промежуточная страница перед уходом на защищённую страницу банка.
    Покупатель видит её долю секунды, но текст всё равно должен быть на его
    языке; имя банка не называем — адреса платёжных роутов тоже обезличены.
--}}
<html lang="{{ $lang ?? app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ trans('variables.payment_redirect_title') }}</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fafafa;
            color: #465061;
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            font-size: 16px;
            text-align: center;
        }
        .pay-redirect { padding: 24px; max-width: 420px; }
        .pay-redirect p { margin: 0; }
        .pay-order-summary {
            margin-bottom: 20px;
            padding: 16px;
            background: #ffffff;
            border-radius: 8px;
            text-align: left;
            font-size: 14px;
        }
        .pay-order-summary__title { font-weight: 600; margin-bottom: 8px; }
        .pay-order-summary__row { display: flex; justify-content: space-between; gap: 12px; padding: 2px 0; }
        .pay-order-summary__row--total { font-weight: 600; border-top: 1px solid #eeeeee; margin-top: 6px; padding-top: 6px; }
        .pay-redirect-spinner {
            width: 32px;
            height: 32px;
            margin: 0 auto 16px;
            border: 3px solid #eeeeee;
            border-top-color: #db6e97;
            border-radius: 50%;
            animation: pay-spin 900ms linear infinite;
        }
        @keyframes pay-spin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) {
            .pay-redirect-spinner { animation: none; }
        }
    </style>
</head>
<body>
<div class="pay-redirect">
    {{-- Договор с банком (2.3) — показать № заказа, состав, доставку и итог
         непосредственно перед оплатой, а не просто редиректить на банк. --}}
    @if(isset($order) && $order->ordersData)
        <div class="pay-order-summary">
            <div class="pay-order-summary__title">
                {{ trans('variables.payment_order_summary_title') }} №{{ $order->id }}
            </div>
            @foreach($order->basket as $item)
                <div class="pay-order-summary__row">
                    <span>{{ $item->goods_name }} × {{ $item->items_count }}</span>
                    <span>{{ getDefaultPriceFormat($item->goods_price * $item->items_count) }}</span>
                </div>
            @endforeach
            <div class="pay-order-summary__row">
                <span>{{ trans('variables.payment_order_summary_delivery') }}</span>
                <span>{{ getDefaultPriceFormat($order->ordersData->delivery_cost) }}</span>
            </div>
            <div class="pay-order-summary__row pay-order-summary__row--total">
                <span>{{ trans('variables.payment_order_summary_total') }}</span>
                <span>{{ getDefaultPriceFormat($order->ordersData->amountDue()) }}</span>
            </div>
        </div>
    @endif
    <div class="pay-redirect-spinner" aria-hidden="true"></div>
    <p>{{ trans('variables.payment_redirect_text') }}</p>
</div>
<form id="vbForm" method="POST" action="{{ $endpoint }}">
    @foreach ($fields as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
    <noscript>
        <button type="submit">{{ trans('variables.payment_redirect_button') }}</button>
    </noscript>
</form>
<script>document.getElementById('vbForm').submit();</script>
</body>
</html>
