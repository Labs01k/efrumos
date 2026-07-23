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
                                           placeholder="{{ __('variables.search_object_it') }}"
                                           value="{{ $concrete_search_key ?? '' }}"
                                           title="{{ __('variables.orders_products') }}">
                                    <button type="submit" class="input-group-text btn-success btn-search"><i
                                            class="bx bx-search"></i></button>
                                </div>
                            </form>
                        </div>
                        <div class="ms-auto">
                            <a href="{{ is_null($goods_subject_id) ? urlForFunctionLanguage($lang, '') : urlForLanguage($lang, 'memberslist') }}"
                               class="btn btn-primary mt-2 mt-lg-0"><i
                                    class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            @if(!empty($child_goods_item_list) && count($child_goods_item_list))
                                @if($groupSubRelations->del_to_rec == 1)
                                    <button class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                            data-url="{{urlForFunctionLanguage($lang, 'destroyGoodsItemToCart/destroyGoodsItemToCart')}}"
                                            data-current-url="{{ url()->current() }}" disabled>
                                        <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }}
                                        (<span>0</span>)
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                    <hr/>
                    @if(!empty($child_goods_item_list) && count($child_goods_item_list))
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">№</th>
                                    <th scope="col" class="text-center">{{__('variables.photo')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.title_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.1c_code')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.articol')}}</th>
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
                                <tbody class="sort-table" data-url="{{ $url_for_active_elem }}" data-action="item">
                                @foreach($child_goods_item_list as $key => $one_goods_item_list)
                                    <tr class="row-id" data-id="{{$one_goods_item_list->id}}">
                                        <th class="text-center"
                                            scope="row">{{ $child_goods_item_list->firstItem() + $loop->index }}</th>
                                        <td class="text-center">
                                            @if(!empty($one_goods_item_list->goodsOnePhoto->img) && file_exists('upfiles/goods-items/' . $one_goods_item_list->goodsOnePhoto->img))
                                                <a href="{{ asset('upfiles/goods-items') }}/{{$one_goods_item_list->goodsOnePhoto->img ?? ''}}"
                                                   data-fancybox="gallery">
                                                    <img
                                                        src="{{ asset('upfiles/goods-items/m') }}/{{ showImg($one_goods_item_list->goodsOnePhoto->img) ?? ''}}"
                                                        class="product-sm-img">
                                                </a>
                                            @else
                                                <img src="{{asset('admin-assets/images/no-image.png')}}"
                                                     alt="no-image" class="product-sm-img"
                                                     title="No image">
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span>{{!empty(IfHasName($one_goods_item_list->goods_item_id, $lang_id, 'goods_item')) ? IfHasName($one_goods_item_list->goods_item_id, $lang_id, 'goods_item') : __('variables.another_name')}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{ $one_goods_item_list->goodsItemId ? $one_goods_item_list->goodsItemId->one_c_code : __('variables.another_name')}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{ $one_goods_item_list->goodsItemId ? $one_goods_item_list->goodsItemId->articol : __('variables.another_name')}}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($one_goods_item_list->goodsItemId && $one_goods_item_list->goodsItemId->getSubjectId)
                                                @foreach($lang_list as $lang_key => $one_lang)
                                                    <a href="{{urlForFunctionLanguage($lang, $one_goods_item_list->goodsItemId->getSubjectId->alias.'/editgoodsitem/'.$one_goods_item_list->goods_item_id.'/'.$lang_key)}}"
                                                       class="btn btn-sm btn-{{ !empty(IfHasName($one_goods_item_list->goods_item_id, $lang_key, 'goods_item')) ? 'success' : 'danger' }}">{{Str::ucfirst($one_lang)}}</a>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="form-switch">
                                                <input class="form-check-input change-active" type="checkbox"
                                                       data-active="{{$one_goods_item_list->goodsItemId->active}}"
                                                       data-element-id="{{$one_goods_item_list->goodsItemId->id}}"
                                                       data-action="item"
                                                       id="switch-active-{{$one_goods_item_list->goods_item_id}}"
                                                       data-url="{{$url_for_active_elem}}" {{$one_goods_item_list->goodsItemId->active == 1 ? 'checked' : ''}}>
                                                <label class="form-check-label"
                                                       for="switch-active-{{$one_goods_item_list->goods_item_id}}"></label>
                                            </div>
                                        </td>
                                        <td class="position cursor-pointer text-center"><i class="lni lni-move"></i>
                                        </td>
                                        @if($groupSubRelations->del_to_rec == 1)
                                            <td class="text-center">
                                                <input class="form-check-input destroy-element" type="checkbox"
                                                       name="destroy_element"
                                                       value="{{$one_goods_item_list->goods_item_id}}"
                                                       id="destroy-element-{{$one_goods_item_list->goods_item_id}}">
                                                <label class="form-check-label"
                                                       for="destroy-element-{{$one_goods_item_list->goods_item_id}}"></label>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.templates.pagination', ['paginator' => $child_goods_item_list])
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
