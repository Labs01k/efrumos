@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@if(($payment_outcome ?? 'paid') === 'paid')
@section('google-tag-manager')
    <script>
        dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
        dataLayer.push({
            event: "purchase",
            ecommerce: {
                transaction_id: "{{ $orders->id ?? '' }}",
                affiliation: "Efrumos Beauty Shop",
                value: "{{ priceFormatForGA4($orders->ordersData->total_price) }}",
                tax: "4.90",
                shipping: "{{ priceFormatForGA4($orders->ordersData->delivery_cost) }}",
                currency: "MDL",
                coupon: "",
                items: {!! $goods_objects ?? '' !!}
            }
        });

        fbq('track', 'Purchase', {
            content_type: 'product',
            content_ids: {!! $goods_items_ids ?? '' !!},
            value: {{ priceFormatForGA4($orders->ordersData->total_price + $orders->ordersData->delivery_cost) }},
            num_items: {{ $orders->ordersData->total_count ?? '' }},
            contents: {!! json_encode($goods_objects_fb) !!},
            currency: 'MDL'
        });
    </script>
@stop
@endif

@section('styles')
    <link rel="stylesheet" href="{{ asset('front-assets/css/checkout-result.css?v=') . config('custom.front.css_version') }}">
@stop

@section('container')
    @php
        // Макет «1. Онлайн-оплата»: успех — ноды 783:8422 (1024) и 787:17968 (375),
        // ошибка — 787:17932 и 787:17989. Экран ожидания в макете не нарисован,
        // он собран из того же окна.
        $result_outcome = $payment_outcome ?? 'paid';
        $result_paid_online = ($orders->pay_method ?? null) === 'card';
        // заказ без регистрации в «Моих заказах» не появится — гостю кнопку не показываем
        $result_can_track = !empty($global_user) && !empty($order_id);
        $result_cms_message = !$result_paid_online
            && $checkout_success_message && $checkout_success_message->itemByLang && $checkout_success_message->itemByLang->body
            ? $checkout_success_message->itemByLang
            : null;
    @endphp

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('checkout-success-page', $page_title ?? null) }}
            </div>
        </div>

        <div class="checkout-result checkout-result--{{ $result_outcome }}" role="dialog" aria-modal="true" aria-labelledby="checkout-result-title">
            <div class="checkout-result-window">
                <a href="{{ route('/') }}" class="checkout-result-close" aria-label="{{ trans('variables.checkout_continue_shopping') }}">
                    <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M12 4 4 12M4 4l8 8"/></svg>
                </a>

                <div class="checkout-result-icon" aria-hidden="true">
                    @switch($result_outcome)
                        @case('failed')
                            <svg viewBox="0 0 64 64"><circle cx="32" cy="32" r="26.67"/><path d="M24 24 40 40M40 24 24 40"/></svg>
                        @break
                        @case('processing')
                            <svg viewBox="0 0 64 64"><circle cx="32" cy="32" r="26.67"/><path d="M32 20v12l8 5"/></svg>
                        @break
                        @default
                            <svg viewBox="0 0 64 64"><circle cx="32" cy="32" r="26.67"/><path d="m21.33 33.33 5.34 5.34 14.66-13.34"/></svg>
                    @endswitch
                </div>

                <div class="checkout-result-text">
                    @switch($result_outcome)
                        @case('processing')
                            <h1 id="checkout-result-title">{{ trans('variables.checkout_processing_title') }}</h1>
                            <p>{{ trans('variables.checkout_processing_text', ['order' => $order_id]) }}</p>
                        @break
                        @case('failed')
                            <h1 id="checkout-result-title">{{ trans('variables.checkout_failed_title') }}</h1>
                            <p>{{ trans('variables.checkout_failed_text', ['order' => $order_id]) }}</p>
                        @break
                        @default
                            {{-- заказ без онлайн-оплаты: текст ведёт заказчик в CMS
                                 (Меню → success-order-message), в макете его нет --}}
                            @if($result_cms_message)
                                <h1 id="checkout-result-title">{{ $result_cms_message->short_descr ?: trans('variables.checkout_success_title') }}</h1>
                                <div class="checkout-result-cms">{!! str_replace('{order_id}', $order_id, $result_cms_message->body) !!}</div>
                            @else
                                <h1 id="checkout-result-title">{{ trans('variables.checkout_success_title') }}</h1>
                                <p>{{ trans($result_paid_online ? 'variables.checkout_success_paid_text' : 'variables.checkout_success_text', ['order' => $order_id]) }}</p>
                            @endif
                    @endswitch
                </div>

                <div class="checkout-result-actions">
                    @if($result_outcome === 'failed')
                        {{-- в макете «Вернуться в корзину», но корзина после оформления
                             уже пустая, а ТЗ требует повтор оплаты — решение заказчика --}}
                        @if($can_retry_payment ?? false)
                            <a href="{{ route('payments.bank.initiate', ['order' => $order_id, 'lang' => LANG]) }}" class="checkout-result-button">{{ trans('variables.checkout_retry_payment') }}</a>
                        @else
                            <a href="{{ route('/') }}" class="checkout-result-button">{{ trans('variables.checkout_continue_shopping') }}</a>
                        @endif
                        {{-- открывает чат «Открытая линия» (Bitrix24, грузится снаружи);
                             пока чат не загрузился — обычная ссылка на контакты --}}
                        <a href="{{ route('menu', 'contacts') }}" class="checkout-result-button checkout-result-button--outline" data-open-support-chat>{{ trans('variables.checkout_write_support') }}</a>
                    @else
                        <a href="{{ route('/') }}" class="checkout-result-button">{{ trans('variables.checkout_continue_shopping') }}</a>
                        @if($result_can_track)
                            <a href="{{ route('cabinet-orders') }}" class="checkout-result-button checkout-result-button--outline">{{ trans('variables.checkout_track_order') }}</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>

    </div>

@stop

@push('other-scripts')
    <script>
        document.addEventListener('click', function (event) {
            var link = event.target.closest && event.target.closest('[data-open-support-chat]');
            if (!link || !window.BXLiveChat || typeof window.BXLiveChat.open !== 'function') return;
            event.preventDefault();
            window.BXLiveChat.open();
        });
    </script>
@endpush
