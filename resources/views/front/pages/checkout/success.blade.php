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
                            {{-- тексты в lang/*/variables.php: заказчик правит их
                                 переводами, как и остальной интерфейс сайта --}}
                            <div class="basket-end-title">{{ trans('variables.checkout_processing_title') }}</div>
                            <div class="basket-end-text">{{ trans('variables.checkout_processing_text', ['order' => $order_id]) }}</div>
                            <div class="basket-end-link">
                                <a href="{{ route('/') }}">{{ ShowLabelById(125) }}</a>
                            </div>
                        @break

                        @case('failed')
                            <div class="basket-end-title">{{ trans('variables.checkout_failed_title') }}</div>
                            <div class="basket-end-text">{{ trans('variables.checkout_failed_text', ['order' => $order_id]) }}</div>
                            {{-- по макету (нода 787:17933): повторная попытка и возврат
                                 в корзину; повтор оплаты — требование п.1 ТЗ --}}
                            <div class="basket-end-link">
                                <a href="{{ route('payments.bank.initiate', ['order' => $order_id, 'lang' => LANG]) }}" class="btn btn-primary">
                                    {{ trans('variables.checkout_retry_payment') }}
                                </a>
                                <a href="{{ route('cart') }}">{{ trans('variables.checkout_back_to_cart') }}</a>
                            </div>
                        @break

                        @default
                            <div class="basket-end-icon">
                                <img src="{{ asset('front-assets/img/icons/basket-success.svg') }}" alt="Success">
                            </div>
                            {{-- текст берётся из CMS (Меню → success-order-message);
                                 он заполнен не на всех языках, поэтому при пустом
                                 значении показываем перевод, а не пустой экран --}}
                            @if($checkout_success_message && $checkout_success_message->itemByLang && $checkout_success_message->itemByLang->body)
                                <div class="basket-end-title">{{ $checkout_success_message->itemByLang->short_descr ?? '' }}</div>
                                <div class="basket-end-text">
                                    {!! str_replace('{order_id}', $order_id, $checkout_success_message->itemByLang->body) !!}
                                </div>
                            @else
                                <div class="basket-end-title">{{ trans('variables.checkout_success_title') }}</div>
                                <div class="basket-end-text">{{ trans('variables.checkout_success_text', ['order' => $order_id]) }}</div>
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
