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
                                @if(request()->segment(5) == '' || request()->segment(4) == 'createModules')
                                    <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                                class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                    <a href="{{ urlForFunctionLanguage($lang, 'createModules/createmodules') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                                class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                    </a>
                                    <a href="{{ urlForFunctionLanguage($lang, 'modulesCart/modulescart') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                                class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                    </a>
                                @else
                                    <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                                class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                    <a href="{{ urlForLanguage($lang, 'createmodules') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                                class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                    </a>
                                    <a href="{{ urlForLanguage($lang, 'modulescart') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                                class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                    </a>
                                @endif
                            @else
                                @if(request()->segment(5) == '' || request()->segment(4) == 'createModules')
                                    <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                                class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                    <a href="{{ urlForFunctionLanguage($lang, 'modulesCart/modulescart') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                                class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                    </a>
                                @else
                                    <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                                class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                    <a href="{{ urlForLanguage($lang, 'modulescart') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                                class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST" action="{{ urlForLanguage($lang, 'savemodules') }}" id="add-form"
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
                                        <div class="mb-3 show-ckeditor">
                                            <label for="body" class="form-label">{{__('variables.description')}}</label>
                                            <textarea class="form-control editor" name="body" id="body"
                                                      rows="10"></textarea>
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
                                                            <option value="{{$lang_key}}" {{$lang_key == $lang_id ? 'selected' : ''}}>{{Str::ucfirst($one_lang)}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                            <div class="col-12">
                                                <label for="p_id"
                                                       class="form-label">{{__('variables.p_id_name')}}</label>
                                                <select class="form-select" name="p_id" id="p_id">
                                                    <option value="0">{{__('variables.home')}}</option>
                                                    {!! SelectModulesTree($lang_id, 0, $curr_page_id) !!}
                                                </select>
                                            </div>

                                            <div class="col-12">
                                                <label for="controller"
                                                       class="form-label">{{__('variables.controller')}}</label>
                                                <input type="text" name="controller" class="form-control"
                                                       id="controller">
                                            </div>

                                            <div class="col-md-6">
                                                <input type="checkbox" class="form-check-input" name="root"
                                                       id="root">
                                                <label class="form-check-label"
                                                       for="root">{{__('variables.for_root_module')}}</label>
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
