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
                                    class="text-primary">"{{$settings->name ?? '' }}"</span></h5>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'createSetting/createitem') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($settings_without_lang->name).'/edititem/'.$settings_without_lang->settings_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($settings_without_lang->name).'/edititem/'.$settings_without_lang->settings_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'save/'.$settings_without_lang->settings_id.'/'.$lang_to_edit) }}"
                          id="edit-form" enctype="multipart/form-data">
                        @csrf

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-8 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control" id="name"
                                                   value="{{$settings->name ?? '' }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="alias"
                                                   class="form-label">{{__('variables.alias_table')}}</label>
                                            <input type="text" name="alias" class="form-control" id="alias"
                                                   value="{{$settings_without_lang->settingsId->alias ?? '' }}">
                                        </div>

                                        <div class="mb-3 input">
                                            <label for="input" class="form-label">{{__('variables.input')}}</label>
                                            <input type="text" name="input" class="form-control" id="input"
                                                   value="{{ $settings_body ?? ''}}">
                                        </div>
                                        {{--<div class="mb-3 ckeditor hidden">
                                            <label for="body" class="form-label">{{__('variables.body')}}</label>
                                            <textarea class="form-control editor" name="body" id="body" rows="10">{{$settings->body ?? $settings_without_lang->body}}</textarea>
                                        </div>--}}
                                        <div class="mb-3 textarea hidden">
                                            <label for="textarea" class="form-label">{{__('variables.body')}}</label>
                                            <textarea class="form-control" name="textarea" id="textarea"
                                                      rows="10">{{ $settings_body ?? '' }}</textarea>
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
                                                <label for="set_type"
                                                       class="form-label">{{__('variables.parameter_type')}}</label>
                                                <select class="form-select" name="set_type" id="set_type">
                                                    <option
                                                        value="input" {{(!is_null($settings_id)) ? (($settings_id->set_type == 'input' ? 'selected' : '')) : ''}}>{{__('variables.input')}}</option>
                                                    <option
                                                        value="textarea" {{(!is_null($settings_id)) ? (($settings_id->set_type == 'textarea' ? 'selected' : '')) : ''}}>{{__('variables.textarea')}}</option>
                                                    {{--<option value="ckeditor" {{(!is_null($settings_id)) ? (($settings_id->set_type == 'ckeditor' ? 'selected' : '')) : ''}}>{{__('variables.ckeditor')}}</option>--}}
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <input type="checkbox" class="form-check-input" name="lang_dependent"
                                                       id="lang_dependent" {{$settings_id->lang_dependent == 1 ? 'checked' : ''}}>
                                                <label class="form-check-label"
                                                       for="lang_dependent">{{__('variables.lang_dependent')}}</label>
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
