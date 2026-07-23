<div class="common-modal-bg"></div>
<div class="common-modal-wrapper">
    <div class="basket-modal-inner">
        <div class="basket-modal-head">
            <div class="basket-modal-title">{{ ShowLabelById(22) }}</div>
            <button type="button" class="common-modal-close">
                <svg>
                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#close') }}"></use>
                </svg>
            </button>
        </div>

        <div class="basket-modal-content">
            @if(!empty($header_basket_items) && count($header_basket_items))
                <div class="basket-modal-list">
                    @foreach($header_basket_items as $one_basket_item)
                        @php
                            $goods_price_collect = getGoodsPrice($one_basket_item->goodsItemId);
                            //$goods_price = $goods_price_collect->promo_price > 0 ? $goods_price_collect->promo_price : $goods_price_collect->price;
                        @endphp
                        <div class="basket-modal-item">
                            <div class="basket-modal-item-img">
                                <a href="{{ route('catalog-product', ['product', $one_basket_item->goodsItemId->alias]) }}">
                                    <img
                                        src="{{ $one_basket_item->oImage && $one_basket_item->oImage->img && file_exists('upfiles/goods-items/s/' . showImg($one_basket_item->oImage->img)) ? asset('upfiles/goods-items/s/'. showImg($one_basket_item->oImage->img)) : asset('front-assets/img/no-image-xs.png') }}"
                                        alt="{{ $one_basket_item->goods_name ?? '' }}">
                                </a>
                            </div>
                            <div class="basket-modal-item-text">
                                <div class="basket-modal-item-name">
                                    <a href="{{ route('catalog-product', ['product', $one_basket_item->goodsItemId->alias]) }}">{{ $one_basket_item->goodsItemId->itemByLang->name ?? '' }}</a>
                                </div>
                                <div class="basket-modal-item-info">
                                    <div class="product-quantity">
                                        <button type="button" class="count-minus count-minus-change"
                                                onclick="this.parentNode.querySelector('input[type=number]').value--">
                                            <svg>
                                                <use
                                                    xlink:href="{{ asset('front-assets/svg/sprite.svg#minus') }}"></use>
                                            </svg>
                                        </button>
                                        <input type="number" class="basket-quantity-change" min="1" value="{{ $one_basket_item->items_count ?? 1 }}"
                                               data-goods-item-id="{{ $one_basket_item->goods_item_id ?? '' }}"
                                               data-page="header-cart">
                                        <button type="button" class="count-plus count-plus-change"
                                                onclick="this.parentNode.querySelector('input[type=number]').value++">
                                            <svg>
                                                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#plus') }}"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="basket-modal-item-price">
                                        x {{ $goods_price_collect->price ?? '' }} {{ ShowLabelById(3) }}</div>
                                </div>
                                <div class="basket-modal-item-total">{{ ShowLabelById(23) }}: <span class="one-item-total-price">{{ getDefaultPriceFormat($goods_price_collect->price * $one_basket_item->items_count) }}</span> {{ ShowLabelById(3) }}</div>
                            </div>
                            <div class="basket-modal-item-remove">
                                <a href="javascript:;"  class="remove-basket-item" data-goods-item-id="{{ $one_basket_item->goods_item_id ?? '' }}">
                                    <svg>
                                        <use xlink:href="{{ asset('front-assets/svg/sprite.svg#delete') }}"></use>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="basket-modal-empty">
                    <div class="basket-modal-empty-icon">
                        <img src="{{ asset('front-assets/img/icons/basket-empty.svg') }}" alt="Empty basket">
                    </div>
                    <div class="basket-modal-empty-text">
                        <p>{{ ShowLabelById(242) }}</p>
                    </div>
                    <div class="basket-modal-empty-link">
                        <a href="{{ route('catalog-product') }}" class="button">{{ ShowLabelById(243) }}</a>
                    </div>
                </div>
            @endif
        </div>

        @if(!empty($header_basket_items) && count($header_basket_items))
            <div class="basket-modal-footer">
                <div class="basket-modal-info">
                    <div class="basket-modal-row">
                        <p>{{ ShowLabelById(24) }}:</p>
                        <p><span class="header-basket-count">{{ $basket_count ?? '' }}</span></p>
                    </div>
                    <div class="basket-modal-row basket-modal-total">
                        <p>{{ ShowLabelById(25) }}:</p>
                        <p><span class="basket-subtotal-price">{{ getDefaultPriceFormat($header_total_price) }}</span> {{ ShowLabelById(3) }}</p>
                    </div>
                </div>
                <div class="basket-modal-cta">
                    <a href="{{ route('cart') }}" class="button button-black--inversed">{{ ShowLabelById(11) }}</a>
                    <a href="{{ route('checkout') }}?type={{ $global_user ? 'already' : 'new' }}" class="button button--black" onclick='onCheckout({!! $goods_objects !!}, {{ priceFormatForGA4($header_total_price) }});onCheckoutFB({{ $goods_items_ids ?? '' }}, {{ priceFormatForGA4($header_total_price) }}, {{ $basket_count ?? '' }})'>{{ ShowLabelById(26) }}</a>
                </div>
            </div>
        @endif

    </div>
</div>
