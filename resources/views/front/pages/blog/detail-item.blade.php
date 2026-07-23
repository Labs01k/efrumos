@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('google-tag-manager')
    <script>
        //Recommended goods
        dataLayer.push({ecommerce: null});
        dataLayer.push({
            event: "view_item_list",
            ecommerce: {
                items: {!! $goods_objects_bestseller ?? '' !!}
            }
        });
    </script>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('blog-item', $blog_item) }}
            </div>
        </div>

        <div class="section pt-0 news-end">
            <div class="container">
                <div class="section-head mb-1">
                    <h1 class="h2">{{ $blog_item->itemByLang->name ?? '' }}</h1>
                </div>
                <div class="news-end-head mb-3">
                    <div
                        class="news-end-date">{{ Carbon\Carbon::parse($blog_item->add_date)->locale(LANG)->isoFormat('DD MMM YYYY') }}</div>
                    @if($blog_item->itemByLang->author)
                        <div class="news-end-author">Autor: {{ $blog_item->itemByLang->author ?? '' }}</div>
                    @endif
                </div>
                @if($blog_item->oImage && $blog_item->oImage->img && file_exists('upfiles/info-items/'. $blog_item->oImage->img))
                    <div class="news-end-img mb-3">
                        <img src="{{ asset('upfiles/info-items/'. $blog_item->oImage->img) }}"
                             alt="{{ $blog_item->itemByLang->name ?? '' }}">
                    </div>
                @endif
            </div>

            <div class="news-end-text">
                <div class="container">
                    <div class="news-end-inner">
                        <div class="common-text">
                            {!! $blog_item->itemByLang->body ?? '' !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($related_products) && count($related_products))
            <div class="section goods-slider goods-slider--large">
                <div class="container">
                    <div class="section-head">
                        <h2>{{ ShowLabelById(278) }}</h2>
                    </div>
                    <div class="goods-slider-inner">
                        <div class="goods-slider-wrapper">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    @foreach($related_products as $one_goods)
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

        @if($similar_blog_list && $similar_blog_list->infoItems->isNotEmpty())
            <div class="section blog">
                <div class="container">
                    <div class="section-head">
                        <h2>{{ ShowLabelById(49) }}</h2>
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
                                @foreach($similar_blog_list->infoItems as $one_blog_item)
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

    </div>

@stop
