@extends('front.app')

@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('shops-list') }}
            </div>
        </div>

        <div class="section pt-0 stores">
            <div class="container">
                <div class="section-head">
                    <h1 class="h2">{{ $menu_id && $menu_id->itemByLang ? $menu_id->itemByLang->name : '' }}</h1>
                </div>
                @if(!empty($shops) && count($shops))
                    <div class="stores-inner">
                        <div class="stores-tabs">
                            @foreach($shops as $one_shop)
                                <div class="stores-tabs-item">
                                    <button type="button" class="stores-tab"
                                            onclick="openMap(event, 'stores-{{ $one_shop->id ?? '' }}')">
                                        <span class="stores-tab-title">{{ $one_shop->itemByLang->name ?? '' }}</span>
                                        <span class="stores-tab-list">
                                            <ul>
                                                <li>
                                                    <span>
                                                        <svg>
                                                            <use
                                                                xlink:href="{{ asset('front-assets/svg/sprite.svg#pin') }}"></use>
                                                        </svg>
                                                    </span>
                                                    <span>{{ $one_shop->itemByLang->address ?? '' }}</span>
                                                </li>
                                                <li>
                                                    <span>
                                                        <svg>
                                                            <use
                                                                xlink:href="{{ asset('front-assets/svg/sprite.svg#phone') }}"></use>
                                                        </svg>
                                                    </span>
                                                    <span>{{ $one_shop->phone ?? '' }}</span>
                                                </li>
                                                <li>
                                                    <span>
                                                        <svg>
                                                            <use
                                                                xlink:href="{{ asset('front-assets/svg/sprite.svg#clock') }}"></use>
                                                        </svg>
                                                    </span>
                                                    <span>{{ $one_shop->itemByLang->schedule ?? '' }}</span>
                                                </li>
                                            </ul>
                                        </span>
                                    </button>
                                    <div data-id="stores-{{ $one_shop->id ?? '' }}" class="stores-map">
                                        {!! $one_shop->map_iframe ?? '' !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="stores-map-wrapper">
                            @foreach($shops as $one_shop)
                                <div data-id="stores-{{ $one_shop->id ?? '' }}" class="stores-map">
                                    {!! $one_shop->map_iframe ?? '' !!}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="stores-info">
                    <div class="stores-tab-title">{{ ShowLabelById(127) }}</div>
                    <div class="stores-tab-text">
                        <p>{{ ShowLabelById(129) }}</p>
                    </div>
                    <div class="stores-tab-list">
                        <ul>
                            <li>
                                <div>
                                    <svg>
                                        <use xlink:href="{{ asset('front-assets/svg/sprite.svg#phone') }}"></use>
                                    </svg>
                                </div>
                                <div>
                                    <a href="tel:{{ str_replace([' ', '(', ')', '-'], '', showSettingBodyByAlias('main-phone')) }}">{{ showSettingBodyByAlias('main-phone') }}</a>
                                </div>
                            </li>
                            <li>
                                <div>
                                    <svg>
                                        <use xlink:href="{{ asset('front-assets/svg/sprite.svg#envelope') }}"></use>
                                    </svg>
                                </div>
                                <div>
                                    <a href="mailto:{{ showSettingBodyByAlias('main-email') }}">{{ showSettingBodyByAlias('main-email') }}</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
