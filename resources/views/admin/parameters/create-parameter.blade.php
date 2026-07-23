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
                                <a href="{{ urlForLanguage($lang, 'goodsparameters/'.$goods_subject_id->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.parameters_list') }}</a>
                                <a href="{{ urlForLanguage($lang, 'creategoodsparameter/'.$goods_subject_id->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>{{ __('variables.add_parameter') }}
                                </a>
                                <a href="{{ urlForLanguage($lang, 'goodsparametercart/'.$goods_subject_id->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                            @else
                                <a href="{{ urlForLanguage($lang, 'goodsparameters/'.$goods_subject_id->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.parameters_list') }}</a>
                                <a href="{{ urlForLanguage($lang, 'goodsparametercart/'.$goods_subject_id->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST" action="{{ urlForLanguage($lang, 'savegoodsparameter') }}"
                          id="add-form" data-page-type="create-parameter" enctype="multipart/form-data">

                        @csrf
                        <input type="hidden" name="goods_subject_id" value="{{$goods_subject_id->id ?? ''}}">

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


                                        <div class="hide-parameter-values">
                                            <div class="position-relative d-flex justify-content-between mt-5">
                                                <div>
                                                    <h5 class="card-title">{{__('variables.parametr_value')}}</h5>
                                                </div>
                                            </div>

                                            <div class="card card-files radius-10">
                                                <div class="card-body">
                                                    <label class="form-label">{{__('variables.name_text')}}</label>
                                                    <div class="parametr-values-list">
                                                        <div class="inputs">
                                                            <input name="parametr_type_value[]"
                                                                   class="form-control form-control-sm mb-3">
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-secondary btn-sm px-5"
                                                            id="more_values">{{__('variables.add_value')}}</button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="hide-measure-type">

                                            <div class="position-relative d-flex justify-content-between mt-5">
                                                <div>
                                                    <h5 class="card-title">{{__('variables.measure_type')}}</h5>
                                                </div>
                                            </div>

                                            <div class="card card-files radius-10">
                                                <div class="card-body">
                                                    <div class="col-12">
                                                        <label for="measure_type"
                                                               class="form-label">{{__('variables.measure_type')}}</label>
                                                        <select class="form-select" name="measure_type"
                                                                id="measure_type">
                                                            <option value="no_measure">{{__('variables.without_measurement')}}</option>
                                                            <option value="with_measure"
                                                                    selected>{{__('variables.with_measurement')}}</option>
                                                            {{--<option value="measure_list">{{__('variables.with_list_of_measurement')}}</option>--}}
                                                        </select>
                                                    </div>
                                                    @if(!empty($measure) && count($measure))
                                                        <div class="col-12 mt-3 hide-with-measure">
                                                            <label for="goods_measure_id"
                                                                   class="form-label">{{__('variables.one_measure_type')}}</label>
                                                            <select class="form-select" name="goods_measure_id"
                                                                    id="goods_measure_id">
                                                                @foreach($measure as $one_measure)
                                                                    <option value="{{$one_measure->goods_measure_id}}">{{$one_measure->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @endif
                                                </div>
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
                                                            <option value="{{$lang_key}}" {{$lang_key == $lang_id ? 'selected' : ''}}>{{Str::ucfirst($one_lang)}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif

                                            <div class="col-12">
                                                <label for="parametr_type"
                                                       class="form-label">{{__('variables.parameter_type')}}</label>
                                                <select class="form-select" name="parametr_type" id="parametr_type">
                                                    <option value="input">Input</option>
                                                    <option value="textarea">Textarea</option>
                                                    <option value="select">Select</option>
                                                    <option value="radio">Radio</option>
                                                    <option value="checkbox">Checkbox</option>
                                                </select>
                                            </div>

                                            <div class="col-12">
                                                <input type="checkbox" class="form-check-input" name="start_open"
                                                       id="start_open">
                                                <label class="form-check-label"
                                                       for="start_open">{{__('variables.start_open')}}</label>
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
