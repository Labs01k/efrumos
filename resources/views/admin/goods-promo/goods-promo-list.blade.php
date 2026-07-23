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
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'createGoodsPromo/createGoodsPromo') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            @endif
                            @if($groupSubRelations->del_to_rec == 1)
                                <button class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                        data-url="{{urlForFunctionLanguage($lang, 'destroyGoodsPromo/destroyGoodsPromo')}}"
                                        data-current-url="{{ url()->current() }}" disabled>
                                    <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }} (<span>0</span>)
                                </button>
                            @endif
                        </div>
                    </div>
                    <hr/>
                    @if(!empty($goods_promo_list) && count($goods_promo_list))
                        <div class="table-responsive table-responsive-scrollbar-top"></div>
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">№</th>
                                    <th scope="col" class="text-center">{{__('variables.title_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.promo_type')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.start_promo')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.end_promo')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.total_orders_count')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.orders_total_price')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.promo_status')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.edit_table')}}</th>
                                    @if($groupSubRelations->del_to_rec == 1)
                                        <th scope="col"
                                            class="text-center select-all-elements cursor-pointer">{{__('variables.delete_table')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($goods_promo_list as $key => $one_promo)
                                    <tr class="row-id" data-id="{{$one_promo->id}}">
                                        <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                        <td class="text-center">
                                            <span>{{ $one_promo->name ?? '' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{ getPromoType($one_promo->promo_type) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{ $one_promo->data_start ?? '' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{ $one_promo->data_end ?? '' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{--{{ getCountPromoOrders($one_promo->one_c_id) }}--}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{--{{ number_format(getPromoOrdersTotalPrice($one_promo->one_c_id), 2, '.', '') }}--}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-{{ $one_promo->data_end >= \Carbon\Carbon::now()->format('Y-m-d H:i:s') ? 'success' : 'danger' }}">{{ $one_promo->data_end >= \Carbon\Carbon::now()->format('Y-m-d H:i:s') ? 'Activ' : 'Inactiv' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{urlForFunctionLanguage(LANG, Str::slug($one_promo->name).'/editGoodsPromo/'.$one_promo->id)}}"
                                               style="text-decoration: underline;">{{__('variables.edit')}}</a>
                                        </td>
                                        @if($groupSubRelations->del_to_rec == 1)
                                            <td class="text-center">
                                                <input class="form-check-input destroy-element" type="checkbox"
                                                       name="destroy_element"
                                                       value="{{$one_promo->id}}"
                                                       id="destroy-element-{{$one_promo->id}}">
                                                <label class="form-check-label"
                                                       for="destroy-element-{{$one_promo->id}}"></label>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.templates.pagination', ['paginator' => $goods_promo_list])
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
