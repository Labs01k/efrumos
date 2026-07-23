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
                items: {!! $goods_objects ?? '' !!}
            }
        });
    </script>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('brands-item', $brand_item) }}
            </div>
        </div>

        <div class="section pt-0 catalog-page">
            <div class="container">
                <div class="brands-catalog">
                    @if($brand_item->oImage && $brand_item->oImage->img && file_exists('upfiles/brand/m/'. showImg($brand_item->oImage->img)))
                        <div class="brands-catalog-img">
                            <img
                                src="{{ $brand_item->oImage && $brand_item->oImage->img && file_exists('upfiles/brand/m/'. showImg($brand_item->oImage->img)) ? asset('upfiles/brand/m/'. showImg($brand_item->oImage->img)) : asset('front-assets/img/no-image-brand-m.png') }}"
                                alt="{{ $brand_item->oImage->name ?? '' }}">
                        </div>
                    @endif
                    <div class="brands-catalog-content">
                        <h1 class="h2">{{ $brand_item->itemByLang->name ?? '' }}</h1>
                        @if($brand_item->itemByLang->body)
                            <div class="brands-catalog-text common-text">
                                {!! $brand_item->itemByLang->body ?? '' !!}
                            </div>
                        @endif
                        @if($brand_item->img_certificate && file_exists('upfiles/goods-brand-certificate/'. $brand_item->img_certificate))
                            <div class="product-end-palette mt-2 mb-0">
                                <a href="{{ asset('upfiles/goods-brand-certificate/'. $brand_item->img_certificate) }}" data-fancybox="">
                                    <span>Certificat:</span>
                                    <img src="{{ asset('front-assets/img/icons/certificate.svg') }}"
                                         alt="Efrumos brand certificate">
                                </a>
                            </div>
                        @endif
                        {{--<div class="brand-certificate mt-2">
                            <a href="{{ $brand_item->oImage && $brand_item->oImage->img && file_exists('upfiles/brand/m/'. showImg($brand_item->oImage->img)) ? asset('upfiles/brand/m/'. showImg($brand_item->oImage->img)) : asset('front-assets/img/no-image-brand-m.png') }}" data-fancybox="">
                            <img
                                src="{{ $brand_item->oImage && $brand_item->oImage->img && file_exists('upfiles/brand/m/'. showImg($brand_item->oImage->img)) ? asset('upfiles/brand/m/'. showImg($brand_item->oImage->img)) : asset('front-assets/img/no-image-brand-m.png') }}"
                                alt="{{ $brand_item->oImage->name ?? '' }}">
                            </a>
                        </div>--}}
                    </div>
                </div>
                <div class="catalog-page-inner">
                    <div class="catalog-filters">
                        <div class="filters-head">
                            <div class="filters-title">{{ ShowLabelById(117) }}</div>
                            <div class="filters-remove">   <!-- d-block -->
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
                              method="post" id="filter-data">
                            @csrf
                            <input type="hidden" value="{{ $brand_item->id ?? '' }}" name="brand[]">

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
                                @if(!empty($parameters) && count($parameters))
                                    @foreach($parameters as $one_parameter)
                                        @if($parameter_values[$one_parameter->id] && count($parameter_values[$one_parameter->id]))
                                            <div class="filters-item">
                                                <button type="button"
                                                        class="filters-item-head">{{ $one_parameter->itemByLang->name ?? '' }}</button>
                                                <div class="filters-item-inner">
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
                                                                           data-parameter-id="{{ $one_parameter_value->goods_parametr_id ?? '' }}"
                                                                           class="click-param"
                                                                    @if(!empty($filters_elements['p_'.$one_parameter_value->goods_parametr_id]))
                                                                        {{ !empty($filters_elements) && in_array($one_parameter_value->id, $filters_elements['p_'.$one_parameter_value->goods_parametr_id]) ? 'checked' : '' }}
                                                                        @endif {{ isset($goods_parameter_values_ids) && in_array($one_parameter_value->id, $goods_parameter_values_ids) || (isset($filters_elements['p_'.$one_parameter_value->goods_parametr_id])) ? '' : 'disabled' }}>
                                                                    <label
                                                                        for="id-{{ $one_parameter_value->id ?? '' }}">{{ $one_parameter_value->itemByLang->name ?? '' }}</label>
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
                        <div class="catalog-page-content-filter">
                            <a href="javascript:;" class="button open-filter">{{ ShowLabelById(120) }}</a>
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
            </div>
        </div>

    </div>

    @push('other-scripts')
        @include('front.templates.price-range-script')
    @endpush

@stop
