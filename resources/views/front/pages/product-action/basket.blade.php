@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('google-tag-manager')
    <script>
        dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
        dataLayer.push({
            event: "view_cart",
            ecommerce: {
                currency: "MDL",
                value: {!! priceFormatForGA4($discount_goods_price && $discount_goods_price > 0 ? $total_price + $costul_livrarei - $discount_goods_price : $total_price + $costul_livrarei) ?? '' !!},
                items: {!! $goods_objects ?? '' !!}
            }
        });
    </script>

@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('cart-page') }}
            </div>
        </div>

        <div class="{{ !empty($basket) && count($basket) ? 'basket' : 'basket-end' }}">
            <div class="container">
                @if(!empty($basket) && count($basket))
                    <div class="basket-inner">
                        <div class="basket-left">
                            <div class="basket-head">
                                <div class="basket-img">{{ ShowLabelById(78) }}: <span
                                        class="basket-count">{{ $basket_items_count ?? '' }}</span></div>
                                <div class="basket-text">{{ ShowLabelById(79) }}</div>
                                <div class="basket-price">{{ ShowLabelById(119) }}</div>
                                <div class="basket-quantity">{{ ShowLabelById(80) }}</div>
                                <div class="basket-price">{{ ShowLabelById(81) }}</div>
                            </div>
                            <div class="basket-list">
                                @foreach($basket as $one_basket_item)
                                    @php
                                        $goods_price_collect = getGoodsPrice($goods_item[$one_basket_item->id]);
                                        $item_price = $goods_price_collect->price;
                                        if($one_basket_item->discount_procent > 0)
                                            $item_price = round($item_price * (100 - $one_basket_item->discount_procent) / 100);
                                        elseif($one_basket_item->discount_summa > 0)
                                            $item_price = round($item_price - $one_basket_item->discount_summa);
                                    @endphp
                                    <div class="basket-item{{ !empty($goods_promo) && !empty($goods_promo[$one_basket_item->goods_item_id]) && !empty($goods_promo_cadou_list) && $one_basket_item->promo_one_c_id > 0 && $one_basket_item->has_cadou == 1 ? ' has-free-gift' : '' }}">
                                        <div class="basket-img">
                                            <a href="{{ route('catalog-product', ['product', $goods_item[$one_basket_item->id]->alias]) }}">
                                                <img
                                                    src="{{ $goods_item[$one_basket_item->id]->oImage && $goods_item[$one_basket_item->id]->oImage->img && file_exists('upfiles/goods-items/s/' . showImg($goods_item[$one_basket_item->id]->oImage->img)) ? asset('upfiles/goods-items/s/'. showImg($goods_item[$one_basket_item->id]->oImage->img)) : asset('front-assets/img/no-image-xs.png') }}"
                                                    alt="{{ $goods_item[$one_basket_item->id]->itemByLang->name ?? '' }}">
                                            </a>
                                        </div>
                                        <div class="basket-text">
                                            <div class="basket-name">
                                                <a href="{{ route('catalog-product', ['product', $goods_item[$one_basket_item->id]->alias]) }}">{{ $goods_item[$one_basket_item->id]->itemByLang->name ?? '' }}</a>
                                            </div>
                                            <div class="basket-desc">
                                                @if($goods_item[$one_basket_item->id]->getBrand)
                                                    <p>{{ ShowLabelById(8) }}
                                                        : {{ $goods_item[$one_basket_item->id]->getBrand->itemByLang->name ?? '' }}</p>
                                                @endif
                                                @if($goods_item[$one_basket_item->id]->articol)
                                                    <p>{{ ShowLabelById(9) }}
                                                        : {{ $goods_item[$one_basket_item->id]->articol ?? '' }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="basket-price">
                                            <div class="basket-price-old"
                                                 id="item-discount-{{ $one_basket_item->goods_item_id ?? '' }}">
                                                @if(($goods_price_collect->price_promo && $goods_price_collect->price_promo > 0 && $goods_price_collect->price_promo < $goods_price_collect->price_default) || $one_basket_item->discount_procent > 0 || $one_basket_item->discount_summa > 0)
                                                    <span
                                                        class="item-real-price">{{ $goods_price_collect->price_default ?? '' }}</span> {{ ShowLabelById(3,$lang_id) }}
                                                @endif
                                            </div>
                                            <div class="basket-price-current"{{--E47F9E--}}><span
                                                    id="item-price-{{ $one_basket_item->goods_item_id ?? '' }}">{{ $item_price ?? 0 }} </span>{{ ShowLabelById(3,$lang_id) }}
                                            </div>
                                        </div>

                                        <div class="basket-quantity">
                                            <div class="product-quantity">
                                                <button type="button" class="count-minus count-minus-change"
                                                        onclick="this.parentNode.querySelector('input[type=number]').value--">
                                                    <svg>
                                                        <use
                                                            xlink:href="{{ asset('front-assets/svg/sprite.svg#minus') }}"></use>
                                                    </svg>
                                                </button>
                                                <input type="number" class="basket-quantity-change" min="1"
                                                       value="{{ $one_basket_item->items_count ?? 1 }}"
                                                       data-goods-item-id="{{ $goods_item[$one_basket_item->id]->id ?? '' }}"
                                                       data-page="cart"
                                                       @if($one_basket_item->promo_one_c_id > 0 && $one_basket_item->has_cadou == 1) data-cadou="{{ $goods_promo[$one_basket_item->goods_item_id]->one_c_id ?? '' }}"
                                                       @endif @if($one_basket_item->promo_one_c_id > 0) data-promo="{{ $one_basket_item->promo_one_c_id ?? '' }}"@endif>
                                                <button type="button" class="count-plus count-plus-change"
                                                        onclick="this.parentNode.querySelector('input[type=number]').value++">
                                                    <svg>
                                                        <use
                                                            xlink:href="{{ asset('front-assets/svg/sprite.svg#plus') }}"></use>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div
                                            class="basket-price"><span
                                                class="one-item-total-price">{{ getDefaultPriceFormat($goods_price_collect->price * $one_basket_item->items_count) }}</span> {{ ShowLabelById(3) }}
                                        </div>
                                        <div class="basket-cta">
                                            <a href="javascript:;" class="remove-basket-item"
                                               data-goods-item-id="{{ $goods_item[$one_basket_item->id]->id ?? '' }}">
                                                <svg>
                                                    <use
                                                        xlink:href="{{ asset('front-assets/svg/sprite.svg#delete') }}"></use>
                                                </svg>
                                            </a>
                                            <a href="javascript:;" class="{{ $global_user ? ' add-to-wish' : ' open-login-modal' }}{{ $global_user && $goods_item[$one_basket_item->id]->checkIfWishItemExist ? ' active' : '' }}" data-goods-item-id="{{ $goods_item[$one_basket_item->id]->id ?? '' }}">
                                                <svg>
                                                    <use
                                                        xlink:href="{{ asset('front-assets/svg/sprite.svg#heart') }}"></use>
                                                    <use
                                                        xlink:href="{{ asset('front-assets/svg/sprite.svg#heart-active') }}"></use>
                                                </svg>
                                            </a>
                                        </div>
                                        @if(!empty($basket_promo) && !empty($basket_promo[$one_basket_item->id]) && $one_basket_item->promo_one_c_id > 0)
                                            <div
                                                class="basket-quantity-info d-none d-md-block discount-offer-{{$one_basket_item->promo_one_c_id}}">
                                                {!! str_replace(['{items_count}','{conditions}'], [$basket_promo[$one_basket_item->id]->cant_pentru_disc, $one_basket_item->discount_summa > 0 ? $one_basket_item->discount_summa . ' ' . ShowLabelById(3,$lang_id) : $basket_promo[$one_basket_item->id]->discount_procent.'%'], showSettingBodyByAlias('promo-text-cant-discount')) !!}
                                            </div>
                                        @endif
                                    </div>
                                    @if(!empty($goods_promo) && !empty($goods_promo[$one_basket_item->goods_item_id]) && !empty($goods_promo_cadou_list) && $one_basket_item->promo_one_c_id > 0 && $one_basket_item->has_cadou == 1)
                                        <div class="free-gift" data-basket-item-id="{{ $one_basket_item->id ?? '' }}">
                                            <div class="free-gift-head">
                                                <div class="free-gift-head-icon">
                                                    <img src="{{ asset('front-assets/img/icons/gift.svg') }}" alt="Gift">
                                                </div>
                                                <p>{!! str_replace('{items_count}',$goods_promo[$one_basket_item->goods_item_id]->cant_pentru_disc, showSettingBodyByAlias('promo-text-cadou')) !!}</p>
                                            </div>
                                            <div class="free-gift-list">
                                                <ul>
                                                    @foreach($goods_promo_cadou_list as $one_promo_cadou)
                                                        <li>
                                                            <input type="radio" id="gift-{{ $one_promo_cadou->goods_item_id ?? '' }}-{{ $one_basket_item->id ?? '' }}" name="gift" value="{{ $one_promo_cadou->one_c_id ?? '' }}" class="cadou-{{ $one_basket_item->goods_item_id }}"{{ $one_basket_item->items_count >= $goods_promo[$one_basket_item->goods_item_id]->cant_pentru_disc ? '' : ' disabled="disabled"' }}{{ $one_basket_item->related_one_c_id == $one_promo_cadou->one_c_id ? ' checked' : '' }}>
                                                            <label for="gift-{{ $one_promo_cadou->goods_item_id ?? '' }}-{{ $one_basket_item->id ?? '' }}" class="select-gift" data-related-id="{{ $one_promo_cadou->one_c_id ?? '' }}" data-promo-id="{{ $one_promo_cadou->goods_promo_id ?? '' }}" data-basket-id="{{ $one_basket_item->id ?? '' }}">
                                                                <span class="free-gift-img">
                                                                    <img
                                                                        src="{{ $one_promo_cadou->getGoodsItemId->oImage && $one_promo_cadou->getGoodsItemId->oImage->img && file_exists('upfiles/goods-items/s/' . showImg($one_promo_cadou->getGoodsItemId->oImage->img)) ? asset('upfiles/goods-items/s/'. showImg($one_promo_cadou->getGoodsItemId->oImage->img)) : asset('front-assets/img/no-image-xs.png') }}"
                                                                        alt="{{ $one_promo_cadou->getGoodsItemId->itemByLang->name ?? '' }}">
                                                                </span>
                                                                <span class="free-gift-text">
                                                                    <span
                                                                        class="free-gift-name">{{ $one_promo_cadou->getGoodsItemId->itemByLang->name ?? '' }}</span>
                                                                    @if($one_promo_cadou->getGoodsItemId->getBrand)
                                                                        <span
                                                                            class="free-gift-desc">{{ ShowLabelById(8) }}: {{ $one_promo_cadou->getGoodsItemId->getBrand->itemByLang->name ?? '' }}</span>
                                                                    @endif
                                                                </span>
                                                            </label>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="basket-footer">
                                <div class="basket-footer-back">
                                    <a href="{{ route('catalog-product') }}" class="button button-black--inversed">
                                        <svg>
                                            <use
                                                xlink:href="{{ asset('front-assets/svg/sprite.svg#arrow-right') }}"></use>
                                        </svg>
                                        <span>{{ ShowLabelById(10) }}</span>
                                    </a>
                                </div>
                                <div class="basket-footer-cta">

                                    <a href="javascript:;" class="remove-all-items">
                                        <svg>
                                            <use xlink:href="{{ asset('front-assets/svg/sprite.svg#delete') }}"></use>
                                        </svg>
                                        <span>{{ ShowLabelById(237) }}</span>
                                    </a>
                                    <a href="javascript:;" class="update-cart">
                                        <svg>
                                            <use xlink:href="{{ asset('front-assets/svg/sprite.svg#refresh') }}"></use>
                                        </svg>
                                        <span>{{ ShowLabelById(238) }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="basket-right">
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
                                                    onclick="saveForm(this)" data-form-id="check-promocod">{{ ShowLabelById(231) }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                {{--<div class="promo-code-success">
                                    <img src="{{ asset('front-assets/img/icons/success.svg') }}" alt="Promocode">
                                    <p>Cod promoțional aplicat cu succes</p>
                                </div>--}}
                            </div>
                            <div class="order-summary">
                                <div class="order-summary-head">{{ ShowLabelById(225) }}</div>
                                <div class="order-summary-inner">
                                    <div class="order-summary-content">
                                        <div class="order-summary-row">
                                            <p>{{ ShowLabelById(226) }}: </p>
                                            <p><span
                                                    class="basket-subtotal-price">{{ getDefaultPriceFormat($total_price) }}</span>
                                                {{ ShowLabelById(3) }}</p>
                                        </div>

                                        <div
                                            class="order-summary-row show-discount{{ !$discount_goods_price ? ' d-none' : '' }}"
                                            style="color: #80CC28">
                                            <p>{{ ShowLabelById(239) }}: </p>
                                            <p><span
                                                    class="discount">{{ $discount_goods_price ?? '' }}</span> {{ ShowLabelById(3) }}
                                            </p>
                                        </div>

                                        <div class="order-summary-row">
                                            <p>{{ ShowLabelById(82) }}:</p>
                                            <p><span
                                                    class="basket-delivery-price">{{ getDefaultPriceFormat($costul_livrarei) }}</span>
                                                {{ ShowLabelById(3) }}</p>
                                        </div>
                                        <div
                                            class="order-summary-info for-free-delivery-row{{ $pina_livrare && $pina_livrare > 0 ? '' : ' d-none' }}">
                                            <p>{{ ShowLabelById(227) }}: <span
                                                    class="basket-for-free-delivery">{{ getDefaultPriceFormat($pina_livrare) }}</span>
                                                {{ ShowLabelById(3) }}</p>
                                        </div>
                                    </div>
                                    <div class="order-summary-total">
                                        <div class="order-summary-row">
                                            <p>{{ ShowLabelById(228) }}: </p>
                                            <p><span
                                                    class="basket-total-price">{{ getDefaultPriceFormat($discount_goods_price && $discount_goods_price > 0 ? $total_price + $costul_livrarei - $discount_goods_price : $total_price + $costul_livrarei) }}</span>
                                                {{ ShowLabelById(3) }}</p>
                                        </div>
                                    </div>
                                    <div class="order-summary-desc">
                                        {!! showSettingBodyByAlias('text-delivery-descr') !!}
                                    </div>

                                    <div class="order-summary-footer">
                                        <a href="{{ route('checkout') }}?type={{ $global_user ? 'already' : 'new' }}"
                                           class="button button--black" onclick='onCheckout({!! $goods_objects !!}, {{ priceFormatForGA4($discount_goods_price && $discount_goods_price > 0 ? $total_price + $costul_livrarei - $discount_goods_price : $total_price + $costul_livrarei) }});onCheckoutFB({{ $goods_items_ids ?? '' }}, {{ priceFormatForGA4($discount_goods_price && $discount_goods_price > 0 ? $total_price + $costul_livrarei - $discount_goods_price : $total_price + $costul_livrarei) }}, {{ $basket_items_count ?? '' }})'>{{ ShowLabelById(240) }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
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
                @endif
            </div>
        </div>

    </div>

@stop
