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
                            <h5 class="card-title">{{__('variables.add_element')}}</h5>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                @if(request()->segment(5) == '' || request()->segment(4) == 'createMenu')
                                    <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                    <a href="{{ urlForFunctionLanguage($lang, 'createMenu/createmenu') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                    </a>
                                    <a href="{{ urlForFunctionLanguage($lang, 'menuCart/menucart') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                    </a>
                                @else
                                    <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                    <a href="{{ urlForLanguage($lang, 'createmenu') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                    </a>
                                    <a href="{{ urlForLanguage($lang, 'menucart') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                    </a>
                                @endif
                            @else
                                @if(request()->segment(5) == '' || request()->segment(4) == 'createMenu')
                                    <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                    <a href="{{ urlForFunctionLanguage($lang, 'menuCart/menucart') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                    </a>
                                @else
                                    <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                    <a href="{{ urlForFunctionLanguage($lang, 'menuCart/menucart') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST" action="{{ urlForLanguage($lang, 'savemenu') }}" id="add-form"
                          enctype="multipart/form-data">

                        @csrf

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-8 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control" id="name" autofocus>
                                        </div>
                                        <div class="mb-3">
                                            <label for="alias"
                                                   class="form-label">{{__('variables.alias_table')}}</label>
                                            <input type="text" name="alias" class="form-control" id="alias">
                                        </div>
                                        <div class="mb-3">
                                            <label for="short_descr"
                                                   class="form-label">{{__('variables.short_description')}}</label>
                                            <textarea class="form-control" name="short_descr" id="short_descr"
                                                      rows="3"></textarea>
                                        </div>
                                        <div class="mb-3 show-ckeditor">
                                            <label for="body" class="form-label">{{__('variables.description')}}</label>
                                            <textarea class="form-control editor" name="body" id="body"
                                                      rows="10"></textarea>
                                        </div>

                                        <div class="mb-3">
                                             <label for="upload_files" class="form-label">{{__('variables.select_file')}}</label>
                                             <input class="form-control" type="file" name="upload_files[]"
                                                    id="upload_files" multiple="">
                                         </div>

                                         @include('admin.templates.upload-new-images')

                                        <div class="position-relative d-flex justify-content-between mt-5">
                                            <div>
                                                <h5 class="card-title">{{__('variables.seo_settings')}}</h5>
                                            </div>
                                            <div class="show-seo-settings cursor-pointer">
                                                <h5 class="card-title text-primary font-30"><i
                                                        class="lni lni-arrow-down-circle"></i></h5>
                                            </div>
                                        </div>
                                        <hr>

                                        <div class="hide-seo-settings">
                                            <div class="mb-3">
                                                <label for="page_title"
                                                       class="form-label">{{__('variables.general_title_page')}}</label>
                                                <input type="text" name="page_title" class="form-control"
                                                       id="page_title">
                                            </div>

                                            <div class="mb-3">
                                                <label for="h1_title"
                                                       class="form-label">{{__('variables.h1_title_page')}}</label>
                                                <input type="text" name="h1_title" class="form-control" id="h1_title">
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_title"
                                                       class="form-label">{{__('variables.meta_title_page')}}</label>
                                                <input type="text" name="meta_title" class="form-control"
                                                       id="meta_title">
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_keywords"
                                                       class="form-label">{{__('variables.meta_keywords_page')}}</label>
                                                <input type="text" name="meta_keywords" class="form-control"
                                                       id="meta_keywords">
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_description"
                                                       class="form-label">{{__('variables.meta_description_page')}}</label>
                                                <input type="text" name="meta_description" class="form-control"
                                                       id="meta_description">
                                            </div>
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
                                                                value="{{$lang_key}}" {{$lang_key == $lang_id ? 'selected' : ''}}>{{Str::ucfirst($one_lang)}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                            <div class="col-12">
                                                <label for="p_id"
                                                       class="form-label">{{__('variables.p_id_name')}}</label>
                                                <select class="form-select" name="p_id" id="p_id">
                                                    <option value="0">{{__('variables.home')}}</option>
                                                    {!! SelectTreeGoodsPage($lang_id, 0, $curr_page_id) !!}
                                                </select>
                                            </div>
                                            {{--<div class="col-12">
                                                <label for="page_type"
                                                       class="form-label">{{__('variables.parameter_type')}}</label>
                                                <select class="form-select" name="page_type" id="page_type">
                                                    <option value="page">{{__('variables.html_page')}}</option>
                                                    <option value="link">{{__('variables.link')}}</option>
                                                </select>
                                            </div>--}}

                                            @if(!empty($goods_subject_list) && count($goods_subject_list))
                                                <div class="col-12">
                                                    <label for="goods_subject_id"
                                                           class="form-label">{{ __('variables.subject_element') }}</label>
                                                    <select class="form-select" name="goods_subject_id"
                                                            id="goods_subject_id">
                                                        <option value="">{{ __('variables.select_item') }}</option>
                                                        @foreach($goods_subject_list as $one_goods_subject)
                                                            <option
                                                                value="{{ $one_goods_subject->id ?? '' }}">{{ $one_goods_subject->itemByLang->name ?? '' }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif

                                            <div class="col-12">
                                                <label for="link" class="form-label">{{__('variables.link')}}</label>
                                                <input type="text" name="link" class="form-control" id="link">
                                            </div>

                                            @if($groupSubRelations->save == 1)
                                                <div class="col-12">
                                                    <div class="d-grid">
                                                        <button class="btn btn-success"
                                                                onclick="saveForm(this)"
                                                                data-form-id="add-form">{{__('variables.save_it')}}
                                                        </button>
                                                    </div>
                                                </div>
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

