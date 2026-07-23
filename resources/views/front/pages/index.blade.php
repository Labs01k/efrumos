@extends('front.app',['attributes'=>'class=home-page'])
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('google-tag-manager')
    <script>
        //Promo goods
        dataLayer.push({ecommerce: null});
        dataLayer.push({
            event: "view_item_list",
            ecommerce: {
                items: {!! $goods_objects_promo ?? '' !!}
            }
        });

        //Bestseller goods
        dataLayer.push({ecommerce: null});
        dataLayer.push({
            event: "view_item_list",
            ecommerce: {
                items: {!! $goods_objects_bestseller ?? '' !!}
            }
        });

        //New goods
        dataLayer.push({ecommerce: null});
        dataLayer.push({
            event: "view_item_list",
            ecommerce: {
                items: {!! $goods_objects_new ?? '' !!}
            }
        });
    </script>
@stop

@section('container')

    <div class="page-content">

        @if($popular_categories && $popular_categories->children->isNotEmpty())
            <div class="tag-menu">
                <div class="container">
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            @foreach($popular_categories->children as $one_item)
                                <div class="swiper-slide">
                                    <a href="{{ $one_item->itemByLang->link ?? '' }}">{{ $one_item->itemByLang->name ?? '' }}</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="section home-hero">
            <div class="container">
                <div class="home-hero-inner">
                    @if($header_goods_subjects && $header_goods_subjects->children->isNotEmpty())
                        <div class="home-hero-catalog">
                            <div class="catalog-inner">
                                <div class="catalog-list">
                                    @include('front.templates.header-catalog-menu')
                                </div>
                            </div>
                        </div>
                    @endif
                    @if(!empty($slider) && count($slider))
                        <div class="home-hero-slider">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    @foreach($slider as $one_slide)
                                        @if($one_slide->itemByLang->link)
                                            <a href="{{ $one_slide->itemByLang->link ?? '' }}" class="swiper-slide">
                                                <picture>
                                                    @if(isMobile())
                                                        <source
                                                            srcset="{{ $one_slide->itemByLang->img_mobile && file_exists('upfiles/slider-mobile/' . $one_slide->itemByLang->img_mobile) ? asset('upfiles/slider-mobile/'. $one_slide->itemByLang->img_mobile) : asset('front-assets/img/no-image-slider.png') }}"
                                                            media="(max-width: 991px)">
                                                    @endif
                                                    <img
                                                        src="{{ $one_slide->itemByLang->img && file_exists('upfiles/slider/' . $one_slide->itemByLang->img) ? asset('upfiles/slider/'. $one_slide->itemByLang->img) : asset('front-assets/img/no-image-slider.png') }}"
                                                        alt="{{ $one_slide->itemByLang->name ?? '' }}">
                                                </picture>
                                            </a>
                                        @else
                                            <div class="swiper-slide">
                                                <picture>
                                                    @if(isMobile())
                                                        <source
                                                            srcset="{{ $one_slide->itemByLang->img_mobile && file_exists('upfiles/slider-mobile/' . $one_slide->itemByLang->img_mobile) ? asset('upfiles/slider-mobile/'. $one_slide->itemByLang->img_mobile) : asset('front-assets/img/no-image-slider.png') }}"
                                                            media="(max-width: 991px)">
                                                    @endif
                                                    <img
                                                        src="{{ $one_slide->itemByLang->img && file_exists('upfiles/slider/' . $one_slide->itemByLang->img) ? asset('upfiles/slider/'. $one_slide->itemByLang->img) : asset('front-assets/img/no-image-slider.png') }}"
                                                        alt="{{ $one_slide->itemByLang->name ?? '' }}">
                                                </picture>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <button type="button" class="home-hero-nav home-hero-nav--prev">
                                <svg>
                                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#slider-arrow') }}"></use>
                                </svg>
                            </button>
                            <button type="button" class="home-hero-nav home-hero-nav--next">
                                <svg>
                                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#slider-arrow') }}"></use>
                                </svg>
                            </button>
                            <div class="slider-pagination"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($two_banners_under_slider && $two_banners_under_slider->children->isNotEmpty())
            <div class="section banners banners--two-columns">
                <div class="container">
                    <div class="banners-list">
                        @foreach($two_banners_under_slider->children as $one_item)
                            @include('front.templates.banner-item', ['columns_count' => 2])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($popular_categories && $popular_categories->children->isNotEmpty())
            <div class="section categories">
                <div class="container">
                    @if($popular_categories->itemByLang->h1_title)
                        <div class="section-head">
                            <h2>{{ $popular_categories->itemByLang->h1_title ?? '' }}</h2>
                        </div>
                    @endif
                    <div class="categories-slider">
                        <div class="swiper-container">
                            <div class="swiper-wrapper">
                                @foreach($popular_categories->children as $one_item)
                                    <div class="swiper-slide">
                                        <a href="{{ $one_item->itemByLang->link ?? '' }}">
                                            <div class="categories-img">
                                                <img
                                                    src="{{ $one_item->oImage && $one_item->oImage->img && file_exists('upfiles/menu/s/' . showImg($one_item->oImage->img)) ? asset('upfiles/menu/s/'. showImg($one_item->oImage->img)) : asset('front-assets/img/no-image-wb-xs.png') }}"
                                                    alt="{{ $one_item->oImage->name ?? '' }}">
                                            </div>
                                            <h3>{{ $one_item->itemByLang->name ?? '' }}</h3>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="slider-scrollbar"></div>
                    </div>
                </div>
            </div>
        @endif

        @if($three_banners_under_catalog && $three_banners_under_catalog->children->isNotEmpty())
            <div class="section banners banners--three-columns">
                <div class="container">
                    <div class="banners-list">
                        @foreach($three_banners_under_catalog->children as $one_item)
                            @include('front.templates.banner-item', ['columns_count' => 3])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($main_page_banner_promo_goods && (!empty($promo_goods) && count($promo_goods)))
            <div class="section goods-slider">
                <div class="container">
                    <div class="section-head">
                        <h2>{{ $main_page_banner_promo_goods->itemByLang->short_descr ?? '' }}</h2>
                        @if($main_page_banner_promo_goods->itemByLang->link)
                            <div class="section-link">
                                <a href="{{ $main_page_banner_promo_goods->itemByLang->link ?? '' }}">
                                    <span>{{ $main_page_banner_promo_goods->itemByLang->link_name ?? '' }}</span>
                                    <svg>
                                        <use xlink:href="{{ asset('front-assets/svg/sprite.svg#arrow-right') }}"></use>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="goods-slider-inner">
                        <div class="goods-slider-banner">
                            <a{{ $main_page_banner_promo_goods->itemByLang->link ? ' href='.$main_page_banner_promo_goods->itemByLang->link : ''  }}>
                                <img
                                    src="{{ $main_page_banner_promo_goods->getImageByLang && $main_page_banner_promo_goods->getImageByLang->img && file_exists('upfiles/banners/xs/' . showImg($main_page_banner_promo_goods->getImageByLang->img)) ? asset('upfiles/banners/xs/'. showImg($main_page_banner_promo_goods->getImageByLang->img)) : asset('front-assets/img/no-image-menu.png') }}"
                                    alt="{{ $main_page_banner_promo_goods->itemByLang->short_descr ?? '' }}">
                            </a>
                        </div>
                        <div class="goods-slider-wrapper">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide goods-slider-banner">
                                        <a{{ $main_page_banner_promo_goods->itemByLang->link ? ' href='.$main_page_banner_promo_goods->itemByLang->link : ''  }}>
                                            <img
                                                src="{{ $main_page_banner_promo_goods->getImageByLang && $main_page_banner_promo_goods->getImageByLang->img && file_exists('upfiles/banners/xs/' . showImg($main_page_banner_promo_goods->getImageByLang->img)) ? asset('upfiles/banners/xs/'. showImg($main_page_banner_promo_goods->getImageByLang->img)) : asset('front-assets/img/no-image-menu.png') }}"
                                                alt="{{ $main_page_banner_promo_goods->itemByLang->short_descr ?? '' }}">
                                        </a>
                                    </div>
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

        @if($main_page_banner_bestseller_goods && (!empty($bestseller_goods) && count($bestseller_goods)))
            <div class="section goods-slider">
                <div class="container">
                    <div class="section-head">
                        <h2>{{ $main_page_banner_bestseller_goods->itemByLang->short_descr ?? '' }}</h2>
                        @if($main_page_banner_bestseller_goods->itemByLang->link)
                            <div class="section-link">
                                <a href="{{ $main_page_banner_bestseller_goods->itemByLang->link ?? '' }}">
                                    <span>{{ $main_page_banner_bestseller_goods->itemByLang->link_name ?? '' }}</span>
                                    <svg>
                                        <use xlink:href="{{ asset('front-assets/svg/sprite.svg#arrow-right') }}"></use>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="goods-slider-inner">
                        <div class="goods-slider-banner">
                            <a{{ $main_page_banner_bestseller_goods->itemByLang->link ? ' href='.$main_page_banner_bestseller_goods->itemByLang->link : ''  }}>
                                <img
                                    src="{{ $main_page_banner_bestseller_goods->getImageByLang && $main_page_banner_bestseller_goods->getImageByLang->img && file_exists('upfiles/banners/xs/' . showImg($main_page_banner_bestseller_goods->getImageByLang->img)) ? asset('upfiles/banners/xs/'. showImg($main_page_banner_bestseller_goods->getImageByLang->img)) : asset('front-assets/img/no-image-menu.png') }}"
                                    alt="{{ $main_page_banner_bestseller_goods->itemByLang->short_descr ?? '' }}">
                            </a>
                        </div>
                        <div class="goods-slider-wrapper">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide goods-slider-banner">
                                        <a{{ $main_page_banner_bestseller_goods->itemByLang->link ? ' href='.$main_page_banner_bestseller_goods->itemByLang->link : ''  }}>
                                            <img
                                                src="{{ $main_page_banner_bestseller_goods->getImageByLang && $main_page_banner_bestseller_goods->getImageByLang->img && file_exists('upfiles/banners/xs/' . showImg($main_page_banner_bestseller_goods->getImageByLang->img)) ? asset('upfiles/banners/xs/'. showImg($main_page_banner_bestseller_goods->getImageByLang->img)) : asset('front-assets/img/no-image-menu.png') }}"
                                                alt="{{ $main_page_banner_bestseller_goods->itemByLang->short_descr ?? '' }}">
                                        </a>
                                    </div>
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

        @if($two_banners_under_bestseller_goods && $two_banners_under_bestseller_goods->children->isNotEmpty())
            <div class="section banners banners--two-columns">
                <div class="container">
                    <div class="banners-list">
                        @foreach($two_banners_under_bestseller_goods->children as $one_item)
                            @include('front.templates.banner-item', ['columns_count' => 2])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($main_page_banner_new_goods && (!empty($bestseller_goods) && count($bestseller_goods)))
            <div class="section goods-slider">
                <div class="container">
                    <div class="section-head">
                        <h2>{{ $main_page_banner_new_goods->itemByLang->short_descr ?? '' }}</h2>
                        @if($main_page_banner_new_goods->itemByLang->link)
                            <div class="section-link">
                                <a href="{{ $main_page_banner_new_goods->itemByLang->link ?? '' }}">
                                    <span>{{ $main_page_banner_new_goods->itemByLang->link_name ?? '' }}</span>
                                    <svg>
                                        <use xlink:href="{{ asset('front-assets/svg/sprite.svg#arrow-right') }}"></use>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="goods-slider-inner">
                        <div class="goods-slider-banner">
                            <a{{ $main_page_banner_new_goods->itemByLang->link ? ' href='.$main_page_banner_new_goods->itemByLang->link : ''  }}>
                                <img
                                    src="{{ $main_page_banner_new_goods->getImageByLang && $main_page_banner_new_goods->getImageByLang->img && file_exists('upfiles/banners/xs/' . showImg($main_page_banner_new_goods->getImageByLang->img)) ? asset('upfiles/banners/xs/'. showImg($main_page_banner_new_goods->getImageByLang->img)) : asset('front-assets/img/no-image-menu.png') }}"
                                    alt="{{ $main_page_banner_new_goods->itemByLang->short_descr ?? '' }}">
                            </a>
                        </div>
                        <div class="goods-slider-wrapper">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide goods-slider-banner">
                                        <a{{ $main_page_banner_new_goods->itemByLang->link ? ' href='.$main_page_banner_new_goods->itemByLang->link : ''  }}>
                                            <img
                                                src="{{ $main_page_banner_new_goods->getImageByLang && $main_page_banner_new_goods->getImageByLang->img && file_exists('upfiles/banners/xs/' . showImg($main_page_banner_new_goods->getImageByLang->img)) ? asset('upfiles/banners/xs/'. showImg($main_page_banner_new_goods->getImageByLang->img)) : asset('front-assets/img/no-image-menu.png') }}"
                                                alt="{{ $main_page_banner_new_goods->itemByLang->short_descr ?? '' }}">
                                        </a>
                                    </div>
                                    @foreach($new_goods as $one_goods)
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

        @if($video_gallery && $video_gallery->galleryMediaVideo->isNotEmpty())
            <div class="section videos">
                <div class="container">
                    <div class="section-head">
                        <h2>{{ $video_gallery->itemByLang->h1_title ?: $video_gallery->itemByLang->name }}</h2>
                    </div>
                    <div class="slider-wrapper">
                        <div class="swiper-container">
                            <div class="swiper-wrapper">
                                @foreach($video_gallery->galleryMediaVideo as $one_gallery_item)
                                    <div class="swiper-slide">
                                        <a data-fancybox href="{{ $one_gallery_item->youtube_link ?? '' }}"
                                           class="videos-item">
                                            <img
                                                src="https://img.youtube.com/vi/{{ $one_gallery_item->youtube_id }}/maxresdefault.jpg"
                                                alt="{{ $one_gallery_item->itemByLang->name ?? '' }}">
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

        @if($three_banners_under_video && $three_banners_under_video->children->isNotEmpty())
            <div class="section banners banners--three-columns">
                <div class="container">
                    <div class="banners-list">
                        @foreach($three_banners_under_video->children as $one_item)
                            @include('front.templates.banner-item', ['columns_count' => 3, 'square_banner' => 1])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($news && $news->infoItems->isNotEmpty())
            <div class="section news">
                <div class="container">
                    <div class="section-head">
                        <h2>{{ $news->itemBylang->name ?? '' }}</h2>
                        <div class="section-link">
                            <a href="{{ route('news') }}">
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
                                @foreach($news->infoItems as $one_news_item)
                                    <div class="swiper-slide">
                                        @include('front.templates.news-item')
                                    </div>
                                @endforeach
                            </div>
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

        @if(!empty($brands) && count($brands))
            <div class="section brands">
                <div class="container">
                    <div class="section-head mb-1">
                        <h2>{{ ShowLabelById(159) }}</h2>
                    </div>
                </div>
                <div class="brands-slider">
                    <div class="container">
                        <div class="slider-wrapper">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    @foreach($brands as $one_brand)
                                        <div class="swiper-slide">
                                            <a href="{{ route('brands', $one_brand->alias) }}">
                                                <img
                                                    src="{{ $one_brand->oImage && $one_brand->oImage->img && file_exists('upfiles/brand/s/'. showImg($one_brand->oImage->img)) ? asset('upfiles/brand/s/'. showImg($one_brand->oImage->img)) : asset('front-assets/img/no-image-brand-s.png') }}"
                                                    alt="{{ $one_brand->oImage->name ?? '' }}">
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($three_banners_under_brands && $three_banners_under_brands->children->isNotEmpty())
            <div class="section banners banners--three-columns">
                <div class="container">
                    <div class="banners-list">
                        @foreach($three_banners_under_brands->children as $one_item)
                            @include('front.templates.banner-item', ['columns_count' => 3])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($about_shop)
            <div class="section about">
                <div class="container">
                    <h2>{{ $about_shop->itemByLang->name ?? '' }}</h2>
                    <div class="about-text common-text hidden-text">
                        {!! $about_shop->itemByLang->body ?? '' !!}
                    </div>
                    <div class="open-hidden-text">
                        <a href="javascript:;" data-more="{{ ShowLabelById(122) }}" data-less="{{ ShowLabelById(123) }}">
                            <span>{{ ShowLabelById(122) }}</span>
                            <svg>
                                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#arrow-right') }}"></use>
                            </svg>
                        </a>
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

@stop
