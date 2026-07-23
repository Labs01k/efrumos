@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('google-tag-manager')
    <script>
        dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
        dataLayer.push({
            event: "view_promotion",
            ecommerce: {
                items: {!! $goods_objects ?? '' !!}
            }
        });

        //Promo goods
        dataLayer.push({ecommerce: null});
        dataLayer.push({
            event: "view_item_list",
            ecommerce: {
                items: {!! $promo_goods_objects ?? '' !!}
            }
        });

    </script>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('promo-item', $promo_item) }}
            </div>
        </div>

        <div class="section pt-0 promo-page">
            <div class="container">
                <div class="promo-page-inner">

                    @if($promo_item->getImageByLang && $promo_item->getImageByLang->img && file_exists('upfiles/info-items/'. $promo_item->getImageByLang->img))
                        <div class="promo-page-end-img">
                            <img src="{{ asset('upfiles/info-items/'. $promo_item->oImage->img) }}"
                                 alt="{{ $promo_item->itemByLang->name ?? '' }}">
                        </div>
                    @endif

                    <h1 class="h2">{{ $promo_item->itemByLang->name ?? '' }}</h1>
                    <div class="promo-page-desc">
                        @if($promo_item->getGoodsPromoId && $promo_item->getGoodsPromoId->data_end)
                            <p>
                                <span>{{ $promo_item->getGoodsPromoId ? \Carbon\Carbon::parse($promo_item->getGoodsPromoId->data_end)->diffInDays() + 1 : '' }} {{ ShowLabelById(56) }}</span> {{ ShowLabelById(57) }}
                            </p>
                        @endif
                    </div>
                    <div class="promo-page-end-text common-text">
                        {!! $promo_item->itemByLang->body ?? '' !!}
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($promo_goods) && count($promo_goods))
            <div class="section goods-slider goods-slider--large">
                <div class="container">
                    <div class="section-head">
                        <h2>{{ ShowLabelById(59) }}</h2>
                    </div>
                    <div class="goods-slider-inner">
                        <div class="goods-slider-wrapper">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    @foreach($promo_goods as $one_goods)
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

        @if(!empty($view_goods) && count($view_goods))
            <div class="section goods-slider goods-slider--large">
                <div class="container">
                    <div class="section-head">
                        <h2>{{ ShowLabelById(58) }}</h2>
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

    </div>

@stop
