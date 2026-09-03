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

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('checkout-success-page') }}
            </div>
        </div>

        <div class="basket-end">
            <div class="container">
                <div class="basket-end-inner">
                    @switch($payment_outcome ?? 'paid')
                        @case('processing')
                            <div class="basket-end-icon">
                                <img src="{{ asset('front-assets/img/icons/basket-success.svg') }}" alt="Processing">
                            </div>
                            <div class="basket-end-title">
                                @switch(LANG)
                                    @case('ru') Платёж обрабатывается @break
                                    @case('en') Payment is being processed @break
                                    @default Plata este în curs de procesare
                                @endswitch
                            </div>
                            <div class="basket-end-text">
                                @switch(LANG)
                                    @case('ru') Мы получили ответ от банка и обрабатываем его. Статус заказа №{{ $order_id }} обновится в течение нескольких минут. @break
                                    @case('en') We received the bank's response and are processing it. Order #{{ $order_id }} status will update within a few minutes. @break
                                    @default Am primit răspunsul de la bancă și îl procesăm. Statutul comenzii nr. {{ $order_id }} se va actualiza în câteva minute.
                                @endswitch
                            </div>
                            <div class="basket-end-link">
                                <a href="{{ route('/') }}">{{ ShowLabelById(125) }}</a>
                            </div>
                        @break

                        @case('failed')
                            <div class="basket-end-title">
                                @switch(LANG)
                                    @case('ru') Оплата не прошла @break
                                    @case('en') Payment failed @break
                                    @default Plata nu a reușit
                                @endswitch
                            </div>
                            <div class="basket-end-text">
                                @switch(LANG)
                                    @case('ru') Заказ №{{ $order_id }} не был оплачен. Вы можете попробовать снова или выбрать другой способ оплаты. @break
                                    @case('en') Order #{{ $order_id }} was not paid. You can try again or choose a different payment method. @break
                                    @default Comanda nr. {{ $order_id }} nu a fost achitată. Puteți încerca din nou sau alege o altă metodă de plată.
                                @endswitch
                            </div>
                            <div class="basket-end-link">
                                <a href="{{ route('payments.bank.initiate', ['order' => $order_id, 'lang' => LANG]) }}" class="btn btn-primary">
                                    @switch(LANG)
                                        @case('ru') Попробовать снова @break
                                        @case('en') Try again @break
                                        @default Încearcă din nou
                                    @endswitch
                                </a>
                                <a href="{{ route('/') }}">{{ ShowLabelById(125) }}</a>
                            </div>
                        @break

                        @default
                            <div class="basket-end-icon">
                                <img src="{{ asset('front-assets/img/icons/basket-success.svg') }}" alt="Success">
                            </div>
                            @if($checkout_success_message && $checkout_success_message->itemByLang)
                                <div class="basket-end-title">{{ $checkout_success_message->itemByLang->short_descr ?? '' }}</div>
                                <div class="basket-end-text">
                                    {!! str_replace('{order_id}', $order_id, $checkout_success_message->itemByLang->body) !!}
                                </div>
                            @endif
                            <div class="basket-end-link">
                                <a href="{{ route('/') }}">{{ ShowLabelById(125) }}</a>
                            </div>
                    @endswitch
                </div>
            </div>
        </div>

    </div>

@stop
