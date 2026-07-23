@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('brands-list') }}
            </div>
        </div>

        <div class="brands brands-page">
            <div class="container">
                <h1 class="h2">{{ $menu_id && $menu_id->itemByLang ? $menu_id->itemByLang->name : '' }}</h1>
                @if(!empty($brands_l1) && count($brands_l1))
                    <div class="brands-list">
                        @foreach($brands_l1 as $ona_brand_l1)
                            <div class="brands-item">
                                <div class="brands-img">
                                    <a href="{{ route('brands', $ona_brand_l1->alias) }}">
                                        <img
                                            src="{{ $ona_brand_l1->oImage && $ona_brand_l1->oImage->img && file_exists('upfiles/brand/m/'. showImg($ona_brand_l1->oImage->img)) ? asset('upfiles/brand/m/'. showImg($ona_brand_l1->oImage->img)) : asset('front-assets/img/no-image-brand-m.png') }}"
                                            alt="{{ $ona_brand_l1->oImage->name ?? '' }}">
                                    </a>
                                </div>
                                <div class="brands-content">
                                    <h2 class="link-more">
                                        <a href="{{ route('brands', $ona_brand_l1->alias) }}">
                                            <span>{{ $ona_brand_l1->itemByLang->name ?? '' }}</span>
                                            <svg>
                                                <use
                                                    xlink:href="{{ asset('front-assets/svg/sprite.svg#arrow-right') }}"></use>
                                            </svg>
                                        </a>
                                    </h2>
                                    @if($ona_brand_l1->itemByLang->body)
                                        <div class="common-text mb-1">
                                            <p>{{ substrBySpace($ona_brand_l1->itemByLang->body, 150).' ...' ?? '' }}</p>
                                        </div>
                                    @endif
                                    @if($ona_brand_l1->children->isNotEmpty())
                                        <div class="brands-info">
                                            <ul>
                                                @foreach($ona_brand_l1->children as $one_brand_l2)
                                                    <li>
                                                        <a href="{{ route('brands', $one_brand_l2->alias) }}">{{ $one_brand_l2->itemByLang->name ?? '' }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <div class="brands-more">
                                        <a href="javascript:;" class="toggle-btn" data-more="{{ ShowLabelById(51) }}"
                                           data-less="{{ ShowLabelById(52) }}">{{ ShowLabelById(51) }}</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
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

@stop
