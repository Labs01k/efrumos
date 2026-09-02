@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('styles')
    @if(!$google_maps_key)
        <link rel="stylesheet" href="{{ asset('front-assets/libs/leaflet/leaflet.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('front-assets/css/shops-page.css?v=').config('custom.front.css_version') }}">
@stop

@section('container')

    {{--
        Страница «Магазины» по макету 758:168: единая интерактивная карта,
        панель списка поверх карты (десктоп), карта/список с переключателем
        и шторкой магазина (мобайл), модалка запроса геолокации.
        Карта — адаптер: Google Maps при наличии ключа, иначе Leaflet/OSM.
    --}}
    <div class="page-content shp-page">

        <div class="shp" data-shp>

            {{-- мобильная шапка страницы: город по центру / переключатель вида.
                 Кнопка «назад» из макета убрана — решение заказчика. --}}
            <div class="shp-topbar">
                <span class="shp-topbar-spacer" aria-hidden="true"></span>
                <div class="shp-city pb-city" data-shp-city>
                    <button type="button" class="shp-city-trigger" aria-haspopup="listbox">
                        <span data-shp-city-value>{{ trans('variables.product_all_shops') }}</span>
                        <svg class="pb-chevron" viewBox="0 0 16 16" aria-hidden="true"><path d="M4 6l4 4 4-4"/></svg>
                    </button>
                    <ul class="pb-dropdown" role="listbox">
                        <li><a class="pb-dropdown-item is-selected" href="javascript:;" data-shp-city-option="" role="option">{{ trans('variables.product_all_shops') }}</a></li>
                        @foreach($shops_cities as $one_city)
                            <li><a class="pb-dropdown-item" href="javascript:;" data-shp-city-option="{{ $one_city }}" role="option">{{ $one_city }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="shp-topbar-btn" data-shp-view-toggle
                        aria-label="{{ trans('variables.shops_view_list') }}">
                    <svg class="shp-icon-list" viewBox="0 0 20 20" aria-hidden="true"><path d="M7 5h10M7 10h10M7 15h10M3 5h.01M3 10h.01M3 15h.01"/></svg>
                    <svg class="shp-icon-map" viewBox="0 0 20 20" aria-hidden="true"><path d="M7 3 3 5v12l4-2 6 2 4-2V3l-4 2-6-2zM7 3v12M13 5v12"/></svg>
                </button>
            </div>

            <div class="shp-map-wrap">
                <div class="shp-map" id="shp-map"></div>

                {{-- мобильная карусель мини-карточек (наполняет скрипт) --}}
                <div class="shp-carousel" data-shp-carousel></div>

                {{-- мобильная шторка выбранного магазина (наполняет скрипт) --}}
                <div class="shp-sheet" data-shp-sheet hidden></div>

                {{-- панель списка поверх карты (десктоп) --}}
                <aside class="shp-panel">
                    <div class="shp-panel-head">
                        <div class="shp-city pb-city" data-shp-city>
                            <button type="button" class="shp-city-trigger" aria-haspopup="listbox">
                                <span data-shp-city-value>{{ trans('variables.product_all_shops') }}</span>
                                <svg class="pb-chevron" viewBox="0 0 16 16" aria-hidden="true"><path d="M4 6l4 4 4-4"/></svg>
                            </button>
                            <ul class="pb-dropdown" role="listbox">
                                <li><a class="pb-dropdown-item is-selected" href="javascript:;" data-shp-city-option="" role="option">{{ trans('variables.product_all_shops') }}</a></li>
                                @foreach($shops_cities as $one_city)
                                    <li><a class="pb-dropdown-item" href="javascript:;" data-shp-city-option="{{ $one_city }}" role="option">{{ $one_city }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="shp-tabs" role="tablist">
                            <button type="button" class="shp-tab is-active" data-shp-sort="default" role="tab">{{ trans('variables.shops_order_default') }}</button>
                            <button type="button" class="shp-tab" data-shp-sort="nearest" role="tab">{{ trans('variables.shops_nearest') }}</button>
                        </div>
                    </div>

                    <div class="shp-list" data-shp-list>
                        @foreach($shops_data as $one_shop)
                            <article class="shp-card" data-shp-card="{{ $one_shop['id'] }}"
                                     data-city="{{ $one_shop['city'] }}"
                                     data-lat="{{ $one_shop['lat'] }}" data-lng="{{ $one_shop['lng'] }}">
                                <div class="shp-card-head">
                                    <h3 class="shp-card-name">{{ $one_shop['name'] }}</h3>
                                    <span class="shp-chips">
                                        <span class="shp-chip shp-chip-distance" data-shp-distance hidden></span>
                                        <a class="shp-chip shp-chip-route"
                                           href="https://www.google.com/maps/dir/?api=1&destination={{ $one_shop['lat'] }},{{ $one_shop['lng'] }}"
                                           target="_blank" rel="noopener">{{ trans('variables.shops_route') }}</a>
                                    </span>
                                    <button type="button" class="shp-locate" data-shp-locate
                                            aria-label="{{ trans('variables.shops_view_map') }}">
                                        <svg viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="2.6"/><path d="M8 1.5V4M8 12v2.5M1.5 8H4M12 8h2.5"/></svg>
                                    </button>
                                </div>
                                <p class="shp-card-address">{{ $one_shop['address'] }}</p>
                                @if(count($one_shop['images']))
                                    {{-- фотогалерея магазина (тикет вёрстки; в макете нет — блок опциональный) --}}
                                    <div class="shp-card-gallery">
                                        @foreach($one_shop['images'] as $one_image)
                                            <a href="{{ $one_image }}" data-fancybox="shop-{{ $one_shop['id'] }}">
                                                <img src="{{ $one_image }}" loading="lazy" alt="{{ $one_shop['name'] }}">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="shp-card-contacts">
                                    @if($one_shop['phone'])
                                        <p class="shp-card-row">
                                            <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3.2 1.8h2.4l1.2 3-1.5 1.2a10 10 0 0 0 4.7 4.7l1.2-1.5 3 1.2v2.4a1.3 1.3 0 0 1-1.4 1.3A12.7 12.7 0 0 1 1.9 3.2a1.3 1.3 0 0 1 1.3-1.4z"/></svg>
                                            <a href="tel:{{ preg_replace('/[^+\d]/', '', $one_shop['phone']) }}">{{ $one_shop['phone'] }}</a>
                                        </p>
                                    @endif
                                    @if($one_shop['schedule'])
                                        <p class="shp-card-row">
                                            <svg viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="6.3"/><path d="M8 4.5V8L5.8 9.6"/></svg>
                                            <span>{{ $one_shop['schedule'] }}</span>
                                        </p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </aside>
            </div>

            {{-- модалка запроса геолокации (нода 789:20645) --}}
            <div class="shp-geo-modal" data-shp-geo-modal hidden>
                <div class="shp-geo-overlay" data-shp-geo-close></div>
                <div class="shp-geo-dialog" role="dialog" aria-modal="true">
                    <button type="button" class="shp-geo-x" data-shp-geo-close aria-label="✕">
                        <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 3l10 10M13 3 3 13"/></svg>
                    </button>
                    <div class="shp-geo-icon">
                        <svg viewBox="0 0 64 64" aria-hidden="true">
                            <path d="M32 4C19.8 4 10 13.8 10 26c0 15.5 22 34 22 34s22-18.5 22-34C54 13.8 44.2 4 32 4z" fill="#db6e97"/>
                            <path d="M24 26.5l5.5 5.5L41 20.5" stroke="#fff" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="shp-geo-title">{{ trans('variables.shops_geo_title') }}</div>
                    <div class="shp-geo-text">{{ trans('variables.shops_geo_text') }}</div>
                    <button type="button" class="pb-button shp-geo-share" data-shp-geo-share>{{ trans('variables.shops_geo_share') }}</button>
                </div>
            </div>

        </div>
    </div>

    <script>
        window.PB_SHOPS = {
            shops: @json($shops_data),
            cities: @json($shops_cities),
            googleKey: @json($google_maps_key),
            texts: {
                all: @json(trans('variables.product_all_shops')),
                m: @json(trans('variables.shops_m')),
                km: @json(trans('variables.shops_km')),
                route: @json(trans('variables.shops_route')),
                buildRoute: @json(trans('variables.shops_build_route')),
            }
        };
    </script>
@stop

@push('other-scripts')
    @if(!$google_maps_key)
        <script src="{{ asset('front-assets/libs/leaflet/leaflet.js') }}"></script>
    @endif
    <script src="{{ asset('front-assets/js/shops-page.js?v=').config('custom.front.js_version') }}"></script>
@endpush
