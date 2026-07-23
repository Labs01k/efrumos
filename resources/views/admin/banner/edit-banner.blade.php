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
                <div class="card-body p-4">
                    <div class="d-lg-flex align-items-center mb-4 gap-3">
                        <div class="position-relative">
                            <h5 class="card-title">{{__('variables.edit_element')}} - <span
                                    class="text-primary">"{{$banner->name ?? '' }}"</span></h5>
                        </div>
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
                                        class="lni lni-cart"></i>{{ __('variables.elements_basket') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($banner_without_lang->name).'/edititem/'.$banner_without_lang->banner_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'bannersCart/cartitems') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-cart"></i>{{ __('variables.elements_basket') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($banner_without_lang->name).'/edititem/'.$banner_without_lang->banner_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'save/'.$banner_without_lang->banner_id.'/'.$lang_to_edit) }}"
                          id="edit-form" data-parent-url="{{$url_for_active_elem}}" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="p_id" value="{{ $banner_without_lang->bannerId->p_id }}">
                        <input type="hidden" name="current_url" value="{{ url()->current() }}">

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-8 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control" id="name"
                                                   value="{{ $banner->name ?? '' }}">
                                        </div>

                                        <div class="mb-3">
                                            <label for="alias"
                                                   class="form-label">{{__('variables.alias_table')}}</label>
                                            <input type="text" name="alias" class="form-control" id="alias"
                                                   value="{{$banner_id->alias ?? '' }}">
                                        </div>

                                        <div class="mb-3">
                                            <label for="short_descr"
                                                   class="form-label">{{__('variables.short_description')}}</label>
                                            <textarea class="form-control" name="short_descr" id="short_descr"
                                                      rows="3">{{ $banner->short_descr ?? '' }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="body" class="form-label">{{__('variables.description')}}</label>
                                            <textarea class="form-control editor" name="body" id="body"
                                                      rows="10">{{ $banner->body ?? '' }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="upload_files"
                                                   class="form-label">{{__('variables.select_file')}}</label>
                                            <input class="form-control" type="file" name="upload_files[]"
                                                   id="upload_files" multiple="">
                                        </div>

                                        @include('admin.templates.uploaded-images', ['upload_path' => 'banners'])

                                        <div class="mb-3">
                                            <label for="link" class="form-label">{{__('variables.link')}}</label>
                                            <input type="text" name="link" class="form-control" id="link"
                                                   value="{{$banner->link ?? '' }}">
                                        </div>

                                        <div class="mb-3">
                                            <label for="link_name"
                                                   class="form-label">{{__('variables.link_name')}}</label>
                                            <input type="text" name="link_name" class="form-control" id="link_name"
                                                   value="{{$banner->link_name ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="row g-3">
                                            @if(!empty($lang_list) && count($lang_list))
                                                <div class="col-12">
                                                    <label for="lang"
                                                           class="form-label">{{__('variables.lang')}}</label>
                                                    <select class="form-select" name="lang" id="lang">
                                                        @foreach($lang_list as $lang_key => $one_lang)
                                                            <option
                                                                value="{{$lang_key}}" {{$lang_key == $lang_to_edit ? 'selected' : ''}}>{{Str::ucfirst($one_lang)}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif

                                            <div class="col-12">
                                                <label for="percent"
                                                       class="form-label">{{ __('variables.discount') }}</label>
                                                <input type="text" name="percent" class="form-control" id="percent"
                                                       value="{{$banner_id->percent ?? '' }}">
                                            </div>

                                            <div class="col-12">
                                                <label for="color_code"
                                                       class="form-label">{{ __('variables.color_code') }}</label>
                                                <input type="text" name="color_code" class="form-control"
                                                       id="color_code" value="{{$banner_id->color_code ?? '' }}"
                                                       placeholder="#FFFFFF">
                                            </div>

                                            <div class="col-12">
                                                <label for="color_code_button"
                                                       class="form-label">{{ __('variables.color_code_button') }}</label>
                                                <input type="text" name="color_code_button" class="form-control"
                                                       id="color_code_button" value="{{$banner_id->color_code_button ?? '' }}" placeholder="#FFFFFF">
                                            </div>

                                            @if($groupSubRelations->save == 1)
                                                <button class="btn btn-success"
                                                        onclick="saveForm(this)"
                                                        data-form-id="edit-form">{{__('variables.save_it')}}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@stop
