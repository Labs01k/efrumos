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

    <div class="page-content pb-page">

        {{--
            Мобильные панели по макету (фреймы 776:2974 и 785:12613): сверху название
            и избранное (без кнопки «назад» — решение заказчика), снизу кнопки покупки.
            Видны только на мобильных, верхняя появляется после прокрутки заголовка.
        --}}
        <div class="pb-bar pb-bar--top" data-pb-topbar>
            <span class="pb-bar-title">{{ $goods_item->itemByLang->name ?? '' }}</span>
            <a href="javascript:;"
               class="pb-bar-heart {{ $global_user ? 'add-to-wish' : 'open-login-modal' }}{{ $global_user && $goods_item->checkIfWishItemExist ? ' active' : '' }}"
               data-goods-item-id="{{ $goods_item->id ?? '' }}"
               aria-label="{{ ShowLabelById(105) }}">
                <svg>
                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#heart') }}"></use>
                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#heart-active') }}"></use>
                </svg>
            </a>
        </div>
        @if($goods_item->in_stoc == 1)
            <div class="pb-bar pb-bar--bottom">
                <a href="javascript:;" class="pb-button pb-button--ghost open-one-click">{{ ShowLabelById(252) }}</a>
                <a href="javascript:;" class="pb-button open-add-to-cart product-end-add-to-basket"
                   data-goods-item-id="{{ $goods_item->id ?? '' }}">{{ ShowLabelById(5) }}</a>
            </div>
        @endif

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('goods-item', $goods_subject, $goods_item) }}
            </div>
        </div>

        @php
        // Вкладки по макету: Описание / Состав / Применение / Характеристики.
        // «Состав» и «Применение» — отдельные параметры товара; если параметр
        // не заполнен или ещё не заведён в CMS, вкладка не показывается.
        $composition_id = config('custom.front.composition_parametr_id');
        $usage_id = config('custom.front.usage_parametr_id');

        $tab_composition = collect($goods_parameters)->firstWhere('id', $composition_id)['value'] ?? null;
        $tab_usage = $usage_id ? (collect($goods_parameters)->firstWhere('id', $usage_id)['value'] ?? null) : null;
        $tab_usage = $tab_usage ?: ($preview_usage ?? null);

        // в «Характеристиках» состав и применение не дублируем
        $tab_parameters = collect($goods_parameters)
        ->reject(fn ($one) => in_array($one['id'] ?? null, array_filter([$composition_id, $usage_id])))
        ->values();
        @endphp

        <div class="section pt-0 product-end">
            <div class="container">
                <div class="product-end-inner pb-product @if(empty($shops_stock) || !count($shops_stock)) pb-product--no-stock @endif">
                    <div class="product-end-sliders">
                        {{--
                            Галерея по макету: на десктопе столбик превью 80×80 со стрелками
                            и фото 440×440 (нода 786:15093), на ≤1024 — слайдер с точками
                            (нода 786:14393). Своя разметка, чтобы Swiper из main.js её не трогал.
                        --}}
                        <div class="pb-gallery @if($goods_item->oImages->count() <= 1) pb-gallery--single @endif" data-pb-gallery>
                            <div class="pb-gallery-thumbs">
                                <button type="button" class="pb-gallery-thumb-nav" data-pb-thumbs-prev
                                        aria-label="{{ trans('variables.product_slider_prev') }}">
                                    <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M4 10l4-4 4 4"/></svg>
                                </button>
                                <div class="pb-gallery-thumbs-list" data-pb-thumbs>
                                    @foreach($goods_item->oImages as $one_image)
                                        <button type="button" class="pb-gallery-thumb @if($loop->first) is-active @endif"
                                                data-pb-go="{{ $loop->index }}"
                                                aria-label="{{ $goods_item->itemByLang->name ?? '' }} — {{ $loop->iteration }}">
                                            <img src="{{ file_exists('upfiles/goods-items/s/' . showImg($one_image->img)) ? asset('upfiles/goods-items/s/'. showImg($one_image->img)) : asset('front-assets/img/no-image-xs.png') }}"
                                                 width="80" height="80" loading="lazy"
                                                 alt="{{ $one_image->itemByLang->name ?? '' }} - thumbs image {{ $loop->iteration }}">
                                        </button>
                                    @endforeach
                                </div>
                                <button type="button" class="pb-gallery-thumb-nav" data-pb-thumbs-next
                                        aria-label="{{ trans('variables.product_slider_next') }}">
                                    <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M4 6l4 4 4-4"/></svg>
                                </button>
                            </div>
                            <div class="pb-gallery-stage">
                                <div class="pb-gallery-track" data-pb-track>
                                    @forelse($goods_item->oImages as $one_image)
                                        <a class="pb-gallery-slide"
                                           href="{{ file_exists('upfiles/goods-items/' . $one_image->img) ? asset('upfiles/goods-items/' . $one_image->img) : asset('front-assets/img/no-image-l.png') }}"
                                           data-fancybox="pb-gallery">
                                            <img src="{{ file_exists('upfiles/goods-items/' . $one_image->img) ? asset('upfiles/goods-items/'. $one_image->img) : asset('front-assets/img/no-image-l.png') }}"
                                                 width="440" height="440" @if(!$loop->first) loading="lazy" @endif
                                                 alt="{{ $one_image->itemByLang->name ?? '' }} - image {{ $loop->iteration }}">
                                        </a>
                                    @empty
                                        <span class="pb-gallery-slide">
                                            <img src="{{ asset('front-assets/img/no-image-l.png') }}" width="440" height="440"
                                                 alt="{{ $goods_item->itemByLang->name ?? '' }}">
                                        </span>
                                    @endforelse
                                </div>
                                <button type="button" class="pb-gallery-arrow pb-gallery-arrow--prev" data-pb-prev
                                        aria-label="{{ trans('variables.product_slider_prev') }}">
                                    <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M13 8H3M6.5 3.5 3 8l3.5 4.5"/></svg>
                                </button>
                                <button type="button" class="pb-gallery-arrow pb-gallery-arrow--next" data-pb-next
                                        aria-label="{{ trans('variables.product_slider_next') }}">
                                    <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h10M9.5 3.5 13 8l-3.5 4.5"/></svg>
                                </button>
                                <div class="pb-gallery-dots" data-pb-dots aria-hidden="true"></div>
                            </div>
                        </div>
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
                        {{--
                            Правая колонка по макету (нода 786:15112): только заголовок,
                            объём, оттенок, цена, кнопки. Артикул, код, бренд и тип переехали
                            в «Характеристики» под описанием, индикатор остатка и рейтинг
                            в макете отсутствуют.
                        --}}
                        <div class="product-end-content-inner">
                            <h1>{{ $goods_item->itemByLang->name ?? '' }}</h1>
                        </div>
                        {{-- Селекторы вариантов товара по макету: объём и (для красок) оттенок --}}
                        @include('front.templates.product.volume-select')
                        @include('front.templates.product.shade-select')

                        <div class="product-end-price">
                            <span class="pb-field-label">{{ trans('variables.product_price') }}</span>
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
                            {{-- по макету кнопка корзины и иконка избранного идут в один ряд --}}
                            <div class="pb-cta-row">
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

                        {{-- старая картинка-палитра прячется, когда есть новый селектор оттенка --}}
                        @if(empty($shades) || !count($shades))
                        @if($brand_image_palette && file_exists('upfiles/goods-brand-palette/'. $brand_image_palette))
                            <div class="product-end-palette">
                                <a href="{{ asset('upfiles/goods-brand-palette/'. $brand_image_palette) }}"
                                   data-fancybox="">
                                    <span>{{ ShowLabelById(261) }}:</span>
                                    <img src="{{ asset('front-assets/img/icons/palette.svg') }}" alt="Efrumos Palette">
                                </a>
                            </div>
                        @endif
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

                    <div class="pb-product-stock">
                        {{-- п.5 ТЗ — наличие по магазинам. Скрыт, пока 1С не отдаёт остатки по складам --}}
                        @if(!empty($shops_stock) && count($shops_stock))
                        <div class="section pt-0">
                        <div class="container">
                        @include('front.templates.product.shop-stock')
                        </div>
                        </div>
                        @endif
                    </div>

                    {{--
                        Блок описания по макету (нода 786:15247): вкладки Описание / Состав /
                        Применение, ниже всегда — «Доставка», характеристики и ряд преимуществ.
                        Механика вкладок штатная (openTab из main.js + автоклик первой).
                    --}}
                    <div class="pb-product-info">
                        <div class="section product-end-tabs pb-info">
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
                                            @if($tab_composition)
                                                <div class="swiper-slide">
                                                    <button type="button" class="product-tab"
                                                            onclick="openTab(event, 'product-tabs-composition')">
                                                        {{ trans('variables.product_tab_composition') }}
                                                    </button>
                                                </div>
                                            @endif
                                            @if($tab_usage)
                                                <div class="swiper-slide">
                                                    <button type="button" class="product-tab"
                                                            onclick="openTab(event, 'product-tabs-usage')">
                                                        {{ trans('variables.product_tab_usage') }}
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
                                @if($tab_composition)
                                    <div id="product-tabs-composition" class="product-tabs-content">
                                        <div class="common-text">
                                            <p>{{ $tab_composition }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if($tab_usage)
                                    <div id="product-tabs-usage" class="product-tabs-content">
                                        <div class="common-text">
                                            <p>{{ $tab_usage }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if(showSettingBodyByAlias('text-delivery-descr'))
                                    <div class="pb-info-delivery">
                                        <span class="pb-field-label">{{ trans('variables.product_delivery') }}</span>
                                        <div class="pb-info-delivery-text">
                                            {!! showSettingBodyByAlias('text-delivery-descr') !!}
                                        </div>
                                    </div>
                                @endif

                                <dl class="pb-specs">
                                    @if($goods_item->articol)
                                        <dt>{{ ShowLabelById(9) }}:</dt>
                                        <dd>{{ $goods_item->articol }}</dd>
                                    @endif
                                    @if($goods_item->one_c_code)
                                        <dt>{{ ShowLabelById(13) }}:</dt>
                                        <dd>{{ $goods_item->one_c_code }}</dd>
                                    @endif
                                    @if($goods_item->getBrand)
                                        <dt>{{ ShowLabelById(8) }}:</dt>
                                        <dd>
                                            @if($goods_item->getBrand->parent)
                                                <a href="{{ route('brands', $goods_item->getBrand->parent->alias) }}">{{ $goods_item->getBrand->parent->itemByLang->name ?? '' }}</a> /
                                            @endif
                                            <a href="{{ route('brands', $goods_item->getBrand->alias) }}">{{ $goods_item->getBrand->itemByLang->name ?? '' }}</a>
                                        </dd>
                                    @endif
                                    @if($goods_item->getType && $goods_item->getType->itemByLang)
                                        <dt>{{ ShowLabelById(14) }}:</dt>
                                        <dd>{{ $goods_item->getType->itemByLang->name ?? '' }}</dd>
                                    @endif
                                    @if($goods_item->gramaj)
                                        <dt>{{ trans('variables.product_volume') }}:</dt>
                                        <dd>{{ $goods_item->gramaj }}</dd>
                                    @endif
                                    @foreach($tab_parameters as $one_parameter)
                                        <dt>{{ $one_parameter['name'] ?? '' }}:</dt>
                                        <dd>{{ $one_parameter['value'] ?? '' }}</dd>
                                    @endforeach
                                </dl>

                                <div class="pb-benefits">
                                    <div class="pb-benefit">
                                        <span class="pb-benefit-icon">
                                            <svg viewBox="0 0 16 16" aria-hidden="true">
                                                <path d="M1.5 4h8v7h-8zM9.5 6.5h2.7l1.8 2.3V11H9.5z"/>
                                                <circle cx="4.6" cy="12.4" r="1.3"/>
                                                <circle cx="11.4" cy="12.4" r="1.3"/>
                                            </svg>
                                        </span>
                                        <span>{{ trans('variables.product_usp_delivery') }}</span>
                                    </div>
                                    <div class="pb-benefit">
                                        <span class="pb-benefit-icon">
                                            <svg viewBox="0 0 16 16" aria-hidden="true">
                                                <circle cx="8" cy="8" r="6.2"/>
                                                <path d="M5.5 8l1.8 1.8 3.2-3.5"/>
                                            </svg>
                                        </span>
                                        <span>{{ trans('variables.product_usp_quality') }}</span>
                                    </div>
                                    <div class="pb-benefit">
                                        <span class="pb-benefit-icon">
                                            <svg viewBox="0 0 16 16" aria-hidden="true">
                                                <path d="M14 7.7A6 6 0 1 1 8 1.8a6 6 0 0 1 6 5.9zM8 14l-3.4.9.9-3.2"/>
                                                <path d="M6.3 6.4A1.8 1.8 0 1 1 8 8.4v.8"/>
                                                <path d="M8 11.1v.1"/>
                                            </svg>
                                        </span>
                                        <span>{{ trans('variables.product_usp_support') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{--
                        Отзывы по макету (нода 786:15392): правая колонка между описанием
                        и «С этим товаром покупают». Показаны все отзывы — они выводятся
                        прямо на странице, отдельного окна для них нет.
                    --}}
                    <div class="pb-product-reviews" id="review-section">
                        <h2 class="rec-title">{{ trans('variables.product_reviews_title') }}</h2>
                        <div class="pb-reviews-summary">
                            <div class="pb-reviews-metric">
                                <span class="pb-field-label">{{ trans('variables.product_rating_label') }}</span>
                                <span class="pb-reviews-score">
                                    {{ $reviews_count ? rtrim(rtrim(number_format($goods_item->rating, 1), '0'), '.') : 0 }}
                                    @include('front.templates.product.stars', ['filled' => $reviews_count ? round($goods_item->rating) : 0])
                                </span>
                            </div>
                            <div class="pb-reviews-metric">
                                <span class="pb-field-label">{{ trans('variables.product_reviews_label') }}</span>
                                <span class="pb-reviews-score">{{ $reviews_count }} {{ trans_choice('variables.goods_reviews', $reviews_count) }}</span>
                            </div>
                            <a href="javascript:;"
                               class="pb-button pb-reviews-write{{ $global_user ? ' open-review-modal' : ' open-login-modal' }}">{{ ShowLabelById(182) }}</a>
                        </div>
                        @if($goods_item->goodsItemReviews->isNotEmpty())
                            <div class="pb-reviews-list">
                                @foreach($goods_item->goodsItemReviews as $one_goods_review)
                                    <div class="pb-review">
                                        <div class="pb-review-author">
                                            <span>{{ $one_goods_review->frontUserId->name ?? '' }}</span>
                                            @include('front.templates.product.stars', ['filled' => round($one_goods_review->rating)])
                                        </div>
                                        <div class="pb-review-date">{{ Carbon\Carbon::parse($one_goods_review->created_at)->locale(LANG)->isoFormat('DD MMM YYYY') }}</div>
                                        <div class="pb-review-text">
                                            <p>{{ $one_goods_review->review_text ?? '' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="pb-reviews-empty">
                                <p>{{ ShowLabelById(183) }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- п.3 ТЗ — «С этим товаром покупают»: правая колонка, под описанием --}}
                    <div class="pb-product-set">
                        @include('front.templates.product.set-block')
                    </div>

                    {{-- п.4 ТЗ — «Похожие товары»: сразу следом, на всю ширину --}}
                    <div class="pb-product-similar">
                        @include('front.templates.product.similar-block')
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

        @if(config('custom.front.show_extra_blocks_on_product_page') && $goods_item && $goods_item->goodsVideos->isNotEmpty())
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




        {{-- В новом макете этого блока нет. Скрыт флагом, разметка сохранена:
             вернуть — custom.front.show_bestsellers_on_product_page = true --}}
        @if(config('custom.front.show_bestsellers_on_product_page') && !empty($bestseller_goods) && count($bestseller_goods))
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

        @if(config('custom.front.show_extra_blocks_on_product_page') && $three_banners_for_goods_page && $three_banners_for_goods_page->children->isNotEmpty())
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


        @if(config('custom.front.show_extra_blocks_on_product_page') && !empty($view_goods) && count($view_goods))
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

        @if(config('custom.front.show_extra_blocks_on_product_page') && $advantages && $advantages->children->isNotEmpty())
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
