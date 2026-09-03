@extends('admin.app')

@section('content')

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
                            <form method="GET" action="{{urlForFunctionLanguage($lang, 'search/searchObjects')}}">
                                <div class="input-group">
                                    <input type="text" name="search-key" class="form-control"
                                           placeholder="{{ __('variables.search_object_it') }}" value="{{ $concrete_search_key ?? '' }}" title="{{ __('variables.orders_search') }}">
                                    <button type="submit" class="input-group-text btn-success btn-search"><i
                                                class="bx bx-search"></i></button>
                                </div>
                            </form>
                        </div>
                        <div class="ms-auto">
                            <a href="{{ urlForFunctionLanguage($lang, '') }}"
                               class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            @if(!empty($orders) && count($orders))
                                @if($groupSubRelations->del_to_rec == 1)
                                    <button class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                            data-url="{{urlForFunctionLanguage($lang, 'destroyOrderToCart/destroyOrderToCart')}}"
                                            data-current-url="{{ url()->current() }}" disabled>
                                        <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }}
                                        (<span>0</span>)
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                    <hr/>
                    @if(!empty($orders) && count($orders))
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">№.:</th>
                                    <th scope="col" class="text-center">{{__('variables.order_type')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.last_name')}}, {{__('variables.name_text')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.email_text')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.phone')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.total_count')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.total_price')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.delivery_method')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.pay_method')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.payment_status')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.date_table')}}</th>
                                    @if($groupSubRelations->active == 1)
                                        {{--<th scope="col" class="text-center">{{__('variables.active_table')}}</th>--}}
                                        <th scope="col" class="text-center">{{__('Details')}}</th>
                                    @endif
                                    @if($groupSubRelations->del_to_rec == 1 || $groupSubRelations->del_from_rec == 1)
                                        <th scope="col"
                                            class="text-center select-all-elements cursor-pointer">{{__('variables.delete_table')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody class="sort-table" data-url="{{ $url_for_active_elem }}">
                                @foreach($orders as $one_order)

                                    @php
                                        if($one_order->ordersFrontUser)
                                            $user_info = $one_order->ordersFrontUser;
                                        else
                                            $user_info = $one_order->ordersUsers;
                                    @endphp

                                    <tr class="row-id" data-id="{{$one_order->id}}">
                                        <th class="text-center" scope="row">{{ $one_order->id }}</th>
                                        <td class="text-center">
                                            @if($one_order->fast_order == 1)
                                                <span class="badge bg-info" style="font-size: 12px;">Fast</span>
                                            @else
                                                <span class="badge bg-primary" style="font-size: 12px;">Simple</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span>{{$user_info->name .' '.$user_info->last_name ?? __('variables.do_not_exist')}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{$user_info->email ?? __('variables.do_not_exist')}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{$user_info->phone ?? __('variables.do_not_exist')}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{$one_order->ordersData->total_count ?? __('variables.do_not_exist')}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{$one_order->ordersData->total_price ?? __('variables.do_not_exist')}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{$one_order->delivery_method ?? ''}}
                                                @if($one_order->delivery_method == 'delivery')
                                                    <span class="badge bg-secondary">({{$one_order->ordersData->delivery_cost}}
                                                        )</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{$one_order->pay_method ?? ''}}</span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $paymentBadgeClass = match ($one_order->payment_status) {
                                                    \App\Enums\PaymentStatus::Paid => 'bg-success',
                                                    \App\Enums\PaymentStatus::Failed => 'bg-danger',
                                                    \App\Enums\PaymentStatus::Cancelled => 'bg-dark',
                                                    default => 'bg-warning text-dark',
                                                };
                                            @endphp
                                            <span class="badge {{ $paymentBadgeClass }}">{{ $one_order->payment_status?->label() ?? '—' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ getDefaultDateFormatAdmin($one_order->created_at) }}</span>
                                        </td>
                                        {{--<td class="text-center">
                                            <div class="form-switch">
                                                <input class="form-check-input change-active" type="checkbox"
                                                       data-active="{{$one_order->active}}"
                                                       data-element-id="{{$one_order->id}}"
                                                       data-action="main-active"
                                                       id="switch-active-{{$one_order->id}}"
                                                       data-url="{{$url_for_active_elem}}" {{$one_order->active == 1 ? 'checked' : ''}}>
                                                <label class="form-check-label"
                                                       for="switch-active-{{$one_order->id}}"></label>
                                            </div>
                                        </td>--}}
                                        <td class="text-center">
                                            <a href="{{urlForFunctionLanguage($lang, Str::slug($one_order->delivery_method).'/edititem/'.$one_order->id)}}"
                                               class="btn btn-sm btn-success">{{__('Detail')}}</a>
                                        </td>
                                        @if($groupSubRelations->del_to_rec == 1)
                                            <td class="text-center">
                                                <input class="form-check-input destroy-element" type="checkbox"
                                                       name="destroy_element"
                                                       value="{{$one_order->id}}"
                                                       id="destroy-element-{{$one_order->id}}">
                                                <label class="form-check-label"
                                                       for="destroy-element-{{$one_order->id}}"></label>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.templates.pagination', ['paginator' => $orders])
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
