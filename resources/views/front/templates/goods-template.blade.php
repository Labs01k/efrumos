@php
    $goods_price_collect = getGoodsPrice($one_goods);
    $color_by_status = goodsColorByStatus($one_goods, $goods_price_collect);

    $reviews_count = $one_goods->goodsItemReviews ? $one_goods->goodsItemReviews->count() : 0;
    //$goods_price = $goods_price_collect->promo_price > 0 ? $goods_price_collect->promo_price : $goods_price_collect->price;
@endphp
<div class="goods-item{{ $one_goods->in_stoc == 0 ? ' out-of-stock' : '' }}">
    <div class="goods-item-img">
        <a href="{{ route('catalog-product', ['product', $one_goods->alias]) }}"
           onclick='onProductClick("select_item", {!! \App\Services\GA4\GoogleEcommerce::oneGoodsCollectionToObjects($one_goods) !!})'>
            <img
                src="{{ $one_goods->oImage && $one_goods->oImage->img && file_exists('upfiles/goods-items/m/' . showImg($one_goods->oImage->img)) ? asset('upfiles/goods-items/m/'. showImg($one_goods->oImage->img)) : asset('front-assets/img/no-image-goods-m.png') }}"
                loading="lazy"
                alt="{{ $one_goods->itemByLang->name ?? '' }}">
        </a>
        <div class="goods-item-labels">
            @if(isset($color_by_status['price_promo']))
                {{--<p style="background-color: #E681A0;">Sale</p>--}}
                <p style="background-color: {{ $color_by_status['price_promo'] ?? '' }}">{{ ShowLabelById(179) }}</p>
                @if($goods_price_collect->price_promo > 0)
                    <p style="background: #FAE5EC; color: {{ $color_by_status['price_promo'] ?? '' }}">-{{ $goods_price_collect->promo_percent ?? '' }}
                        %</p>
                @endif
            @endif
            @if(isset($color_by_status['new_element']))
                <p style="background-color: {{ $color_by_status['new_element'] ?? '' }}">{{ ShowLabelById(180) }}</p>
            @endif
            @if(isset($color_by_status['popular_element']))
                <p style="background-color: {{ $color_by_status['popular_element'] ?? '' }}">{{ ShowLabelById(181) }}</p>
            @endif
        </div>
        @if($one_goods->goodsPromoTags->isNotEmpty())
            <div class="goods-item-labels goods-item-labels-bottom">
                @foreach($one_goods->goodsPromoTags as $one_promo_tag)
                    <p style="background-color: {{ $one_promo_tag->tag_color }};">{{ $one_promo_tag->tag_name }}</p>
                @endforeach
            </div>
        @endif
        <div class="goods-item-cta">
            <a href="javascript:;"
               class="{{ $global_user ? ' add-to-wish' : ' open-login-modal' }}{{ $global_user && $one_goods->checkIfWishItemExist ? ' active' : '' }}"
               data-goods-item-id="{{ $one_goods->id ?? '' }}">
                <svg>
                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#heart') }}"></use>
                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#heart-active') }}"></use>
                </svg>
            </a>
            <a href="javascript:;" class="open-quick-view goods-quick-view-modal"
               data-goods-item-id="{{ $one_goods->id ?? '' }}">
                <svg>
                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#eye') }}"></use>
                </svg>
            </a>
        </div>
    </div>
    <div class="goods-item-content">
        <div class="goods-item-brand">{!! $one_goods->getBrand->itemByLang->name ?? '&nbsp;' !!}</div>
        <h3>
            <a href="{{ route('catalog-product', ['product', $one_goods->alias]) }}"
               onclick='onProductClick("select_item", {!! \App\Services\GA4\GoogleEcommerce::oneGoodsCollectionToObjects($one_goods) !!})'>{{ $one_goods->itemByLang->name ?? '' }}</a>
        </h3>
        <div class="goods-item-grade">
            @php
                $stars = $reviews_count == 0 ? 0 : round($one_goods->rating);
                $no_stars = 5 - $stars;
            @endphp
            @include('front.templates.goods-rating')
            <p>({{ $reviews_count ?? '' }})</p>
        </div>
        <div class="goods-item-prices">
            @if($goods_price_collect->price_promo > 0)
                <div
                    class="goods-item-price-current"
                    style="color: {{ $color_by_status['price_promo'] ?? '' }}">{{ $goods_price_collect->price_promo ?? '' }} {{ ShowLabelById(3) }}</div>
                <div
                    class="goods-item-price-old">{{ $goods_price_collect->price_default ?? '' }} {{ ShowLabelById(3) }}</div>
            @else
                <div
                    class="goods-item-price-current"
                    style="color: {{ count($color_by_status) > 2 ? $color_by_status['default'] : end($color_by_status) }}">{{ $goods_price_collect->price ?? '' }} {{ ShowLabelById(3) }}</div>
            @endif
            {{--<div class="goods-item-price-current" style="color: #E681A0;">
                {{ $goods_price ?? '' }}
                MDL
            </div>
            <div class="goods-item-price-old">150 MDL</div>--}}
        </div>
        @if((isset($modal_add_to_basket) && $modal_add_to_basket == 1) || $one_goods->in_stoc == 0)
            <div class="goods-item-add">
                <a href="{{ route('catalog-product', ['product', $one_goods->alias]) }}"
                   onclick='onProductClick("select_item", {!! \App\Services\GA4\GoogleEcommerce::oneGoodsCollectionToObjects($one_goods) !!})'
                   class="button button--black">{{ ShowLabelById(4) }}</a>
            </div>
        @else
            <div class="goods-item-add">
                <a href="javascript:;" class="button button--black open-add-to-cart add-to-basket"
                   data-goods-item-id="{{ $one_goods->id ?? '' }}">{{ ShowLabelById(5) }}</a>
            </div>
        @endif
    </div>
</div>

