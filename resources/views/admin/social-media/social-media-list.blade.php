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
                                <a href="{{ urlForFunctionLanguage($lang, 'createitem/createitem') }}"
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
                                        data-url="{{urlForFunctionLanguage($lang, 'destroyItem/destroyItem')}}"
                                        data-current-url="{{ url()->current() }}" disabled>
                                    <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }} (<span>0</span>)
                                </button>
                            @endif
                        </div>
                    </div>
                    <hr/>
                    @if(!empty($social_list) && count($social_list))
                        <div class="table-responsive table-responsive-scrollbar-top"></div>
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">№</th>
                                    <th scope="col" class="text-center">{{__('variables.photo')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.title_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.link')}}</th>
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
                                @foreach($social_list as $key => $one_social_item)
                                    <tr class="row-id" data-id="{{$one_social_item->id}}">
                                        <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                        <td class="text-center">
                                            @if(($one_social_item->img) && file_exists('upfiles/social-media/' . $one_social_item->img))
                                                <a href="{{ asset('upfiles/social-media') }}/{{$one_social_item->img ?? ''}}"
                                                   data-fancybox="gallery">
                                                    <img src="{{ asset('upfiles/social-media') }}/{{showImg($one_social_item->img) ?? ''}}"
                                                         class="product-sm-img">
                                                </a>
                                            @else
                                                <img src="{{asset('admin-assets/images/no-image.png')}}"
                                                     alt="no-image" class="product-sm-img"
                                                     title="No image">
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span>{{ $one_social_item->name ?? __('variables.another_name') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{ $one_social_item->link ?? __('variables.do_not_exist') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{urlForFunctionLanguage($lang, Str::slug($one_social_item->name).'/edititem/'.$one_social_item->id.'/'.$lang_id)}}"
                                                   class="btn btn-sm btn-success">{{ __('variables.edit') }}</a>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-switch">
                                                <input class="form-check-input change-active" type="checkbox"
                                                       data-active="{{$one_social_item->active}}"
                                                       data-element-id="{{$one_social_item->id}}"
                                                       data-action="item"
                                                       id="switch-active-{{$one_social_item->id}}"
                                                       data-url="{{$url_for_active_elem}}" {{$one_social_item->active == 1 ? 'checked' : ''}}>
                                                <label class="form-check-label"
                                                       for="switch-active-{{$one_social_item->id}}"></label>
                                            </div>
                                        </td>
                                        <td class="position cursor-pointer text-center"><i class="lni lni-move"></i>
                                        @if($groupSubRelations->del_to_rec == 1)
                                            <td class="text-center">
                                                <input class="form-check-input destroy-element" type="checkbox"
                                                       name="destroy_element"
                                                       value="{{ $one_social_item->id ?? '' }}"
                                                       id="destroy-element-{{ $one_social_item->id ?? '' }}">
                                                <label class="form-check-label"
                                                       for="destroy-element-{{ $one_social_item->id ?? '' }}"></label>
                                            </td>

                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.templates.pagination', ['paginator' => $social_list])
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

