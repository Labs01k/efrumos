@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('google-tag-manager')
    <script>
        dataLayer.push({ecommerce: null});  // Clear the previous ecommerce object.
        dataLayer.push({
            event: "view_item_list",
            ecommerce: {
                items: {!! $goods_objects ?? '' !!}
            }
        });
    </script>

    @if(request()->input('s'))
        <script>
            fbq(
                'track', 'Search', {
                    search_string: '{{ request()->input('s') }}',
                    content_category: 'Product Search',
                    content_ids: {{ $goods_search_ids_array ?? '' }},
                }
            );
        </script>
    @endif
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                @if(Request::segment(2) == 'catalog' && !Request::segment(3))
                    {{ Breadcrumbs::render('catalog-page') }}
                @else
                    {{ Breadcrumbs::render('goods-subject', $goods_subject) }}
                @endif
            </div>
        </div>

        <div class="section pt-0 catalog-page">
            <div class="container">
                <div class="catalog-page-inner">
                    <div class="catalog-filters">
                        <div class="filters-head">
                            <div class="filters-title">{{ ShowLabelById(117) }}</div>
                            <div class="filters-remove">
                                <a href="{{ url()->current() }}">
                                    <img src="{{ asset('front-assets/img/icons/delete.svg') }}" alt="Delete">
                                    <span>{{ ShowLabelById(124) }}</span>
                                </a>
                            </div>
                            <button type='button' class="filters-close">
                                <svg>
                                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#close') }}"></use>
                                </svg>
                            </button>
                        </div>
                        <form action="{{ route('catalog-filter') }}" data-form-id="filter-data"
                              method="post" data-parent="{{ $goods_subject->alias ?? '' }}" id="filter-data">
                            @csrf

                            <div class="filters-list">
                                <div class="filters-item">
                                    <button type="button" class="filters-item-head">{{ ShowLabelById(119) }}</button>
                                    <div class="filters-item-inner">
                                        <div class="filters-price">
                                            <div class="price-slider-container">
                                                <div class="filter-price-slider" id="priceSlider"></div>
                                            </div>
                                            <div class="filter-price-inpust">
                                                <input id="minPrice" type="text" name="min_price"
                                                       value="{{ $min_price ?? 0 }}">
                                                <div class="filter-price-line"></div>
                                                <input id="maxPrice" type="text" name="max_price"
                                                       value="{{ $max_price ?? $get_max_price }}"
                                                       data-max-val="{{ $get_max_price }}">
                                                <div class="filter-price-currency">
                                                    <p>{{ ShowLabelById(3) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if(!empty($goods_subject_list) && count($goods_subject_list))
                                    <div
                                        class="filters-item">
                                        <button type="button"
                                                class="filters-item-head">{{ ShowLabelById(121) }}</button>
                                        <div
                                            class="filters-item-inner">
                                            <div class="filters-search">
                                                <input type="text" class="filters-search-input"
                                                       placeholder="{{ ShowLabelById(109) }} ...">
                                            </div>
                                            <div class="filters-item-list goods-subject-ids">
                                                <ul>
                                                    @foreach($goods_subject_list as $one_goods_subject)
                                                        <li>
                                                            <input type="checkbox"
                                                                   name="subject[]"
                                                                   id="id-{{ $one_goods_subject->id ?? '' }}"
                                                                   value="{{ $one_goods_subject->id ?? '' }}" data-parameter-id="{{ $goods_subject->id ?? '' }}" class="click-param" {{ !empty($filters_elements) && isset($filters_elements['subject']) && in_array($one_goods_subject->id, $filters_elements['subject']) ? 'checked' : '' }} {{ $goods_subject_ids && in_array($one_goods_subject->id, $goods_subject_ids) || (isset($filters_elements['subject']) && in_array($one_goods_subject->id, $filters_elements['subject'])) ? '' : 'disabled' }}>
                                                            <label
                                                                for="id-{{ $one_goods_subject->id ?? '' }}">
                                                                {{ $one_goods_subject->itemByLang->name ?? '' }}
                                                            </label>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($goods_brands_l1) && count($goods_brands_l1))
                                    <div class="filters-item">
                                        <button type="button" class="filters-item-head">{{ ShowLabelById(8) }}</button>
                                        <div class="filters-item-inner">
                                            <div class="filters-search">
                                                <input type="text" class="filters-search-input"
                                                       placeholder="{{ ShowLabelById(109) }} ...">
                                            </div>
                                            <div class="filters-item-list filters-item-submenu">
                                                <ul>
                                                    @foreach($goods_brands_l1 as $one_brand_l1)
                                                        @if($one_brand_l1->childrenSortByName->isNotEmpty())
                                                            <li class="custom-check-row parent-brands">
                                                                <div class="filters-submenu-head">
                                                                    <input onchange="checkAllBox(this)" type="checkbox"
                                                                           name="brand[]"
                                                                           value="{{ $one_brand_l1->id ?? '' }}"
                                                                           id="brand-{{ $one_brand_l1->id ?? '' }}-l1" data-parameter-id="brands" class="click-param" {{ !empty($filters_elements) && isset($filters_elements['brand']) && in_array($one_brand_l1->id,$filters_elements['brand']) ? 'checked' : '' }} {{ $goods_brand_ids && in_array($one_brand_l1->id, $goods_brand_ids) || (isset($filters_elements['brand']) && in_array($one_brand_l1->id, $filters_elements['brand'])) ? '' : 'disabled' }}>
                                                                    <label
                                                                        for="brand-{{ $one_brand_l1->id ?? '' }}-l1">{{ $one_brand_l1->itemByLang->name ?? '' }}</label>
                                                                    <button type="button" class="filters-submenu-btn">
                                                                        {{ $one_brand_l1->itemByLang->name ?? '' }}
                                                                    </button>
                                                                </div>
                                                                <div class="filters-item-list filters-sub-item-list">
                                                                    <ul>
                                                                        @foreach($one_brand_l1->childrenSortByName as $one_brand_l2)
                                                                            <li>
                                                                                <input type="checkbox" name="brand[]"
                                                                                       value="{{ $one_brand_l2->id }}"
                                                                                       id="brand-{{ $one_brand_l2->id ?? '' }}-l2" data-parameter-id="brands"
                                                                                       {{ !empty($filters_elements) && isset($filters_elements['brand']) && in_array($one_brand_l2->id,$filters_elements['brand']) ? 'checked' : '' }} {{ $goods_brand_ids && in_array($one_brand_l2->id, $goods_brand_ids) || (isset($filters_elements['brand']) && in_array($one_brand_l2->id, $filters_elements['brand'])) ? '' : 'disabled' }} class="check-sub-brand click-param">
                                                                                <label
                                                                                    for="brand-{{ $one_brand_l2->id ?? '' }}-l2">{{ $one_brand_l2->itemByLang->name ?? '' }}</label>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            </li>
                                                        @else
                                                            <li>
                                                                <div class="filters-submenu-head">
                                                                    <input type="checkbox" name="brand[]"
                                                                           value="{{ $one_brand_l1->id ?? '' }}"
                                                                           id="brand-{{ $one_brand_l1->id ?? '' }}-l1" data-parameter-id="brands" class="click-param"
                                                                        {{ !empty($filters_elements) && !empty(isset($filters_elements['brand'])) && in_array($one_brand_l1->id,$filters_elements['brand']) ? 'checked' : '' }} {{ $goods_brand_ids && in_array($one_brand_l1->id, $goods_brand_ids) || (isset($filters_elements['brand']) && in_array($one_brand_l1->id, $filters_elements['brand'])) ? '' : 'disabled' }}>
                                                                    <label
                                                                        for="brand-{{ $one_brand_l1->id ?? '' }}-l1">{{ $one_brand_l1->itemByLang->name ?? '' }}</label>
                                                                </div>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($goods_types) && count($goods_types))
                                    <div
                                        class="filters-item">
                                        <button type="button"
                                                class="filters-item-head">{{ ShowLabelById(14) }}</button>
                                        <div
                                            class="filters-item-inner">
                                            <div class="filters-search">
                                                <input type="text" class="filters-search-input"
                                                       placeholder="{{ ShowLabelById(109) }} ...">
                                            </div>
                                            <div class="filters-item-list goods-type-ids">
                                                <ul>
                                                    @foreach($goods_types as $one_goods_type)
                                                        <li>
                                                            <input type="checkbox"
                                                                   name="goods_type[]"
                                                                   id="id-{{ $one_goods_type->id ?? '' }}"
                                                                   value="{{ $one_goods_type->id ?? '' }}" data-parameter-id="goods-type" class="click-param" {{ !empty($filters_elements) && isset($filters_elements['goods_type']) && in_array($one_goods_type->id, $filters_elements['goods_type']) ? 'checked' : '' }} {{ $goods_type_ids && in_array($one_goods_type->id, $goods_type_ids) || (isset($filters_elements['goods_type']) && in_array($one_goods_type->id, $filters_elements['goods_type'])) ? '' : 'disabled' }}>
                                                            <label
                                                                for="id-{{ $one_goods_type->id ?? '' }}">
                                                                {{ $one_goods_type->itemByLang->name ?? '' }}
                                                            </label>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($parameters) && count($parameters))
                                    @foreach($parameters as $one_parameter)
                                        @if(!empty($parameter_values[$one_parameter->id]) && count($parameter_values[$one_parameter->id]))
                                            <div class="filters-item{{ $one_parameter->start_open == 0 ? ' filters-item--closed' : '' }}">
                                                <button type="button"
                                                        class="filters-item-head">{{ $one_parameter->itemByLang->name ?? '' }}</button>
                                                <div
                                                    class="filters-item-inner"{{ $one_parameter->start_open == 0 ? ' style=display:none;' : '' }}>
                                                    <div class="filters-search">
                                                        <input type="text" class="filters-search-input"
                                                               placeholder="{{ ShowLabelById(109) }} ...">
                                                    </div>
                                                    <div class="filters-item-list goods-parameter-values-ids">
                                                        <ul>
                                                            @foreach($parameter_values[$one_parameter->id] as $one_parameter_value)
                                                                <li>
                                                                    <input type="checkbox"
                                                                           name="p_{{ $one_parameter_value->goods_parametr_id ?? '' }}[]"
                                                                           id="id-{{ $one_parameter_value->id ?? '' }}"
                                                                           value="{{ $one_parameter_value->id ?? '' }}"
                                                                           data-parameter-id="{{ $one_parameter_value->goods_parametr_id ?? '' }}" class="click-param"
                                                                    @if(!empty($filters_elements['p_'.$one_parameter_value->goods_parametr_id]))
                                                                        {{ !empty($filters_elements) && in_array($one_parameter_value->id, $filters_elements['p_'.$one_parameter_value->goods_parametr_id]) ? 'checked' : '' }}
                                                                        @endif {{ $goods_parameter_values_ids && in_array($one_parameter_value->id, $goods_parameter_values_ids) || (isset($filters_elements['p_'.$one_parameter_value->goods_parametr_id])) ? '' : 'disabled' }}>
                                                                    <label
                                                                        for="id-{{ $one_parameter_value->id ?? '' }}">
                                                                        {{ $one_parameter_value->itemByLang->name ?? '' }}
                                                                        @if($one_parameter->is_color == 1)
                                                                            <span
                                                                                style="color: {{ $one_parameter_value->color_code ?? '' }}"></span>
                                                                        @endif
                                                                    </label>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif

                                <div class="filters-item">
                                    <button type="button"
                                            class="filters-item-head">{{ ShowLabelById(202) }}
                                    </button>
                                    <div
                                        class="filters-item-inner">
                                        <div class="filters-item-list">
                                            <ul>
                                                <li>
                                                    <input type="checkbox" name="price_promo"
                                                           id="price-promo" {{ $filters_elements && isset($filters_elements['price_promo']) ? 'checked' : '' }}>
                                                    <label for="price-promo">{{ ShowLabelById(203) }}</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="new"
                                                           id="new" {{ $filters_elements && isset($filters_elements['new']) ? 'checked' : '' }}>
                                                    <label for="new">{{ ShowLabelById(204) }}</label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="in_stoc"
                                                           id="in-stoc" {{ $filters_elements && isset($filters_elements['in_stoc']) ? 'checked' : '' }}>
                                                    <label for="in-stoc">{{ ShowLabelById(205) }}</label>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </form>
                    </div>
                    <div class="catalog-page-content">

                        @if(request()->input('s'))
                            <div class="search-page-head">
                                <div class="search-page-text">
                                    <p>{{ ShowLabelById(236) }} <b>"{{ request()->input('s') }}"</b></p>
                                </div>
                                {{--<div class="search-page-results">
                                    <p>Număr de produse: 56</p>
                                </div>--}}
                            </div>
                            @if(!empty($search_goods_subject) && count($search_goods_subject))
                                <div class="search-page-categories">
                                    <ul>
                                        @foreach($search_goods_subject as $one_goods_subject)
                                            <li>
                                                <a href="{{ route('category', $one_goods_subject->alias) }}">{{ $one_goods_subject->itemByLang->name ?? '' }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endif

                        <div class="catalog-page-content-filter">
                            <a href="javascript:;" class="button open-filter">{{ ShowLabelById(120) }}</a>
                        </div>
                        <div class="catalog-page-slider">
                            @if(!request()->input('s'))
                                <div class="section-head mb-2">
                                    @if($goods_meta_page)
                                        <h1 class="h2">{{ $goods_meta_page->itemByLang->h1_title ?: $goods_meta_page->itemByLang->name }}</h1>
                                    @else
                                        <h1 class="h2">{{ $goods_subject && $goods_subject->itemByLang->h1_title ? $goods_subject->itemByLang->h1_title : $goods_subject->itemByLang->name }}</h1>
                                    @endif
                                </div>
                            @endif
                            @if($goods_subject && $goods_subject->oImages->isNotEmpty())
                                <div class="home-hero">
                                    <div class="home-hero-slider">
                                        <div class="swiper-container">
                                            <div class="swiper-wrapper">
                                                @foreach($goods_subject->oImages as $one_image)
                                                    <div class="swiper-slide">
                                                        <a{{ $one_image->link ? ' href='.$one_image->link : '' }}>
                                                            <img
                                                                src="{{ $one_image->img && file_exists('upfiles/goods-subject-gallery/' . $one_image->img) ? asset('upfiles/goods-subject-gallery/'. $one_image->img) : asset('front-assets/img/no-image-slider.png') }}"
                                                                alt="{{ $goods_subject->itemByLang->name .'- image' }} {{ $loop->iteration }}">
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <button type="button" class="home-hero-nav home-hero-nav--prev">
                                            <svg>
                                                <use
                                                    xlink:href="{{ asset('front-assets/svg/sprite.svg#slider-arrow') }}"></use>
                                            </svg>
                                        </button>
                                        <button type="button" class="home-hero-nav home-hero-nav--next">
                                            <svg>
                                                <use
                                                    xlink:href="{{ asset('front-assets/svg/sprite.svg#slider-arrow') }}"></use>
                                            </svg>
                                        </button>
                                        <div class="slider-pagination"></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="catalog-page-info">
                            @include('front.templates.catalog-sorting')
                        </div>

                        <div class="render-products-list">
                            @if(!empty($goods_items_list) && count($goods_items_list))
                                @include('front.pages.ajax.products-list', ['goods_items_list' => $goods_items_list])
                            @else
                                <div class="basket-end-inner">
                                    <div class="basket-end-icon">
                                        <img src="{{ asset('front-assets/img/icons/basket-error.svg') }}" alt="Empty">
                                    </div>
                                    <div class="basket-end-title">{{ ShowLabelById(53) }}</div>
                                    <div class="basket-end-link">
                                        <a href="{{ route('/') }}">{{ ShowLabelById(54) }}</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($goods_meta_page && $goods_meta_page->itemByLang->body)
                    <div class="catalog-page-text">
                        <div class="common-text hidden-text">
                            <h3>{{ $goods_meta_page->itemByLang->h1_title ?: $goods_meta_page->itemByLang->name }}</h3>
                            {!! $goods_meta_page->itemByLang->body ?? '' !!}
                        </div>
                        <div class="open-hidden-text">
                            <a href="javascript:;" data-more="{{ ShowLabelById(122) }}"
                               data-less="{{ ShowLabelById(123) }}">
                                <span>{{ ShowLabelById(122) }}</span>
                                <svg>
                                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#arrow-right') }}"></use>
                                </svg>
                            </a>
                        </div>
                    </div>
                @elseif($goods_subject && $goods_subject->itemByLang->body)
                    <div class="catalog-page-text">
                        <div class="common-text hidden-text">
                            <h3>{{ $goods_subject->itemByLang->h1_title ?: $goods_subject->itemByLang->name }}</h3>
                            {!! $goods_subject->itemByLang->body ?? '' !!}
                        </div>
                        <div class="open-hidden-text">
                            <a href="javascript:;" data-more="{{ ShowLabelById(122) }}"
                               data-less="{{ ShowLabelById(123) }}">
                                <span>{{ ShowLabelById(122) }}</span>
                                <svg>
                                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#arrow-right') }}"></use>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('other-scripts')
        @include('front.templates.price-range-script')
    @endpush

@stop
