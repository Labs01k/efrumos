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
                                <a href="{{ urlForLanguage($lang, 'goodsparameters/'.$goods_subject_id->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.parameters_list') }}</a>
                                <a href="{{ urlForLanguage($lang, 'creategoodsparameter/'.$goods_subject_id->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>{{ __('variables.add_parameter') }}
                                </a>
                                <a href="{{ urlForLanguage($lang, 'goodsparametercart/'.$goods_subject_id->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                            @else
                                <a href="{{ urlForLanguage($lang, 'goodsparameters/'.$goods_subject_id->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.parameters_list') }}</a>
                                <a href="{{ urlForLanguage($lang, 'goodsparametercart/'.$goods_subject_id->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                            @endif
                            @if($groupSubRelations->del_to_rec == 1)
                                <button class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                        data-url="{{urlForFunctionLanguage($lang, 'destroyGoodsParameterToCart/destroyGoodsParameterToCart')}}"
                                        data-current-url="{{ url()->current() }}" disabled>
                                    <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }} (<span>0</span>)
                                </button>
                            @endif
                        </div>
                    </div>
                    <hr/>
                    @if(!empty($goods_parameter))
                        <div class="table-responsive table-responsive-scrollbar-top"></div>
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">№</th>
                                    <th scope="col" class="text-center">{{__('variables.title_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.edit_table')}}</th>
                                    @if($groupSubRelations->active == 1)
                                        <th scope="col" class="text-center">{{__('variables.active_table')}}</th>
                                    @endif
                                    <th scope="col" class="text-center">{{__('variables.position_table')}}</th>
                                    @if($groupSubRelations->del_to_rec == 1)
                                        <th scope="col"
                                            class="text-center select-all-elements cursor-pointer">{{__('variables.delete_table')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody class="sort-table" data-url="{{ $url_for_active_elem }}" data-action="parameter">
                                @foreach($goods_parameter as $key => $one_goods_parameter)
                                    <tr class="row-id" data-id="{{$one_goods_parameter->goods_parametr_id}}">
                                        <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                        <td class="text-center">
                                            <span>
                                                {{!empty(IfHasName($one_goods_parameter->goods_parametr_id, $lang_id, 'goods_parametr')) ? IfHasName($one_goods_parameter->goods_parametr_id, $lang_id, 'goods_parametr') : __('variables.another_name')}}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @foreach($lang_list as $lang_key => $one_lang)
                                                <a href="{{urlForLanguage($lang, 'editgoodsparameter/'.$one_goods_parameter->goods_parametr_id.'/'.$lang_key)}}"
                                                   class="btn btn-sm btn-{{ !empty(IfHasName($one_goods_parameter->goods_parametr_id, $lang_key, 'goods_parametr')) ? 'success' : 'danger' }}">{{Str::ucfirst($one_lang)}}</a>
                                            @endforeach
                                        </td>
                                        <td class="text-center">
                                            <div class="form-switch">
                                                <input class="form-check-input change-active" type="checkbox"
                                                       data-active="{{$one_goods_parameter->parametrId->active}}"
                                                       data-element-id="{{$one_goods_parameter->parametrId->id}}"
                                                       data-action="parameter"
                                                       id="switch-active-{{$one_goods_parameter->parametrId->id}}"
                                                       data-url="{{$url_for_active_elem}}" {{$one_goods_parameter->parametrId->active == 1 ? 'checked' : ''}}>
                                                <label class="form-check-label"
                                                       for="switch-active-{{$one_goods_parameter->parametrId->id}}"></label>
                                            </div>
                                        </td>
                                        <td class="position cursor-pointer text-center"><i class="lni lni-move"></i>
                                        </td>
                                        @if($groupSubRelations->del_to_rec == 1)
                                            <td class="text-center">
                                                <input class="form-check-input destroy-element" type="checkbox"
                                                       name="destroy_element"
                                                       value="{{$one_goods_parameter->goods_parametr_id}}"
                                                       id="destroy-element-{{$one_goods_parameter->goods_parametr_id}}">
                                                <label class="form-check-label"
                                                       for="destroy-element-{{$one_goods_parameter->goods_parametr_id}}"></label>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.templates.pagination', ['paginator' => $goods_parameter_id])
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
