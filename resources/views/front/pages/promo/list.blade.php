@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('promo-list') }}
            </div>
        </div>

        <div class="promo-page">
            <div class="container">
                <div class="section-head">
                    <h1 class="h2">{{ $menu_id && $menu_id->itemByLang ? $menu_id->itemByLang->name : '' }}</h1>
                </div>
                @if(!empty($promo_list) && count($promo_list))
                    <div class="promo-page-list">
                        @foreach($promo_list as $one_promo_item)
                            <div class="promo-page-item">
                                <div class="promo-page-img">
                                    <a href="{{ route('promo', $one_promo_item->alias) }}" class="ga4-promo-click"
                                       data-promo-id="{{ $one_promo_item->id ?? '' }}">
                                        <img
                                            src="{{ $one_promo_item->getImageByLang && $one_promo_item->getImageByLang->img && file_exists('upfiles/info-items/'. $one_promo_item->getImageByLang->img) ? asset('upfiles/info-items/'. $one_promo_item->oImage->img) : asset('front-assets/img/no-image-promo.png') }}"
                                            alt="{{ $one_promo_item->getImageByLang->name ?? '' }}">
                                    </a>
                                </div>
                                <div class="promo-page-content">
                                    <h2>
                                        <a href="{{ route('promo', $one_promo_item->alias) }}" class="ga4-promo-click"
                                           data-promo-id="{{ $one_promo_item->id ?? '' }}">{{ $one_promo_item->itemByLang->name ?? '' }}</a>
                                    </h2>

                                    <div class="promo-page-desc">
                                        <p>
                                            @if($one_promo_item->getGoodsPromoId && $one_promo_item->getGoodsPromoId->data_end)
                                                <span>{{ $one_promo_item->getGoodsPromoId ? \Carbon\Carbon::parse($one_promo_item->getGoodsPromoId->data_end)->diffInDays() + 1 : '' }} {{ ShowLabelById(56) }}</span> {{ ShowLabelById(57) }}
                                            @endif
                                        </p>
                                    </div>

                                    <div class="promo-page-link">
                                        <a href="{{ route('promo', $one_promo_item->alias) }}"
                                           class="button button--black ga4-promo-click"
                                           data-promo-id="{{ $one_promo_item->id ?? '' }}">{{ ShowLabelById(55) }}</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @if(!empty($goods_subject_l1) && count($goods_subject_l1))
            @foreach($goods_subject_l1 as $one_goods_subject_l1)
                @if($one_goods_subject_l1->promoGoodsItems->isNotEmpty())
                    <div class="section goods-slider goods-slider--large">
                        <div class="container">
                            <div class="section-head">
                                <h2>{{ $one_goods_subject_l1->itembyLang->name ?? '' }}</h2>
                                <div class="section-link">
                                    <a href="{{ route('category', $one_goods_subject_l1->alias) }}">
                                        <span>{{ ShowLabelById(50) }}</span>
                                        <svg>
                                            <use
                                                xlink:href="{{ asset('front-assets/svg/sprite.svg#arrow-right') }}"></use>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <div class="goods-slider-inner">
                                <div class="goods-slider-wrapper">
                                    <div class="swiper-container">
                                        <div class="swiper-wrapper">
                                            @foreach($one_goods_subject_l1->promoGoodsItems as $one_goods)
                                                <div class="swiper-slide">
                                                    @include('front.templates.goods-template')
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <button type="button" class="slider-nav slider-nav--prev">
                                        <svg>
                                            <use
                                                xlink:href="{{ asset('front-assets/svg/sprite.svg#slider-arrow') }}"></use>
                                        </svg>
                                    </button>
                                    <button type="button" class="slider-nav slider-nav--next">
                                        <svg>
                                            <use
                                                xlink:href="{{ asset('front-assets/svg/sprite.svg#slider-arrow') }}"></use>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
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

    </div>

@stop
