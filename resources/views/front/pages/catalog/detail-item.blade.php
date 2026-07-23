@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('google-tag-manager')
    <script>
        //dataLayer = [];
        dataLayer.push({ecommerce: null});
        dataLayer.push({
            event: "view_item",
            ecommerce: {
                items: [{!! $goods_object ?? '' !!}]
            }
        });

        //Similar goods
        dataLayer.push({ecommerce: null});
        dataLayer.push({
            event: "view_item_list",
            ecommerce: {
                items: {!! $goods_objects_similar ?? '' !!}
            }
        });

        //Related goods
        dataLayer.push({ecommerce: null});
        dataLayer.push({
            event: "view_item_list",
            ecommerce: {
                items: {!! $goods_objects_compatibile ?? '' !!}
            }
        });

        //Recommended goods
        dataLayer.push({ecommerce: null});
        dataLayer.push({
            event: "view_item_list",
            ecommerce: {
                items: {!! $goods_objects_bestseller ?? '' !!}
            }
        });

        //For FB Pixel
        fbq('track', 'ViewContent', {
            content_ids: ['{!! $goods_item->one_c_code ?? '' !!}'],
            content_name: '{!! $goods_item->itemByLang->name ?? '' !!}',
            content_type: 'product',
            content_category: '{!! $goods_item->itemByLang->subject_name ?? '' !!}',
            value: {!! priceFormatForGA4($goods_price_collect->price) !!},
            currency: 'MDL',
            status: '{!! $goods_item->active == 1 ? 'active' : 'archived'; !!}'
        });
    </script>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('goods-item', $goods_subject, $goods_item) }}
            </div>
        </div>

        <div class="section pt-0 product-end">
            <div class="container">
                <div class="product-end-inner">
                    <div class="product-end-sliders">
                        @if($goods_item->oImages->isNotEmpty())
                            <div class="product-end-thumbs">
                                <div class="swiper-container">
                                    <div class="swiper-wrapper">
                                        @foreach($goods_item->oImages as $one_image)
                                            <div class="swiper-slide">
                                                <img
                                                    src="{{ file_exists('upfiles/goods-items/s/' . showImg($one_image->img)) ? asset('upfiles/goods-items/s/'. showImg($one_image->img)) : asset('front-assets/img/no-image-xs.png') }}"
                                                    alt="{{ $one_image->itemByLang->name ?? '' }} - thumbs image {{ $loop->iteration }}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="product-end-navs">
                                    <button type="button" class="product-nav product-nav--prev">
                                        <svg>
                                            <use
                                                xlink:href="{{ asset('front-assets/svg/sprite.svg#slider-arrow') }}"></use>
                                        </svg>
                                    </button>
                                    <button type="button" class="product-nav product-nav--next">
                                        <svg>
                                            <use
                                                xlink:href="{{ asset('front-assets/svg/sprite.svg#slider-arrow') }}"></use>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="product-end-gallery">
                                <div class="swiper-container">
                                    <div class="swiper-wrapper">
                                        @foreach($goods_item->oImages as $one_image)
                                            <div class="swiper-slide">
                                                <a href="{{ file_exists('upfiles/goods-items/' . $one_image->img) ? asset('upfiles/goods-items/' . $one_image->img) : asset('front-assets/img/no-image-l.png') }}"
                                                   data-fancybox="quick-view-gallery">
                                                    <img
                                                        src="{{ file_exists('upfiles/goods-items/' . $one_image->img) ? asset('upfiles/goods-items/'. $one_image->img) : asset('front-assets/img/no-image-l.png') }}"
                                                        alt="{{ $one_image->itemByLang->name ?? '' }} - image {{ $loop->iteration }}">
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="product-end-content">
                        <div class="product-end-head">
                            <div class="product-end-labels">
                                @if(isset($color_by_status['price_promo']))
                                    <p style="background-color: {{ $color_by_status['price_promo'] ?? '' }}">{{ ShowLabelById(179) }}</p>
                                    @if($goods_price_collect->price_promo > 0)
                                        <p style="background: #FAE5EC; color: {{ $color_by_status['price_promo'] ?? '' }}">
                                            -{{ $goods_price_collect->promo_percent ?? '' }}%</p>
                                    @endif
                                @endif
                                @if(isset($color_by_status['new_element']))
                                    <p style="background-color: {{ $color_by_status['new_element'] ?? '' }}">{{ ShowLabelById(180) }}</p>
                                @endif
                                @if(isset($color_by_status['popular_element']))
                                    <p style="background-color: {{ $color_by_status['popular_element'] ?? '' }}">{{ ShowLabelById(181) }}</p>
                                @endif
                                @if($goods_item->goodsPromoTags->isNotEmpty())
                                    @foreach($goods_item->goodsPromoTags as $one_promo_tag)
                                        <p style="background-color: {{ $one_promo_tag->tag_color }}">{{ $one_promo_tag->tag_name }}</p>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="product-end-content-inner">
                            <div class="product-end-content-left">
                                <h1>{{ $goods_item->itemByLang->name ?? '' }}</h1>
                                <div class="product-end-info">
                                    <div class="product-info-row">
                                        @if($goods_item->articol)
                                            <div class="product-info-item">
                                                <p>{{ ShowLabelById(9) }}: <span>{{ $goods_item->articol ?? '' }}</span>
                                                </p>
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
                                            <p>{{ ShowLabelById(8) }}: <span><a
                                                        href="{{ route('brands', $goods_item->getBrand->parent->alias) }}"><b>{{ $goods_item->getBrand->parent->itemByLang->name ?? '' }}</b></a></span>
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
                            </div>
                            <div class="product-end-content-right">
                                <div class="product-end-stock">
                                    {{--<a href="javascript:;" class="product-stock-check open-stock-modal">--}}
                                    <div class="product-stock-check">
                                        @if($goods_item->products_count == 0 || $goods_item->in_stoc == 0)
                                            {{ ShowLabelById(20) }}
                                        @elseif($goods_item->products_count > 5)
                                            {{ ShowLabelById(19) }}
                                        @elseif($goods_item->products_count <= 5)
                                            {{ ShowLabelById(21) }}
                                        @endif
                                    </div>
                                    {{--</a>--}}
                                    <div class="product-stock-status">
                                        <ul>
                                            @if($goods_item->products_count == 0 || $goods_item->in_stoc == 0)
                                                <li style="color: #F94F4F; "></li>
                                                <li></li>
                                                <li></li>
                                            @elseif($goods_item->products_count > 5)
                                                <li style="color: #3FCB99; "></li>
                                                <li style="color: #3FCB99; "></li>
                                                <li style="color: #3FCB99; "></li>
                                            @elseif($goods_item->products_count <= 5)
                                                <li style="color: #F39200; "></li>
                                                <li style="color: #F39200; "></li>
                                                <li></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                <div class="product-info-right">
                                    <div class="goods-item-grade">
                                        @php
                                            $stars = $reviews_count == 0 ? 0 : round($goods_item->rating);
                                            $no_stars = 5 - $stars;
                                        @endphp
                                        @include('front.templates.goods-rating')
                                        <p>({{ $reviews_count ?? '' }})</p>
                                    </div>
                                    <p>
                                        <a href="#review-section" class="to-product-review"
                                           scroll-to-item>{{ ShowLabelById(182) }}</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="product-end-price">
                            @if($goods_price_collect->price_promo > 0)
                                <div
                                    class="product-end-price-current"
                                    style="color: {{ $color_by_status['price_promo'] ?? '' }}">{{ $goods_price_collect->price_promo ?? '' }} {{ ShowLabelById(3) }}</div>
                                <div
                                    class="product-end-price-old">{{ $goods_price_collect->price_default ?? '' }} {{ ShowLabelById(3) }}</div>
                            @else
                                <div
                                    class="product-end-price-current"
                                    style="color: {{ count($color_by_status) > 2 ? $color_by_status['default'] : end($color_by_status) }}">{{ $goods_price_collect->price ?? '' }} {{ ShowLabelById(3) }}</div>
                            @endif
                        </div>
                        @if($goods_item->in_stoc == 1)
                            <div class="product-end-cta">
                                <div class="product-quantity quantity-item-page">
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
                                <a href="javascript:;"
                                   class="button button--black open-add-to-cart product-end-add-to-basket"
                                   data-goods-item-id="{{ $goods_item->id ?? '' }}">{{ ShowLabelById(5) }}</a>
                            </div>
                            <div class="product-end-favorite">
                                <a href="javascript:;"
                                   class="{{ $global_user ? 'add-to-wish' : 'open-login-modal' }}{{ $global_user && $goods_item->checkIfWishItemExist ? ' active' : '' }}"
                                   data-goods-item-id="{{ $goods_item->id ?? '' }}">
                                    <svg>
                                        <use xlink:href="{{ asset('front-assets/svg/sprite.svg#heart') }}"></use>
                                        <use xlink:href="{{ asset('front-assets/svg/sprite.svg#heart-active') }}"></use>
                                    </svg>
                                </a>
                                <p>{{ ShowLabelById(105) }}</p>
                            </div>
                            <div class="product-end-link">
                                <a href="javascript:;"
                                   class="button button-black--inversed open-one-click">{{ ShowLabelById(252) }}</a>
                            </div>
                        @else
                            <div class="product-end-price">
                                <div class="product-end-price-current">{{ ShowLabelById(272) }}</div>
                            </div>
                        @endif

                        @if($brand_image_palette && file_exists('upfiles/goods-brand-palette/'. $brand_image_palette))
                            <div class="product-end-palette">
                                <a href="{{ asset('upfiles/goods-brand-palette/'. $brand_image_palette) }}"
                                   data-fancybox="">
                                    <span>{{ ShowLabelById(261) }}:</span>
                                    <img src="{{ asset('front-assets/img/icons/palette.svg') }}" alt="Efrumos Palette">
                                </a>
                            </div>
                        @endif

                        @if(showSettingBodyByAlias('text-delivery-descr'))
                            <div class="product-end-text">
                                {!! showSettingBodyByAlias('text-delivery-descr') !!}
                            </div>
                        @endif

                        @if(count($promo_list) > 0)
                            <div class="product-end-promo">
                                <div class="product-promo-title">
                                    <a href="{{ route('promo') }}">{{ ShowLabelById(192) }}</a>
                                </div>
                                @foreach($promo_list as $one_promo)
                                    @if(!empty($promo_info_list[$one_promo->id]))
                                        <div class="product-promo-row">
                                            <div class="product-promo-banner">
                                                <a href="{{ route('promo', $promo_info_list[$one_promo->id]->alias) }}">
                                                    <img
                                                        src="{{ $promo_info_list[$one_promo->id]->oImage && $promo_info_list[$one_promo->id]->oImage->img && file_exists('upfiles/info-items/m/'. showImg($promo_info_list[$one_promo->id]->oImage->img)) ? asset('upfiles/info-items/m/'. showImg($promo_info_list[$one_promo->id]->oImage->img)) : asset('front-assets/img/no-image-news.png') }}"
                                                        alt="{{ $promo_info_list[$one_promo->id]->itemByLang->name ?? '' }}">
                                                </a>
                                            </div>
                                            <div class="promo-timer">
                                                <div class="promo-timer-inner">
                                                    <div class="promo-timer-title">
                                                        <img src="{{ asset('front-assets/img/icons/clock.svg') }}"
                                                             alt="Promo clock">
                                                        <p>{{ ShowLabelById(193) }}:</p>
                                                    </div>
                                                    <div id="countdown" class="countdown"
                                                         data-time="{{date('Y-m-d', strtotime($one_promo->data_end) + 60*60*24) }}">
                                                        <div class="countdown-number">
                                                            <div class="days countdown-time"></div>
                                                            <div class="countdown-text">{{ ShowLabelById(194) }}</div>
                                                        </div>
                                                        <div class="countdown-divider">:</div>
                                                        <div class="countdown-number">
                                                            <div class="hours countdown-time"></div>
                                                            <div class="countdown-text">{{ ShowLabelById(195) }}</div>
                                                        </div>
                                                        <div class="countdown-divider">:</div>
                                                        <div class="countdown-number">
                                                            <div class="minutes countdown-time"></div>
                                                            <div class="countdown-text">{{ ShowLabelById(196) }}</div>
                                                        </div>
                                                        <div class="countdown-divider">:</div>
                                                        <div class="countdown-number">
                                                            <div class="seconds countdown-time"></div>
                                                            <div class="countdown-text">{{ ShowLabelById(197) }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- <div class="tag-menu d-block">
             <div class="container">
                 <div class="swiper-container">
                     <div class="swiper-wrapper">
                         <div class="swiper-slide">
                             <a href="#">Mască pentru păr</a>
                         </div>
                         <div class="swiper-slide">
                             <a href="#">Păr deteriorat</a>
                         </div>
                         <div class="swiper-slide">
                             <a href="#">Hidratarea părului</a>
                         </div>
                     </div>
                 </div>
             </div>
         </div>--}}

        @if($goods_item && $goods_item->goodsVideos->isNotEmpty())
            <div class="section videos pt-1">
                <div class="container">
                    <div class="slider-wrapper">
                        <div class="swiper-container">
                            <div class="swiper-wrapper">
                                @foreach($goods_item->goodsVideos as $one_goods_video)
                                    <div class="swiper-slide">
                                        <a data-fancybox href="{{ $one_goods_video->youtube_link ?? '' }}"
                                           class="videos-item">
                                            <img
                                                src="https://img.youtube.com/vi/{{ $one_goods_video->youtube_id }}/maxresdefault.jpg"
                                                alt="{{ $one_goods_video->itemByLang->name ?? '' }}">
                                        </a>
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
            </div>
        @endif

        <div class="section product-end-tabs">
            <div class="container">
                <div class="product-tabs">
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            @if($goods_item->itemByLang->body)
                                <div class="swiper-slide">
                                    <button type="button" class="product-tab"
                                            onclick="openTab(event, 'product-tabs-1')">
                                        {{ ShowLabelById(191) }}
                                    </button>
                                </div>
                            @endif
                            @if(!empty($goods_parameters) && count($goods_parameters))
                                <div class="swiper-slide">
                                    <button type="button" class="product-tab"
                                            onclick="openTab(event, 'product-tabs-2')">
                                        {{ ShowLabelById(190) }}
                                    </button>
                                </div>
                            @endif
                            @if($goods_item->itemByLang->body_two)
                                <div class="swiper-slide">
                                    <button type="button" class="product-tab"
                                            onclick="openTab(event, 'product-tabs-3')">{{ ShowLabelById(191) }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @if($goods_item->itemByLang->body)
                    <div id="product-tabs-1" class="product-tabs-content">
                        <div class="common-text">
                            {!! $goods_item->itemByLang->body ?? '' !!}
                        </div>
                    </div>
                @endif
                @if(!empty($goods_parameters) && count($goods_parameters))
                    <div id="product-tabs-2" class="product-tabs-content">
                        <div class="common-text">
                            @foreach($goods_parameters as $one_parameter)
                                <p><b>{{ $one_parameter['name'] ?? '' }}: </b>{{ $one_parameter['value'] ?? '' }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if($goods_item->itemByLang->body_two)
                    <div id="product-tabs-3" class="product-tabs-content">
                        <div class="common-text">
                            {!! $goods_item->itemByLang->body_two ?? '' !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if(!empty($similare_goods) && count($similare_goods))
            <div class="section goods-slider goods-slider--large">
                <div class="container">
                    <div class="section-head">
                        <h2>{{ ShowLabelById(15) }}</h2>
                    </div>
                    <div class="goods-slider-inner">
                        <div class="goods-slider-wrapper">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    @foreach($similare_goods as $one_goods)
                                        <div class="swiper-slide">
                                            @include('front.templates.goods-template')
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
                </div>
            </div>
        @endif

        @if(!empty($bestseller_goods) && count($bestseller_goods))
            <div class="section goods-slider goods-slider--large">
                <div class="container">
                    <div class="section-head">
                        <h2>{{ ShowLabelById(16) }}</h2>
                    </div>
                    <div class="goods-slider-inner">
                        <div class="goods-slider-wrapper">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    @foreach($bestseller_goods as $one_goods)
                                        <div class="swiper-slide">
                                            @include('front.templates.goods-template')
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
                </div>
            </div>
        @endif

        @if($three_banners_for_goods_page && $three_banners_for_goods_page->children->isNotEmpty())
            <div class="section banners banners--three-columns">
                <div class="container">
                    <div class="banners-list">
                        @foreach($three_banners_for_goods_page->children as $one_item)
                            @include('front.templates.banner-item', ['columns_count' => 3])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if(!empty($compatibile_goods) && count($compatibile_goods))
            <div class="section goods-slider goods-slider--large">
                <div class="container">
                    <div class="section-head">
                        <h2>{{ ShowLabelById(17) }}</h2>
                    </div>
                    <div class="goods-slider-inner">
                        <div class="goods-slider-wrapper">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    @foreach($compatibile_goods as $one_goods)
                                        <div class="swiper-slide">
                                            @include('front.templates.goods-template')
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
                </div>
            </div>
        @endif

        <div id="review-section" class="section product-end-review">
            <div class="container">
                <div class="product-review-head">
                    <div class="product-review-grade">
                        @php
                            $stars = $reviews_count == 0 ? 0 : round($goods_item->rating);
                            $no_stars = 5 - $stars;
                        @endphp
                        @include('front.templates.goods-rating')
                        <p>{{ $reviews_count }} {{ trans_choice('variables.goods_reviews', $reviews_count) }}</p>
                    </div>
                    <div class="product-review-btn">
                        <a href="javascript:;"
                           class="button button--black{{ $global_user ? ' open-review-modal' : ' open-login-modal' }}">{{ ShowLabelById(182) }}</a>
                    </div>
                </div>
                @if($goods_item->goodsItemReviews->isNotEmpty())
                    <div class="product-review-list">
                        @foreach($goods_item->goodsItemReviews as $one_goods_review)
                            @php
                                $stars = round($one_goods_review->rating);
                                $no_stars = 5 - $stars;
                            @endphp
                            <div class="product-review-item">
                                <div class="product-review-left">
                                    @if($one_goods_review->frontUserId)
                                        <div class="product-review-author">
                                            <div
                                                class="product-review-author-icon">{{ mb_substr($one_goods_review->frontUserId->name,0,1) }}</div>
                                            <div
                                                class="product-review-author-name">{{ $one_goods_review->frontUserId->name ?? '' }}</div>
                                        </div>
                                    @endif
                                    {{--<div class="product-review-confirmed">
                                        <img src="{{ asset('front-assets/img/icons/confirmed.svg') }}" alt="">
                                        <span>Comanda confirmată</span>
                                    </div>--}}
                                </div>
                                <div class="product-review-content">
                                    <div class="product-review-item-head">
                                        <div class="product-review-grade">
                                            @include('front.templates.goods-rating')
                                        </div>
                                        <div
                                            class="product-review-date">{{ Carbon\Carbon::parse($one_goods_review->created_at)->locale(LANG)->isoFormat('DD MMM YYYY') }}</div>
                                    </div>
                                    <div class="product-review-text common-text">
                                        <p>{{ $one_goods_review->review_text ?? '' }}</p>
                                    </div>
                                    {{--<div class="product-review-link">
                                        <a href="#">Răspundeți</a>
                                    </div>--}}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="product-review-empty">
                        <p>{{ ShowLabelById(183) }}</p>
                    </div>
                @endif
                {{--<div class="pagination">
                    <ul>
                        <li class="pagination-nav pagination-nav--prev pagination-nav--disabled">
                            <a href="#">
                                <svg>
                                    <use xlink:href="svg/sprite.svg#arrow-right"></use>
                                </svg>
                            </a>
                        </li>
                        <li class="active">
                            <a href="#">1</a>
                        </li>
                        <li>
                            <a href="#">2</a>
                        </li>
                        <li>
                            <a href="#">3</a>
                        </li>
                        <li>...</li>
                        <li>
                            <a href="#">7</a>
                        </li>
                        <li class="pagination-nav pagination-nav--next">
                            <a href="#">
                                <svg>
                                    <use xlink:href="svg/sprite.svg#arrow-right"></use>
                                </svg>
                            </a>
                        </li>
                    </ul>
                </div>--}}
            </div>
        </div>

        @if(!empty($view_goods) && count($view_goods))
            <div class="section goods-slider goods-slider--large">
                <div class="container">
                    <div class="section-head">
                        <h2>{{ ShowLabelById(18) }}</h2>
                    </div>
                    <div class="goods-slider-inner">
                        <div class="goods-slider-wrapper">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    @foreach($view_goods as $one_goods)
                                        <div class="swiper-slide">
                                            @include('front.templates.goods-template')
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
                </div>
            </div>
        @endif

        @if($blog && $blog->infoItems->isNotEmpty())
            <div class="section blog">
                <div class="container">
                    <div class="section-head">
                        <h2>{{ $blog->itemBylang->name ?? '' }}</h2>
                        <div class="section-link">
                            <a href="{{ route('blog') }}">
                                <span>{{ ShowLabelById(50) }}</span>
                                <svg>
                                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#arrow-right') }}"></use>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="slider-wrapper">
                        <div class="swiper-container">
                            <div class="swiper-wrapper">
                                @foreach($blog->infoItems as $one_blog_item)
                                    <div class="swiper-slide">
                                        @include('front.templates.blog-item')
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($advantages && $advantages->children->isNotEmpty())
            <div class="section benefits">
                <div class="container">
                    <div class="benefits-list">
                        @foreach($advantages->children as $one_advantage)
                            @include('front.templates.benefits-item')
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($global_user)
        <div class="common-modal review-modal">
            <div class="common-modal-bg"></div>
            <div class="common-modal-wrapper">
                <button type="button" class="common-modal-close">
                    <svg>
                        <use xlink:href="{{ asset('front-assets/svg/sprite.svg#close') }}"></use>
                    </svg>
                </button>
                <div class="common-modal-inner">
                    <div class="review-modal-title">{{ ShowLabelById(182) }}</div>

                    <form action="{{ route('ajax-new-review') }}"
                          id="goods-review" enctype="multipart/form-data">

                        @csrf
                        <input type="hidden" name="goods_item_id"
                               value="{{ $goods_item->id ?? '' }}">

                        <input type="hidden" name="current_url" value="{{ url()->current() }}">

                        <div class="review-modal-appreciate">
                            <div class="product-reviews-appreciate-wrapper position-relative">
                                <div class="product-reviews-appreciate-title">
                                    <p>{{ ShowLabelById(186) }}*:</p>
                                </div>
                                <div class="product-reviews-appreciate">
                                    <input type="radio" name="rating" id="star-5" value="5">
                                    <label for="star-5"></label>
                                    <input type="radio" name="rating" id="star-4" value="4">
                                    <label for="star-4"></label>
                                    <input type="radio" name="rating" id="star-3" value="3">
                                    <label for="star-3"></label>
                                    <input type="radio" name="rating" id="star-2" value="2">
                                    <label for="star-2"></label>
                                    <input type="radio" name="rating" id="star-1" value="1">
                                    <label for="star-1"></label>
                                </div>
                            </div>
                        </div>
                        <div class="review-modal-form">
                            {{--<div class="form-item">
                                <label for="id-1">Nume / Prenume*</label>
                                <input type="text" id="id-1" name="id-1">
                            </div>
                            <div class="form-item">
                                <label for="id-1">Email*</label>
                                <input type="text" id="id-1" name="id-1">
                            </div>--}}
                            <div class="form-item">
                                <label for="review-text">{{ ShowLabelById(187) }}*</label>
                                <textarea id="review-text" name="review_text"></textarea>
                            </div>
                            {{--<div class="form-item form-item-file">
                                <input class="input-file" type="file" name="files[]" id="file"
                                       accept="image/gif, image/jpeg" data-multiple-caption="{count} files selected"
                                       multiple="">
                                <label for="file" accept="image/gif, image/jpeg">
                                    <svg>
                                        <use xlink:href="svg/sprite.svg#upload"></use>
                                    </svg>
                                    <span>Adăugați o imagine</span>
                                </label>
                            </div>--}}

                            <div class="google-policies">
                                <p>{!! ShowLabelById(27) !!}</p>
                            </div>
                            <p class="aggreement mt-1">
                                <label>
                                    <input id="agree" name="agree" type="checkbox">
                                    <span class="aggreement-checkbox"></span>
                                    {!! ShowLabelById(28) !!}
                                </label>
                            </p>

                            <div class="captcha">
                                <input type="hidden" name="g-recaptcha-response" id="recaptcha-form-goods-review">
                            </div>

                            <div class="form-submit">
                                <button type="submit" class="button button--black prevent-repeated-click"
                                        onclick="saveForm(this)" data-form-id="goods-review">{{ ShowLabelById(188) }}
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @include('front.templates.fast-order')

    @if(isset($promo_list[0]['data_end']) && ($promo_list[0]['data_end'] > $curr_date))
        @push('other-scripts')
            <script>
                function getTimeRemaining(endtime) {
                    let t = Date.parse(endtime) - Date.parse(new Date());
                    let seconds = Math.floor((t / 1000) % 60);
                    let minutes = Math.floor((t / 1000 / 60) % 60);
                    let hours = Math.floor((t / (1000 * 60 * 60)) % 24);
                    let days = Math.floor(t / (1000 * 60 * 60 * 24));
                    return {
                        'total': t,
                        'days': days,
                        'hours': hours,
                        'minutes': minutes,
                        'seconds': seconds
                    };
                }

                function initializeClock(id, endtime) {
                    let clock = document.getElementById(id);
                    let daysSpan = clock.querySelector('.days');
                    let hoursSpan = clock.querySelector('.hours');
                    let minutesSpan = clock.querySelector('.minutes');
                    let secondsSpan = clock.querySelector('.seconds');

                    function updateClock() {
                        let t = getTimeRemaining(endtime);

                        daysSpan.innerHTML = t.days;
                        hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
                        minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
                        secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);

                        if (t.total <= 0) {
                            clearInterval(timeinterval);
                        }

                        wrapWords();
                    }

                    updateClock();
                    let timeinterval = setInterval(updateClock, 1000);

                    wrapWords();
                }

                function wrapWords() {
                    $('.countdown-time').each(function (index) {
                        let characters = $(this).text().split("");

                        $this = $(this);
                        $this.empty();
                        $.each(characters, function (i, el) {
                            $this.append("<span>" + el + "</span");
                        });

                    });
                }

                let deadline = '{{ $promo_list[0]['data_end'] ?? '' }}'; // for endless timer
                initializeClock('countdown', deadline);
            </script>
        @endpush
    @endif

@stop
