@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('cart-page') }}
            </div>
        </div>

        @if(!empty($basket) && count($basket))
            <div class="basket">
                <div class="container">
                    <div class="basket-inner">
                        <div class="basket-left">
                            <div class="basket-account-head">
                                <h1 class="h2">{{ ShowLabelById(26) }}</h1>
                                <div class="basket-account-head-link">
                                    <a href="{{ route('cart') }}">{{ ShowLabelById(221) }}</a>
                                </div>
                            </div>
                            <div class="basket-account-tabs">
                                <ul>
                                    @if(!$global_user)
                                        <li{{ request()->input('type') == 'new' ? ' class=active' : (request()->input('type') != 'already' && request()->input('type') != 'without' ? ' class=active' : '') }}>
                                            <a href="{{ route('checkout') }}?type=new">{{ ShowLabelById(222) }}</a>
                                        </li>
                                    @endif
                                    <li{{ request()->input('type') == 'already' ? ' class=active' : ($global_user && request()->input('type') != 'without' ? ' class=active' : '') }}>
                                        <a href="{{ route('checkout') }}?type=already">{{ ShowLabelById(223) }}</a>
                                    </li>
                                    <li{{ request()->input('type') == 'without' ? ' class=active' : ''  }}>
                                        <a href="{{ route('checkout') }}?type=without">{{ ShowLabelById(224) }}</a>
                                    </li>
                                </ul>
                            </div>
                            @switch(request()->input('type'))
                                @case('new')
                                    @if(!$global_user)
                                        @include('front.pages.checkout.checkout-types.new')
                                    @endif
                                    @break

                                @case('already')
                                    @include('front.pages.checkout.checkout-types.already')
                                    @break

                                @case('without')
                                    @include('front.pages.checkout.checkout-types.without')
                                    @break

                                @default
                                    @if(!$global_user)
                                        @include('front.pages.checkout.checkout-types.new')
                                    @else
                                        @include('front.pages.checkout.checkout-types.already')
                                    @endif
                                    @break
                            @endswitch
                        </div>

                        <div class="basket-right">
                            <div class="order-summary">
                                <div class="order-summary-head">{{ ShowLabelById(225) }}</div>
                                <div class="order-summary-inner">
                                    <div class="order-summary-lisy">
                                        @foreach($basket as $one_basket_item)
                                            @php
                                                $goods_price_collect = getGoodsPrice($goods_item[$one_basket_item->id]);
                                                //$goods_price = $goods_price_collect->promo_price > 0 ? $goods_price_collect->promo_price : $goods_price_collect->price;
                                            @endphp
                                            <div class="order-summary-item">
                                                <div class="order-summary-item-img">
                                                    <img
                                                        src="{{ $goods_item[$one_basket_item->id]->oImage && $goods_item[$one_basket_item->id]->oImage->img && file_exists('upfiles/goods-items/s/' . showImg($goods_item[$one_basket_item->id]->oImage->img)) ? asset('upfiles/goods-items/s/'. showImg($goods_item[$one_basket_item->id]->oImage->img)) : asset('front-assets/img/no-image-xs.png') }}"
                                                        alt="{{ $goods_item[$one_basket_item->id]->itemByLang->name ?? '' }}">
                                                </div>
                                                <div class="order-summary-item-text">
                                                    <div
                                                        class="order-summary-item-name">{{ $goods_item[$one_basket_item->id]->itemByLang->name ?? '' }}
                                                    </div>
                                                    <div
                                                        class="order-summary-item-desc">{{ $one_basket_item->items_count ?? '' }}
                                                        x
                                                        <b>{{ $goods_price_collect->price ?? '' }} {{ ShowLabelById(3) }}</b>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="order-summary-content">
                                        <div class="order-summary-row">
                                            <p>{{ ShowLabelById(226) }}: </p>
                                            <p>{{ getDefaultPriceFormat($total_price) }} {{ ShowLabelById(3) }}</p>
                                        </div>
                                        <div class="order-summary-row">
                                            <p>{{ ShowLabelById(82) }}: </p>
                                            <p><span
                                                    class="basket-delivery-price">{{ getDefaultPriceFormat($costul_livrarei) }}</span> {{ ShowLabelById(3) }}
                                            </p>
                                        </div>
                                        <div
                                            class="order-summary-info" {{ $pina_livrare && $pina_livrare > 0 ? '' : 'style=display:none;' }}>
                                            <p>{{ ShowLabelById(227) }}
                                                : {{ getDefaultPriceFormat($pina_livrare) }} {{ ShowLabelById(3) }}</p>
                                        </div>
                                    </div>
                                    <div class="order-summary-total">
                                        <div class="order-summary-row">
                                            <p>{{ ShowLabelById(228) }}: </p>
                                            <p><span
                                                    class="basket-total-price">{{ getDefaultPriceFormat($discount_goods_price && $discount_goods_price > 0 ? $total_price + $costul_livrarei - $discount_goods_price : $total_price + $costul_livrarei) }}</span> {{ ShowLabelById(3) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="promo-code">
                                <div class="promo-code-inner">
                                    <button type="button" class="promo-code-btn active">
                                        <span>{{ ShowLabelById(229) }}</span>
                                        <svg>
                                            <use
                                                xlink:href="{{ asset('front-assets/svg/sprite.svg#arrow-down') }}"></use>
                                        </svg>
                                    </button>
                                    <div class="promo-code-content" style="display: block;">
                                        <form action="{{ route('check-promocod') }}"
                                              id="check-promocod" enctype="multipart/form-data">

                                            @csrf
                                            <input type="hidden" name="link" value="cart">

                                            <label for="promocod" class="sr-only">{{ ShowLabelById(230) }}</label>
                                            <div class="position-relative">
                                                <input type="text" id="promocod" name="promocod"
                                                       placeholder="{{ ShowLabelById(230) }}">
                                            </div>
                                            <button type="submit" class="button prevent-repeated-click-promocod"
                                                    onclick="saveForm(this)"
                                                    data-form-id="check-promocod">{{ ShowLabelById(231) }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="basket">
                <div class="container">
                    <div class="basket-end-inner">
                        <div class="basket-end-icon">
                            <img src="{{ asset('front-assets/img/icons/basket-empty.svg') }}" alt="Empty cart">
                        </div>
                        <div class="basket-end-title">{{ ShowLabelById(241) }}</div>
                        <div class="basket-end-text">
                            <p>{{ ShowLabelById(242) }}</p>
                        </div>
                        <div class="basket-end-link">
                            <a href="{{ route('catalog-product') }}">{{ ShowLabelById(243) }}</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

@stop

@push('other-scripts')
    <script>
        /* Детали магазина самовывоза под селектом: адрес, телефон, часы работы. */
        (function () {
            function renderPickupDetails(select) {
                var details = select.parentNode.querySelector('[data-pickup-details]');
                var option = select.options[select.selectedIndex];
                if (!details || !option) return;

                var rows = [];
                if (option.dataset.address) rows.push('<p class="checkout-pickup-address">' + option.dataset.address + '</p>');
                if (option.dataset.phone) rows.push('<p class="checkout-pickup-phone">' + option.dataset.phone + '</p>');
                if (option.dataset.schedule) rows.push('<p class="checkout-pickup-schedule">' + option.dataset.schedule + '</p>');
                details.innerHTML = rows.join('');
            }

            document.addEventListener('change', function (event) {
                if (event.target.classList && event.target.classList.contains('checkout-pickup-select')) {
                    renderPickupDetails(event.target);
                }
            });

            document.addEventListener('DOMContentLoaded', function () {
                Array.prototype.forEach.call(document.querySelectorAll('.checkout-pickup-select'), renderPickupDetails);
            });
        })();
    </script>
@endpush
