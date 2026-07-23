@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

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
                </div>
            </div>
        </div>

    </div>

@stop
