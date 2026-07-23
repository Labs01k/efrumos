@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('cabinet-wish') }}
            </div>
        </div>

        <div class="cabinet">
            <div class="container">
                <div class="section-head">
                    <h1 class="h2">{{ ShowLabelById(63) }}</h1>
                </div>
                <div class="cabinet-inner">
                    @include('front.pages.cabinet.templates.menu')
                    <div class="cabinet-content">
                        @if(!empty($goods_items_list) && count($goods_items_list))
                            <div class="cabinet-table">
                                <table>
                                    <thead>
                                    <tr>
                                        <th>{{ ShowLabelById(64) }}</th>
                                        <th></th>
                                        <th>{{ ShowLabelById(65) }}</th>
                                        <th>{{ ShowLabelById(66) }}</th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($goods_items_list as $one_goods)
                                        @php
                                            $goods_price_collect = getGoodsPrice($one_goods);
                                            //$goods_price = $goods_price_collect->promo_price > 0 ? $goods_price_collect->promo_price : $goods_price_collect->price;
                                        @endphp
                                        <tr class="wish-row-item">
                                            <td>
                                                <div class="cabinet-table-img">
                                                    <a href="{{ route('catalog-product', ['product', $one_goods->alias]) }}">
                                                        <img
                                                            src="{{ $one_goods->oImage && $one_goods->oImage->img && file_exists('upfiles/goods-items/s/' . showImg($one_goods->oImage->img)) ? asset('upfiles/goods-items/s/'. showImg($one_goods->oImage->img)) : asset('front-assets/img/no-image-xs.png') }}" style="width: 84px;"
                                                            alt="{{ $one_goods->itemByLang->name ?? '' }}">
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="cabinet-table-name"><a
                                                    href="{{ route('catalog-product', ['product', $one_goods->alias]) }}">{{ $one_goods->itemByLang->name ?? '' }}</a>
                                            </td>
                                            <td class="cabinet-table-price text-nowrap">
                                                {{ $goods_price_collect->price_default ?? '' }} {{ ShowLabelById(3) }}
                                            </td>
                                            <td class="cabinet-table-promo text-nowrap">
                                                @if($goods_price_collect->price_promo > 0)
                                                    {{ $goods_price_collect->price_promo ?? '' }} {{ ShowLabelById(3) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <a href="javascript:;"
                                                   class="button button--black add-to-basket" data-goods-item-id="{{ $one_goods->id ?? '' }}" data-page="cabinet-wish">{{ ShowLabelById(5) }}</a>
                                            </td>
                                            <td>
                                                <div class="cabinet-table-cta cabinet-table-remove">
                                                    <a href="javascript:;" class="delete-wish-item"
                                                       data-goods-item-id="{{ $one_goods->id ?? '' }}">
                                                        <svg>
                                                            <use
                                                                xlink:href="{{ asset('front-assets/svg/sprite.svg#delete') }}"></use>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="cabinet-table-total-row">
                                        <td class="cabinet-table-total">{{ ShowLabelById(67) }}:</td>
                                        <td></td>
                                        <td class="cabinet-table-price text-nowrap"><span class="wish-total-price">{{ $wish_total_price ?? '' }}</span> {{ ShowLabelById(3) }}</td>
                                        <td class="cabinet-table-promo text-nowrap">
                                            @if($wish_total_promo_price && $wish_total_promo_price > 0)
                                                <span class="wish-total-promo-price">{{ $wish_total_promo_price }}</span> {{ ShowLabelById(3) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <a href="javascript:;" class="button button--black add-all-wish-to-basket">{{ ShowLabelById(68) }}</a>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
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
        </div>

    </div>

@stop
