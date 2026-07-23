@extends('admin.app')

@section('content')

    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    @include('admin.templates.breadcrumbs')
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-lg-flex align-items-center mb-4 gap-3">
                                <div class="ms-auto">
                                    @if($groupSubRelations->new == 1)
                                        <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                           class="btn btn-primary mt-2 mt-lg-0"><i
                                                    class="lni lni-list"></i>{{ __('variables.elements_list') }}
                                        </a>
                                        <a href="{{ urlForFunctionLanguage($lang, 'createBrand/createitem') }}"
                                           class="btn btn-primary mt-2 mt-lg-0"><i
                                                    class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                        </a>
                                        <a href="{{ urlForFunctionLanguage($lang, 'brandsCart/cartitems') }}"
                                           class="btn btn-primary mt-2 mt-lg-0"><i
                                                    class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                        </a>
                                    @else
                                        <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                           class="btn btn-primary mt-2 mt-lg-0"><i
                                                    class="lni lni-list"></i>{{ __('variables.elements_list') }}
                                        </a>

                                        <a href="{{ urlForFunctionLanguage($lang, 'brandsCart/cartitems') }}"
                                           class="btn btn-primary mt-2 mt-lg-0"><i
                                                    class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                        </a>
                                    @endif
                                    @if(!empty($deleted_brand_list))
                                        <button class="btn btn-success btn-sm mt-2 mt-lg-0 restore-all-elements"
                                                data-url="{{urlForFunctionLanguage($lang, 'restoreBrand/restoreBrand')}}"
                                                data-current-url="{{ url()->current() }}" disabled>
                                            <i class="fas fa-trash"></i> {{ __('variables.restore_selected') }}
                                            (<span>0</span>)
                                        </button>
                                        @if($groupSubRelations->del_to_rec == 1)
                                            <button class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                                    data-url="{{urlForFunctionLanguage($lang, 'destroyBrandFromCart/destroyBrandFromCart')}}"
                                                    data-current-url="{{ url()->current() }}" disabled>
                                                <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }}
                                                (<span>0</span>)
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @if(!empty($deleted_brand_list) && count($deleted_brand_list))
                                <div class="table-responsive">
                                    <table class="table mb-0 table-hover">
                                        <thead>
                                        <tr>
                                            <th scope="col" class="text-center">№</th>
                                            <th scope="col" class="text-center">{{__('variables.title_table')}}</th>
                                            <th scope="col" class="text-center select-all-restore-elements"
                                                role="button">{{__('variables.reestablish_table')}}</th>
                                            @if($groupSubRelations->del_to_rec == 1)
                                                <th scope="col"
                                                    class="text-center select-all-elements"
                                                    role="button">{{__('variables.delete_table')}}</th>
                                            @endif
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($deleted_brand_list as $deleted_brand)
                                            <tr id="{{$deleted_brand->id}}">
                                                <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                                <td class="text-center">
                                                    <span>{{!empty(IfHasName($deleted_brand->id, $lang_id, 'goods_brand')) ? IfHasName($deleted_brand->id, $lang_id, 'goods_brand') : __('variables.another_name')}}</span>
                                                </td>
                                                <td class="text-center">
                                                    <input class="form-check-input restore-element" type="checkbox"
                                                           name="restore_element"
                                                           value="{{$deleted_brand->id}}"
                                                           id="restore-element-{{$deleted_brand->id}}">
                                                    <label class="form-check-label"
                                                           for="restore-element-{{$deleted_brand->id}}"></label>
                                                </td>
                                                @if($groupSubRelations->del_to_rec == 1)
                                                    <td class="text-center">
                                                        <input class="form-check-input destroy-element" type="checkbox"
                                                               name="destroy_element"
                                                               value="{{$deleted_brand->id}}"
                                                               id="destroy-element-{{$deleted_brand->id}}">
                                                        <label class="form-check-label"
                                                               for="destroy-element-{{$deleted_brand->id}}"></label>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                @include('admin.templates.empty-list')
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@stop