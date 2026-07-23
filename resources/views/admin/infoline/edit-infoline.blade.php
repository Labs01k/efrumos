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
                                    class="text-primary">"{{$info_line->name ?? '' }}"</span></h5>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'createInfoLine/createinfoline') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, 'infoLineCart/infolinecart') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-cart"></i>{{ __('variables.elements_basket') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($info_line_without_lang->name).'/editinfoline/'.$info_line_without_lang->info_line_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'infoLineCart/infolinecart') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-cart"></i>{{ __('variables.elements_basket') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($info_line_without_lang->name).'/editinfoline/'.$info_line_without_lang->info_line_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'saveinfoline/'.$info_line_without_lang->info_line_id.'/'.$lang_to_edit) }}"
                          id="edit-form" data-parent-url="{{$url_for_active_elem}}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-8 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control" id="name"
                                                   value="{{$info_line->name ?? '' }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="alias"
                                                   class="form-label">{{__('variables.alias_table')}}</label>
                                            <input type="text" name="alias" class="form-control" id="alias"
                                                   value="{{$info_line_id->alias ?? '' }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="body" class="form-label">{{__('variables.description')}}</label>
                                            <textarea class="form-control editor" name="body" id="body"
                                                      rows="10">{{ $info_line->descr ?? '' }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="upload_files"
                                                   class="form-label">{{__('variables.select_file')}}</label>
                                            <input class="form-control" type="file" name="upload_files[]"
                                                   id="upload_files">
                                        </div>

                                        @include('admin.templates.uploaded-image', ['upload_path' => 'info-line', 'item' => $info_line_id])

                                        <div class="position-relative d-flex justify-content-between mt-5">
                                            <div>
                                                <h5 class="card-title">{{__('variables.seo_settings')}}</h5>
                                            </div>
                                            <div class="show-seo-settings" role="button">
                                                <h5 class="card-title text-primary font-30"><i
                                                        class="fadeIn animated bx bx-down-arrow-circle"></i></h5>
                                            </div>
                                        </div>
                                        <hr>

                                        <div class="hide-seo-settings">
                                            <div class="mb-3">
                                                <label for="page_title"
                                                       class="form-label">{{__('variables.general_title_page')}}</label>
                                                <input type="text" name="page_title" class="form-control"
                                                       id="page_title" value="{{$info_line->page_title ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="h1_title"
                                                       class="form-label">{{__('variables.h1_title_page')}}</label>
                                                <input type="text" name="h1_title" class="form-control" id="h1_title"
                                                       value="{{$info_line->h1_title ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_title"
                                                       class="form-label">{{__('variables.meta_title_page')}}</label>
                                                <input type="text" name="meta_title" class="form-control"
                                                       id="meta_title" value="{{$info_line->meta_title ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_keywords"
                                                       class="form-label">{{__('variables.meta_keywords_page')}}</label>
                                                <input type="text" name="meta_keywords" class="form-control"
                                                       id="meta_keywords" value="{{$info_line->meta_keywords ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_description"
                                                       class="form-label">{{__('variables.meta_description_page')}}</label>
                                                <input type="text" name="meta_description" class="form-control"
                                                       id="meta_description"
                                                       value="{{$info_line->meta_description ?? '' }}">
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
                                                                value="{{$lang_key}}" {{$lang_key == $lang_to_edit ? 'selected' : ''}}>{{Str::ucfirst($one_lang)}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
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
