@php
    $goods_price_collect = getGoodsPrice($goods_item);
    //$goods_price = $goods_price_collect->promo_price > 0 ? $goods_price_collect->promo_price : $goods_price_collect->price;
@endphp
<div class="add-to-cart-head">
    <img src="{{ asset('front-assets/img/icons/add-to-cart.svg') }}" alt="Add to cart">
    <p>{{ ShowLabelById(6) }}</p>
</div>
<div class="add-to-cart-inner">
    <div class="add-to-cart-img">
        <img
            src="{{ $goods_item->oImage && $goods_item->oImage->img && file_exists('upfiles/goods-items/m/' . showImg($goods_item->oImage->img)) ? asset('upfiles/goods-items/m/'. showImg($goods_item->oImage->img)) : asset('front-assets/img/no-image-goods-m.png') }}"
            loading="lazy"
            alt="{{ $goods_item->itemByLang->name ?? '' }}">
    </div>
    <div class="add-to-cart-content">
        <div class="product-end-content">
            <h1>{{ $goods_item->itemByLang->name ?? '' }}</h1>
            <div class="add-to-cart-desc">
                {{--@if(($goods_item->getBrand &&  $goods_item->getBrand->itemByLang) && ($goods_item->getBrand->parent && $goods_item->getBrand->parent->itemByLang))--}}
                @if($goods_item->getBrand && $goods_item->getBrand->parent)
                    <p>{{ ShowLabelById(8) }}: <span><b><a href="{{ route('brands', $goods_item->getBrand->parent->alias) }}">{{ $goods_item->getBrand->parent->itemByLang->name ?? '' }}</a></b></span> / <span><a href="{{ route('brands', $goods_item->getBrand->alias) }}">{{ $goods_item->getBrand->itemByLang->name ?? '' }}</a></span></p>
                {{--@elseif($goods_item->getBrand && $goods_item->getBrand->itemByLang)--}}
                @elseif($goods_item->getBrand)
                    <p>{{ ShowLabelById(8) }}: <a href="{{ route('brands', $goods_item->getBrand->alias) }}">{{ $goods_item->getBrand->itemByLang->name ?? '' }}</a></span></p>
                @endif
                @if($goods_item->articol)
                    <p>{{ ShowLabelById(9) }}: {{ $goods_item->articol ?? '' }}</p>
                @endif
            </div>

            <div class="product-end-price">
                @if($goods_price_collect->price_promo > 0)
                    <div class="product-end-price-current">{{ $goods_price_collect->price_promo ?? '' }} {{ ShowLabelById(3) }}</div>
                    <div class="product-end-price-old">{{ $goods_price_collect->price_default ?? '' }} {{ ShowLabelById(3) }}</div>
                @else
                    <div class="product-end-price-current">{{ $goods_price_collect->price ?? '' }} {{ ShowLabelById(3) }}</div>
                @endif
            </div>
            <div class="add-to-cart-footer">
                <p>{{ ShowLabelById(7) }}: {{ $count_all_goods ?? '' }}</p>
            </div>
        </div>
    </div>
</div>
<div class="add-to-cart-cta">
    <div class="add-to-cart-link">
        <a href="javascript:;" class="close-common-modal">{{ ShowLabelById(10) }}</a>
    </div>
    <div class="add-to-cart-btn">
        <a href="{{ route('cart') }}" class="button button--black">{{ ShowLabelById(11) }}</a>
    </div>
</div>

@if(!empty($bestseller_goods) && count($bestseller_goods))
    <div class="add-to-cart-slider">
        <div class="section-head">
            <h2>{{ ShowLabelById(12) }}</h2>
        </div>
        <div class="add-to-cart-slider-inner">
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    @foreach($bestseller_goods as $one_goods)
                        <div class="swiper-slide">
                            @include('front.templates.goods-template', ['modal_add_to_basket' => 1])
                        </div>
                    @endforeach
                </div>
            </div>
            <button type="button" class="slider-nav slider-nav--prev">
                <svg>
                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#slider-arrow') }}"></use>
                </svg>
            </button>
            <button type="button" class="slider-nav slider-nav--next">
                <svg>
                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#slider-arrow') }}"></use>
                </svg>
            </button>
        </div>
    </div>
@endif

<script>
    var addToCart = new Swiper ('.add-to-cart-slider .swiper-container', {
        slidesPerView: 2,
        spaceBetween: 20,
        observer: 1,
        observeParents: 1,
        lazy: true,
        navigation: {
            prevEl: '.add-to-cart-slider .slider-nav--prev',
            nextEl: '.add-to-cart-slider .slider-nav--next',
        },
        breakpoints: {
            768: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
            992: {
                slidesPerView: 3,
                spaceBetween: 20,
            },
            1200: {
                slidesPerView: 4,
                spaceBetween: 20,
            },
        }
    });
</script>
