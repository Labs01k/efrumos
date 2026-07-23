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
                                <a href="{{ urlForFunctionLanguage($lang, '')}}"
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
                                    <th scope="col" class="text-center">{{__('variables.banner_mob')}}</th>
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
                                <tbody class="sort-table" data-url="{{ $url_for_active_elem }}">
                                @foreach($banner_list as $key => $banner)
                                    <tr class="row-id" data-id="{{$banner->banner_top_id}}">
                                        <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                        <td class="text-center">
                                            <span>{{ !empty(IfHasName($banner->banner_top_id, $lang_id, 'banner_top')) ? IfHasName($banner->banner_top_id, $lang_id, 'banner_top') : __('variables.another_name')}}</span>
                                        </td>
                                        <td class="text-center">
                                            @foreach($lang_list as $lang_key => $one_lang)
                                                <a href="{{urlForFunctionLanguage($lang, Str::slug($banner->name).'/edititem/'.$banner->banner_top_id.'/'.$lang_key)}}?mob_slide=1"
                                                   class="btn btn-sm btn-{{ !empty(IfHasImgMob($banner->banner_top_id, $lang_key, 'banner_top')) ? 'success' : 'danger' }}">{{__('variables.banner')}} - ({{Str::lower($one_lang)}})</a>
                                            @endforeach
                                        </td>
                                        <td class="text-center">
                                            @foreach($lang_list as $lang_key => $one_lang)
                                                <a href="{{urlForFunctionLanguage($lang, Str::slug($banner->name).'/edititem/'.$banner->banner_top_id.'/'.$lang_key)}}"
                                                   class="btn btn-sm btn-{{ !empty(IfHasName($banner->banner_top_id, $lang_key, 'banner_top')) ? 'success' : 'danger' }}">{{Str::ucfirst($one_lang)}}</a>
                                            @endforeach
                                        </td>
                                        <td class="text-center">
                                            <div class="form-switch">
                                                <input class="form-check-input change-active" type="checkbox"
                                                       data-active="{{$banner->bannerTopId->active}}"
                                                       data-element-id="{{$banner->banner_top_id}}"
                                                       data-action="main-active"
                                                       id="switch-active-{{$banner->banner_top_id}}"
                                                       data-url="{{$url_for_active_elem}}" {{$banner->bannerTopId->active == 1 ? 'checked' : ''}}>
                                                <label class="form-check-label"
                                                       for="switch-active-{{$banner->banner_top_id}}"></label>
                                            </div>
                                        </td>
                                        <td class="position cursor-pointer text-center"><i class="lni lni-move"></i>
                                        </td>
                                        @if($groupSubRelations->del_to_rec == 1)
                                            <td class="text-center">
                                                <input class="form-check-input destroy-element" type="checkbox"
                                                       name="destroy_element"
                                                       value="{{$banner->banner_top_id}}"
                                                       id="destroy-element-{{$banner->banner_top_id}}">
                                                <label class="form-check-label"
                                                       for="destroy-element-{{$banner->banner_top_id}}"></label>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.templates.pagination', ['paginator' => $slider_list_ids])
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
