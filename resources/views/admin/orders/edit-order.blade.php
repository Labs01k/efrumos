@extends('admin.app')

@push('after-head-scripts')
    <script>
        window.dataLayer = window.dataLayer || [];
    </script>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-PLD5TQR');</script>
    <!-- End Google Tag Manager -->
@endpush

@section('content')
    @push('after-body-start-scripts')
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PLD5TQR"
                          height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
    @endpush

    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    @include('admin.templates.breadcrumbs')
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-lg-flex align-items-center mb-4 gap-3">
                        <div class="position-relative">
                            <h6 class="mb-0 text-uppercase">Order info</h6>
                        </div>
                        <div class="ms-auto">
                            <a href="{{ urlForFunctionLanguage($lang, '') }}"
                               class="btn btn-primary mt-2 mt-lg-0"><i
                                    class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            <a href="{{ urlForFunctionLanguage($lang, 'ordersCart/orderscart') }}"
                               class="btn btn-primary mt-2 mt-lg-0"><i
                                    class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                            </a>
                        </div>
                    </div>
                    <hr/>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-secondary">
                            <tr>
                                <th scope="col" class="text-center">№.:</th>
                                <th scope="col" class="text-center">{{__('variables.order_type')}}</th>
                                <th scope="col" class="text-center">{{__('variables.delivery_method')}}</th>
                                <th scope="col" class="text-center">{{__('variables.pay_method')}}</th>
                                <th scope="col" class="text-center">{{__('variables.total_count')}}</th>
                                <th scope="col" class="text-center">{{__('variables.total_price')}}</th>
                                <th scope="col" class="text-center">{{__('variables.date_table')}}</th>
                                {{--@if($groupSubRelations->active == 1)
                                    <th scope="col" class="text-center">{{__('variables.active_table')}}</th>
                                @endif--}}
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="row-id" data-id="{{$orders->id ?? ''}}">
                                <th class="text-center" scope="row">{{ $orders->id }}</th>
                                <td class="text-center">
                                    <span>{{$orders->fast_order == 1 ? 'Fast' : 'Simple'}}</span>
                                </td>
                                <td class="text-center">
                                    <span>
                                        {{$orders->delivery_method ?? ''}}
                                        @if($orders->delivery_method == 'delivery')
                                            <span
                                                class="badge bg-secondary">({{$orders->ordersData->delivery_cost ?? 0}}
                                                )</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span>{{$orders->pay_method ?? ''}}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$orders->ordersData->total_count ?? ''}}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$orders->ordersData->total_price ?? ''}}</span>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-secondary">{{ getDefaultDateFormatAdmin($orders->created_at) }}</span>
                                </td>
                                {{--<td class="text-center">
                                    <div class="form-switch">
                                        <input class="form-check-input change-active" type="checkbox"
                                               data-active="{{$orders->active}}"
                                               data-element-id="{{$orders->id}}"
                                               data-action="main-active"
                                               id="switch-active-{{$orders->id}}"
                                               data-url="{{$url_for_active_elem}}" {{$orders->active == 1 ? 'checked' : ''}}>
                                        <label class="form-check-label"
                                               for="switch-active-{{$orders->id}}"></label>
                                    </div>
                                </td>--}}
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-lg-flex align-items-center mb-4 gap-3">
                        <div class="position-relative">
                            <h6 class="mb-0 text-uppercase">User info</h6>
                        </div>
                        <div class="ms-auto">
                        </div>
                    </div>
                    <hr/>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-secondary">
                            <tr>
                                <th scope="col" class="text-center">{{__('variables.name_text')}}</th>
                                <th scope="col" class="text-center">{{__('variables.last_name')}}</th>
                                <th scope="col" class="text-center">{{__('variables.email_text')}}</th>
                                <th scope="col" class="text-center">{{__('variables.phone')}}</th>
                                <th scope="col" class="text-center">{{__('variables.district_local')}}</th>
                                <th scope="col" class="text-center">{{__('variables.city')}}</th>
                                <th scope="col" class="text-center">{{__('variables.address')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="row-id">
                                <td class="text-center">
                                    <span>{{$user_info->name ?? ''}}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$user_info->last_name ?? __('variables.do_not_exist')}}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$user_info->email ?? __('variables.do_not_exist')}}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$user_info->phone ?? __('variables.do_not_exist')}}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$user_district->name ?? __('variables.do_not_exist') }}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$user_info->city ?? __('variables.do_not_exist')}}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$user_info->address ?? __('variables.do_not_exist')}}</span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            @if(!empty($orderedItems) && count($orderedItems))
                <div class="card">
                    <div class="card-body">
                        <div class="d-lg-flex align-items-center mb-4 gap-3">
                            <div class="position-relative">
                                <h6 class="mb-0 text-uppercase">Ordered items</h6>
                            </div>
                            <div class="ms-auto">
                            </div>
                        </div>
                        <hr/>
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover">
                                <thead class="table-secondary">
                                <tr>
                                    <th scope="col" class="text-center">№</th>
                                    <th scope="col" class="text-center">{{__('variables.name_text')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.price')}} (1 шт.)</th>
                                    <th scope="col" class="text-center">{{__('variables.total_count')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.date_table')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($basket as $basket_elem)
                                    <tr class="row-id">
                                        <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                        <td class="text-center">
                                            <span>{{$basket_elem->goods_name ?? ''}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{$basket_elem->goods_price ?? ''}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{$basket_elem->items_count ?? ''}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-secondary">{{ getDefaultDateFormatAdmin($basket_elem->created_at) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @if($orders->ordersUsers && $orders->ordersUsers->descr)
                <div class="card">
                    <div class="card-body">
                        <div class="d-lg-flex align-items-center mb-4 gap-3">
                            <div class="position-relative">
                                <h6 class="mb-0 text-uppercase">{{__('variables.comment_table')}}</h6>
                            </div>
                            <div class="ms-auto">
                            </div>
                        </div>
                        <hr/>
                        <div class="d-lg-flex align-items-center mb-4 gap-3">
                            <p>{{ $orders->ordersUsers->descr ?? '' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <button type="button" class="btn btn-outline-danger px-5 refund-order-ga4" data-order-id="{{ $orders->id ?? '' }}">{{__('variables.order_refund_ga4')}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@stop

