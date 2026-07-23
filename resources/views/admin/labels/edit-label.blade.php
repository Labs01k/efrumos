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
                                    class="text-primary">"{{ $labels ? Str::limit($labels->name, 50) : '' }}"</span>
                            </h5>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                @if($parent_label_id)
                                    <a href="{{ urlForFunctionLanguage($lang, $parent_label_id->id.'/memberslist') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                @else
                                    <a href="{{ urlForFunctionLanguage($lang) }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                @endif
                                <a href="{{ urlForFunctionLanguage($lang, 'createLabel/createitem') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($labels_without_lang->name).'/edititem/'.$labels_without_lang->labels_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @else
                                @if($parent_label_id)
                                    <a href="{{ urlForFunctionLanguage($lang, $parent_label_id->id.'/memberslist') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                @else
                                    <a href="{{ urlForFunctionLanguage($lang) }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                @endif
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($labels_without_lang->name).'/edititem/'.$labels_without_lang->labels_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'save/'.$labels_without_lang->labels_id.'/'.$lang_to_edit) }}"
                          id="edit-form"
                          enctype="multipart/form-data">

                        @csrf
                        <input type="hidden" name="p_id" value="{{ $labels_without_lang->labelsId->p_id }}">
                        <input type="hidden" name="current_url" value="{{ url()->current() }}">

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="border border-3 p-4 rounded">

                                        <div class="mb-3">
                                            @if(!empty($lang_list) && count($lang_list))
                                                <label for="lang"
                                                       class="form-label">{{__('variables.lang')}}</label>
                                                <select class="form-select" name="lang" id="lang">
                                                    @foreach($lang_list as $lang_key => $one_lang)
                                                        <option
                                                            value="{{$lang_key}}" {{$lang_key == $lang_to_edit ? 'selected' : ''}}>{{Str::ucfirst($one_lang)}}</option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </div>

                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control" id="name"
                                                   value="{{$labels->name ?? '' }}">
                                        </div>

                                        @if($groupSubRelations->save == 1)
                                            <div class="mb-3">
                                                <div class="d-grid">
                                                    <button class="btn btn-success"
                                                            onclick="saveForm(this)"
                                                            data-form-id="edit-form">{{__('variables.save_it')}}
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
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
