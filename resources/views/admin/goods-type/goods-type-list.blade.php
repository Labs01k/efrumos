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
                                <a href="{{ urlForFunctionLanguage($lang, 'createGoodsType/createGoodsType') }}"
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
                                        data-url="{{urlForFunctionLanguage($lang, 'destroyGoodsType/destroyGoodsType')}}"
                                        data-current-url="{{ url()->current() }}" disabled>
                                    <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }} (<span>0</span>)
                                </button>
                            @endif
                        </div>
                    </div>
                    <hr/>
                    @if(!empty($goods_type_list) && count($goods_type_list))
                        <div class="table-responsive tables-white-space-normal">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">{{__('variables.id_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.title_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.edit_table')}}</th>
                                    @if($groupSubRelations->del_to_rec == 1)
                                        <th scope="col"
                                            class="text-center select-all-elements cursor-pointer">{{__('variables.delete_table')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody data-url="{{ $url_for_active_elem }}">
                                @foreach($goods_type_list as $one_goods_type)
                                    <tr class="row-id" data-id="{{$one_goods_type->id}}">
                                        <th class="text-center" scope="row">{{ $goods_type_list->firstItem() + $loop->index }}</th>
                                        <td class="text-center">
                                            <span>{{ $one_goods_type->itemByLang->name ?? '' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex ml-labels-buttons justify-content-center">
                                                @foreach($lang_list as $lang_key => $one_lang)
                                                    <a href="{{urlForFunctionLanguage($lang, Str::slug($one_goods_type->itemByLang->name).'/editGoodsType/'.$one_goods_type->id.'/'.$lang_key)}}"
                                                       class="btn btn-sm btn-{{ !empty(IfHasName($one_goods_type->id, $lang_key, 'goods_type')) ? 'success' : 'danger' }}">{{Str::ucfirst($one_lang)}}</a>
                                                @endforeach
                                            </div>
                                        </td>
                                        @if($groupSubRelations->del_to_rec == 1 || $groupSubRelations->del_from_rec == 1)
                                            <td class="text-center">
                                                <input class="form-check-input destroy-element" type="checkbox"
                                                       name="destroy_element"
                                                       value="{{$one_goods_type->id}}"
                                                       id="destroy-element-{{$one_goods_type->id}}">
                                                <label class="form-check-label"
                                                       for="destroy-element-{{$one_goods_type->id}}"></label>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.templates.pagination', ['paginator' => $goods_type_list])
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>

@stop
