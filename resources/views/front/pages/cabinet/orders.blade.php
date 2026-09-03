@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('cabinet-orders') }}
            </div>
        </div>

        <div class="cabinet">
            <div class="container">
                <div class="section-head">
                    <h1 class="h2">Cont persoană fizică</h1>
                </div>
                <div class="cabinet-inner">
                    @include('front.pages.cabinet.templates.menu')
                    <div class="cabinet-content">
                        @if(!empty($user_orders) && count($user_orders))
                            <div class="cabinet-table">
                                <table>
                                    <thead>
                                    <tr>
                                        <th>{{ ShowLabelById(70) }}</th>
                                        <th>{{ ShowLabelById(71) }}</th>
                                        <th>{{ ShowLabelById(72) }}</th>
                                        <th>{{ ShowLabelById(73) }}</th>
                                        <th style="text-align: center;">{{ __('variables.payment_status') }}</th>
										{{--
                                        <th style="text-align: center;">{{ ShowLabelById(75) }}</th>
										--}}
                                        <th></th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($user_orders as $one_order)
                                        <tr>
                                            <td>#{{ $one_order->id ?? '' }}</td>
                                            <td>{{ getDefaultDateFormat($one_order->created_at) }}</td>
                                            <td>{{ $one_order->ordersData && $one_order->ordersData->total_price ? $one_order->ordersData->total_price : 0  }} {{ ShowLabelById(3) }}</td>
                                            <td>{{ ShowLabelById(76) }}</td>
                                            <td align="center">{{ $one_order->payment_status?->label() ?? __('variables.payment_status_pending') }}</td>
											{{--
                                            <td align="center">
                                                <div class="cabinet-table-status">
                                                    <a href="javascript:;">Tracking</a>
                                                </div>
                                            </td>
											--}}
                                            <td>
                                                <div class="cabinet-table-cta cabinet-table-details">
                                                    <a href="javascript:;" class="open-order-details show-order-details"
                                                       data-order-id="{{ $one_order->id ?? '' }}">
                                                        <svg>
                                                            <use
                                                                xlink:href="{{ asset('front-assets/svg/sprite.svg#details') }}"></use>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="cabinet-table-cta">
                                                    <a href="javascript:;" class="repeat-order" data-order-id="{{ $one_order->id ?? '' }}">
                                                        <svg>
                                                            <use
                                                                xlink:href="{{ asset('front-assets/svg/sprite.svg#refresh') }}"></use>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
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

    <div class="common-modal order-details">
        <div class="common-modal-bg"></div>
        <div class="common-modal-wrapper">
            <button type="button" class="common-modal-close">
                <svg>
                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#close') }}"></use>
                </svg>
            </button>
            <div class="common-modal-inner render-order-details"></div>
        </div>
    </div>
@stop
