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
                                <a href="{{ urlForFunctionLanguage($lang, 'createBanner/createitem') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, 'bannersCart/cartitems') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'bannersCart/cartitems') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                            @endif
                            @if($groupSubRelations->del_to_rec == 1)
                                <button class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                        data-url="{{urlForFunctionLanguage($lang, 'destroyBannerToCart/destroyBannerToCart')}}"
                                        data-current-url="{{ url()->current() }}" disabled>
                                    <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }} (<span>0</span>)
                                </button>
                            @endif
                        </div>
                    </div>
                    <hr/>
                    @if(!empty($banner_list) && count($banner_list))
                        <div class="table-responsive table-responsive-scrollbar-top"></div>
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">№</th>
                                    <th scope="col" class="text-center">{{__('variables.title_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.alias_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.edit_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.date_table')}}</th>
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
                                <tbody class="sort-table" data-url="{{ $url_for_active_elem }}">
                                @foreach($banner_list as $key => $banner)
                                    <tr class="row-id" data-id="{{$banner->banner_id}}">
                                        <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                        <td class="text-center">
                                            <a href="{{urlForFunctionLanguage($lang, $banner->bannerId->id.'/memberslist')}}"
                                               class="text-decoration-underline">
                                                {{ !empty(IfHasName($banner->banner_id, $lang_id, 'banner')) ? IfHasName($banner->banner_id, $lang_id, 'banner') : __('variables.another_name')}}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span>{{ $banner->bannerId->alias ?? '' }}</span>
                                        </td>
                                        <td class="text-center">
                                            @foreach($lang_list as $lang_key => $one_lang)
                                                <a href="{{urlForFunctionLanguage($lang, Str::slug($banner->name).'/edititem/'.$banner->banner_id.'/'.$lang_key)}}"
                                                   class="btn btn-sm btn-{{ !empty(IfHasName($banner->banner_id, $lang_key, 'banner')) ? 'success' : 'danger' }}">{{Str::ucfirst($one_lang)}}</a>
                                            @endforeach
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-secondary">{{ getDefaultDateFormatAdmin($banner->bannerId->created_at) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-switch">
                                                <input class="form-check-input change-active" type="checkbox"
                                                       data-active="{{$banner->bannerId->active}}"
                                                       data-element-id="{{$banner->banner_id}}"
                                                       data-action="main-active"
                                                       id="switch-active-{{$banner->banner_id}}"
                                                       data-url="{{$url_for_active_elem}}" {{$banner->bannerId->active == 1 ? 'checked' : ''}}>
                                                <label class="form-check-label"
                                                       for="switch-active-{{$banner->banner_id}}"></label>
                                            </div>
                                        </td>
                                        <td class="position cursor-pointer text-center"><i class="lni lni-move"></i>
                                        </td>
                                        @if(IfHasChildActive($banner->banner_id, 'banner')->isEmpty())
                                            @if($groupSubRelations->del_to_rec == 1)
                                                <td class="text-center">
                                                    <input class="form-check-input destroy-element" type="checkbox"
                                                           name="destroy_element"
                                                           value="{{$banner->banner_id}}"
                                                           id="destroy-element-{{$banner->banner_id}}">
                                                    <label class="form-check-label"
                                                           for="destroy-element-{{$banner->banner_id}}"></label>
                                                </td>
                                            @endif
                                        @else
                                            <td class="text-center">
                                                <span>{{__('variables.delete_inner_modules')}}</span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.templates.pagination', ['paginator' => $banner_list_ids])
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
