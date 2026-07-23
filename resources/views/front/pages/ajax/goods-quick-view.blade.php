@php
    $goods_price_collect = getGoodsPrice($goods_item);
    //$goods_price = $goods_price_collect->promo_price > 0 ? $goods_price_collect->promo_price : $goods_price_collect->price;
    if($goods_price_collect->price_promo > 0 || CheckIfItemIsInPromo($goods_item->id))
        $label_color = '#E47F9E';
    elseif($goods_item->new_element == 1)
        $label_color = '#80CC28';
    elseif($goods_item->popular_element == 1)
        $label_color = '#58379C';
    else
        $label_color = '#465061';

    $color_by_status = goodsColorByStatus($goods_item, $goods_price_collect);
@endphp
<div class="quick-view-inner">
    <div class="quick-view-img">
        <img
            src="{{ $goods_item->oImage && $goods_item->oImage->img && file_exists('upfiles/goods-items/m/' . showImg($goods_item->oImage->img)) ? asset('upfiles/goods-items/m/'. showImg($goods_item->oImage->img)) : asset('front-assets/img/no-image-goods-m.png') }}"
            loading="lazy"
            alt="{{ $goods_item->itemByLang->name ?? '' }}">
    </div>
    <div class="quick-view-content">
        <div class="product-end-content">
            <div class="product-end-head">
                <div class="product-end-labels">
                    @if(isset($color_by_status['price_promo']))
                        {{--<p style="background-color: #E681A0;">Sale</p>--}}
                        <p style="background-color: {{ $color_by_status['price_promo'] ?? '' }}">{{ ShowLabelById(179) }}</p>
                        @if($goods_price_collect->price_promo > 0)
                            <p style="background: #FAE5EC; color: #E47F9E;">
                                -{{ $goods_price_collect->promo_percent ?? '' }}%</p>
                        @endif
                    @endif
                    @if(isset($color_by_status['new_element']))
                        <p style="background-color: {{ $color_by_status['new_element'] ?? '' }}">{{ ShowLabelById(180) }}</p>
                    @endif
                    @if(isset($color_by_status['popular_element']))
                        <p style="background-color: {{ $color_by_status['popular_element'] ?? '' }}">{{ ShowLabelById(181) }}</p>
                    @endif
                </div>
            </div>
            <div class="h1">{{ $goods_item->itemByLang->name ?? '' }}</div>
            <div class="product-end-info">
                <div class="product-info-row">
                    @if($goods_item->articol)
                        <div class="product-info-item">
                            <p>{{ ShowLabelById(9) }}: <span>{{ $goods_item->articol ?? '' }}</span></p>
                        </div>
                    @endif
                    @if($goods_item->one_c_code)
                        <div class="product-info-item">
                            <p>{{ ShowLabelById(13) }}:
                                <span>{{ $goods_item->one_c_code ?? '' }}</span></p>
                        </div>
                    @endif
                </div>
                @if($goods_item->getBrand && $goods_item->getBrand->parent)
                    <div class="product-info-item product-info--border">
                        <p>{{ ShowLabelById(8) }}:
                            <span><b><a href="{{ route('brands', $goods_item->getBrand->parent->alias) }}">{{ $goods_item->getBrand->parent->itemByLang->name ?? '' }}</a></b></span>
                            / <span><a
                                    href="{{ route('brands', $goods_item->getBrand->alias) }}">{{ $goods_item->getBrand->itemByLang->name ?? '' }}</a></span>
                        </p>
                    </div>
                @elseif($goods_item->getBrand)
                    <p>
                        {{ ShowLabelById(8) }}:
                        <span><a
                                href="{{ route('brands', $goods_item->getBrand->alias) }}">{{ $goods_item->getBrand->itemByLang->name ?? '' }}</a></span>
                    </p>
                @endif

                @if($goods_item->getType && $goods_item->getType->itemByLang)
                    <div class="product-info-item">
                        <p>{{ ShowLabelById(14) }}:
                            <span>{{ $goods_item->getType->itemByLang->name ?? '' }}</span></p>
                    </div>
                @endif
            </div>
            <div class="product-end-price">
                @if($goods_price_collect->price_promo > 0)
                    <div
                        class="goods-item-price-current" style="color: {{ $color_by_status['price_promo'] ?? '' }}">{{ $goods_price_collect->price_promo ?? '' }} {{ ShowLabelById(3) }}</div>
                    <div
                        class="goods-item-price-old">{{ $goods_price_collect->price_default ?? '' }} {{ ShowLabelById(3) }}</div>
                @else
                    <div
                        class="goods-item-price-current" style="color: {{ count($color_by_status) > 2 ? $color_by_status['default'] : end($color_by_status) }}">{{ $goods_price_collect->price ?? '' }} {{ ShowLabelById(3) }}</div>
                @endif
            </div>
            <div class="product-end-cta">
                <div class="product-quantity">
                    <button type="button" class="count-minus"
                            onclick="this.parentNode.querySelector('input[type=number]').value--">
                        <svg>
                            <use xlink:href="{{ asset('front-assets/svg/sprite.svg#minus') }}"></use>
                        </svg>
                    </button>
                    <input type="number" value="1" min="1" id="goods-id-{{ $goods_item->id ?? '' }}">
                    <button type="button" class="count-plus"
                            onclick="this.parentNode.querySelector('input[type=number]').value++">
                        <svg>
                            <use xlink:href="{{ asset('front-assets/svg/sprite.svg#plus') }}"></use>
                        </svg>
                    </button>
                </div>
                <a href="javascript:;" class="button button--black add-to-basket"
                   data-goods-item-id="{{ $goods_item->id ?? '' }}" data-show-notiflix="1">{{ ShowLabelById(5) }}</a>
            </div>
            <div class="quick-view-footer">
                <div class="product-end-favorite">
                    <a href="javascript:;" class="{{ $global_user ? ' add-to-wish' : ' open-login-modal' }}"
                       data-goods-item-id="{{ $goods_item->id ?? '' }}">
                        <svg>
                            <use xlink:href="{{ asset('front-assets/svg/sprite.svg#heart') }}"></use>
                            <use xlink:href="{{ asset('front-assets/svg/sprite.svg#heart-active') }}"></use>
                        </svg>
                    </a>
                    <p>{{ ShowLabelById(105) }}</p>
                </div>
                <div class="quick-view-link">
                    <a href="{{ route('catalog-product', ['product', $goods_item->alias]) }}">{{ ShowLabelById(104) }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
