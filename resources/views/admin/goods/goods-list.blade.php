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
                                           placeholder="{{ __('variables.search_object_it') }}" title="{{ __('variables.orders_products') }}">
                                    <button type="submit" class="input-group-text btn-success btn-search"><i
                                                class="bx bx-search"></i></button>
                                </div>
                            </form>
                        </div>

                        <div class="col">
                            <div class="btn-group" role="group" aria-label="Basic example">
                                <a href="{{ urlForFunctionLanguage($lang, 'requestGoodsFrom1C/requestGoodsFrom1C') }}"
                                   class="btn btn-secondary mt-2 mt-lg-0">{{ __('variables.request_1c_goods') }}</a>
                            </div>
                        </div>
                        <div class="col btn-export">
                            <a href="{{ urlForFunctionLanguage($lang, 'exportExcel/exportExcel') }}"
                               class="btn btn-outline-primary px-4 btn-block"><i class="lni lni-download" style="font-size: 14px;"></i>{{ __('variables.export_file') }}</a>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'createGoodsSubject/creategoodssubject') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>{{ __('variables.add_subject') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, 'goodsSubjectCart/goodssubjectcart') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'goodsSubjectCart/goodssubjectcart') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                            @endif
                            @if($groupSubRelations->del_to_rec == 1)
                                <button class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                        data-url="{{urlForFunctionLanguage($lang, 'destroygoodssubjecttoCart/destroyGoodsSubjectToCart')}}"
                                        data-current-url="{{ url()->current() }}" disabled>
                                    <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }} (<span>0</span>)
                                </button>
                            @endif
                        </div>
                    </div>
                    <hr/>
                    @if(!empty($goods_subject_list) && count($goods_subject_list))
                        <div class="table-responsive table-responsive-scrollbar-top"></div>
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">№</th>
                                    <th scope="col" class="text-center">{{__('variables.title_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.parameters')}}</th>
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
                                <tbody class="sort-table" data-url="{{ $url_for_active_elem }}" data-action="subject">
                                @foreach($goods_subject_list as $key => $one_goods_subject_list)
                                    <tr class="row-id" data-id="{{$one_goods_subject_list->goods_subject_id}}">
                                        <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                        <td class="text-center">
                                            <a href="{{urlForFunctionLanguage($lang, $one_goods_subject_list->goodsSubjectId->alias.'/memberslist')}}">{{!empty(IfHasName($one_goods_subject_list->goods_subject_id, $lang_id, 'goods_subject')) ? IfHasName($one_goods_subject_list->goods_subject_id, $lang_id, 'goods_subject') : __('variables.another_name')}}</a>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{urlForFunctionLanguage($lang, $one_goods_subject_list->goodsSubjectId->alias.'/goodsparameters/'.$one_goods_subject_list->goods_subject_id)}}">{{__('variables.parameters')}}</a>
                                        </td>
                                        <td class="text-center">
                                            @foreach($lang_list as $lang_key => $one_lang)
                                                <a href="{{urlForFunctionLanguage($lang, $one_goods_subject_list->goodsSubjectId->alias.'/editgoodssubject/'.$one_goods_subject_list->goods_subject_id.'/'.$lang_key)}}"
                                                   class="btn btn-sm btn-{{ !empty(IfHasName($one_goods_subject_list->goods_subject_id, $lang_key, 'goods_subject')) ? 'success' : 'danger' }}">{{Str::ucfirst($one_lang)}}</a>
                                            @endforeach
                                        </td>
                                        <td class="text-center">
                                            <div class="form-switch">
                                                <input class="form-check-input change-active" type="checkbox"
                                                       data-active="{{$one_goods_subject_list->goodsSubjectId->active}}"
                                                       data-element-id="{{$one_goods_subject_list->goodsSubjectId->id}}"
                                                       data-action="subject"
                                                       id="switch-active-{{$one_goods_subject_list->goods_subject_id}}"
                                                       data-url="{{$url_for_active_elem}}" {{$one_goods_subject_list->goodsSubjectId->active == 1 ? 'checked' : ''}}>
                                                <label class="form-check-label"
                                                       for="switch-active-{{$one_goods_subject_list->goods_subject_id}}"></label>
                                            </div>
                                        </td>
                                        <td class="position cursor-pointer text-center"><i class="lni lni-move"></i>
                                        </td>
                                        @if($groupSubRelations->del_to_rec == 1)
                                            @if(IfHasChildActive($one_goods_subject_list->goods_subject_id, 'goods_subject')->isEmpty() && CheckIfSubjectHasItems('goods', $one_goods_subject_list->goods_subject_id)->isEmpty())
                                                <td class="text-center">
                                                    <input class="form-check-input destroy-element" type="checkbox"
                                                           name="destroy_element"
                                                           value="{{$one_goods_subject_list->goods_subject_id}}"
                                                           id="destroy-element-{{$one_goods_subject_list->goods_subject_id}}">
                                                    <label class="form-check-label"
                                                           for="destroy-element-{{$one_goods_subject_list->goods_subject_id}}"></label>
                                                </td>
                                            @else
                                                <td class="text-center">
                                                    <span>{{__('variables.delete_inner_modules')}}</span>
                                                </td>
                                            @endif
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.templates.pagination', ['paginator' => $goods_subject_id_list])

                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
