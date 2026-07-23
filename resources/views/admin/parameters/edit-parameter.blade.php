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
                                        class="text-primary">"{{$goods_parametr->name ?? '' }}"</span></h5>
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
                                <a href="{{ urlForLanguage($lang, 'creategoodsparameter/'.$goods_subject_id->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                                <a href="{{ urlForLanguage($lang, 'goodsparametercart/'.$goods_subject_id->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                            @else
                                <a href="{{ urlForLanguage($lang, 'goodsparameters/'.$goods_subject_id->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.add_parameter') }}</a>
                                <a href="{{ urlForLanguage($lang, 'editgoodsparameter/'.$goods_parameter_without_lang->goods_parametr_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>{{ __('variables.edit_element') }}
                                </a>
                                <a href="{{ urlForLanguage($lang, 'goodsparametercart/'.$goods_subject_id->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'savegoodsparameter/'.$goods_parameter_without_lang->goods_parametr_id.'/'.$lang_to_edit) }}"
                          id="edit-form" data-page-type="edit-parameter" enctype="multipart/form-data">

                        @csrf
                        <input type="hidden" name="goods_subject_id" value="{{$goods_subject_id->id ?? ''}}">
                        <input type="hidden" name="measure_type" value="{{$goods_parametr_id->measure_type ?? ''}}">
                        <input type="hidden" name="parametr_type" value="{{$goods_parametr_id->parametr_type ?? ''}}">

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-8 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control"
                                                   id="name" value="{{$goods_parametr->name ?? ''}}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="alias"
                                                   class="form-label">{{__('variables.alias_table')}}</label>
                                            <input type="text" name="alias" class="form-control" id="alias"
                                                   value="{{$goods_parametr_id->alias ?? ''}}">
                                        </div>

                                        @if(!empty($goods_parameter_value) && count($goods_parameter_value))
                                            <div class="{{--hide-parameter-values--}}">
                                                <div class="position-relative d-flex justify-content-between mt-5">
                                                    <div>
                                                        <h5 class="card-title">{{__('variables.parametr_value')}}</h5>
                                                    </div>
                                                </div>

                                                <div class="card card-files radius-10">
                                                    <div class="card-body">
                                                        <table class="table mb-0 table-hover table-parametr-values">
                                                            <thead>
                                                            <tr>
                                                                <th scope="col"
                                                                    class="text-center">{{__('variables.position_table')}}</th>
                                                                <th scope="col"
                                                                    class="text-center">{{__('variables.name_text')}}</th>
                                                                @if($groupSubRelations->del_to_rec == 1)
                                                                    <th scope="col"
                                                                        class="text-center select-all-elements cursor-pointer">{{__('variables.delete_table')}}</th>
                                                                @endif
                                                            </tr>
                                                            </thead>
                                                            <tbody class="sort-table"
                                                                   data-url="{{ $url_for_active_elem }}"
                                                                   data-action="parameter_value">
                                                            @foreach($goods_parameter_value as $key => $one_parameter_value)
                                                                <tr class="row-id"
                                                                    data-id="{{$one_parameter_value->parametrValueId->id}}"
                                                                    data-param-id="{{$goods_parametr_id->id}}">
                                                                    <td class="position cursor-pointer text-center"><i
                                                                                class="lni lni-move"></i></td>
                                                                    <td class="text-center">
                                                                        <input
                                                                                name="parametr_type_value[{{$one_parameter_value->parametrValueId->id}}]"
                                                                                class="parameter-input form-control form-control-sm"
                                                                                value="{{!empty(IfHasName($one_parameter_value->parametrValueId->id, $lang_to_edit, 'goods_parametr_value')) ? IfHasName($one_parameter_value->parametrValueId->id, $lang_to_edit, 'goods_parametr_value') : ''}}">
                                                                    </td>
                                                                    @if($groupSubRelations->del_to_rec == 1)
                                                                        <td class="text-center">
                                                                            <button type="button"
                                                                                    class="btn delete-parametr-value"><i
                                                                                        class="bx bxs-trash"></i>
                                                                            </button>
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                        <button type="button" class="btn btn-secondary btn-sm px-5 mt-3"
                                                                id="more_values">{{__('variables.add_value')}}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($goods_parametr_id->parametr_type == 'input')
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
                                                                <option value="no_measure" {{$goods_parametr_id->measure_type == 'no_measure' ? 'selected' : ''}}>{{__('variables.without_measurement')}}</option>
                                                                <option value="with_measure" {{$goods_parametr_id->measure_type == 'with_measure' ? 'selected' : ''}}>{{__('variables.with_measurement')}}</option>
                                                            </select>
                                                        </div>
                                                        @if(!empty($measure) && count($measure))
                                                            <div class="col-12 mt-3 hide-with-measure">
                                                                <label for="goods_measure_id"
                                                                       class="form-label">{{__('variables.one_measure_type')}}</label>
                                                                <select class="form-select" name="goods_measure_id"
                                                                        id="goods_measure_id">
                                                                    @foreach($measure as $one_measure)
                                                                        <option
                                                                                value="{{$one_measure->goods_measure_id}}" {{$goods_parametr_id->goods_measure_id == $one_measure->goods_measure_id ? 'selected' : ''}}>{{$one_measure->name}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
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

                                            @if($goods_parametr_id->parametr_type == 'select' || $goods_parametr_id->parametr_type == 'checkbox' || $goods_parametr_id->parametr_type == 'radio')
                                                <div class="col-12">
                                                    <label for="parametr_type"
                                                           class="form-label">{{__('variables.parameter_type')}}</label>
                                                    <select class="form-select" name="parametr_type" id="parametr_type">
                                                        <option value="select" {{$goods_parametr_id->parametr_type == 'select' ? 'selected' :''}}>
                                                            Select
                                                        </option>
                                                        <option value="checkbox" {{$goods_parametr_id->parametr_type == 'checkbox' ? 'selected' :''}}>
                                                            Checkbox
                                                        </option>
                                                        <option value="radio" {{$goods_parametr_id->parametr_type == 'radio' ? 'selected' :''}}>
                                                            Radio
                                                        </option>
                                                    </select>
                                                </div>
                                            @else
                                                <input type="hidden" name="parametr_type"
                                                       value="{{$goods_parametr_id->parametr_type ?? ''}}">
                                            @endif

                                            <div class="col-12">
                                                <input type="checkbox" class="form-check-input" name="start_open"
                                                       id="start_open" {{$goods_parametr_id->start_open == 1 ? 'checked' : ''}}>
                                                <label class="form-check-label"
                                                       for="start_open">{{__('variables.start_open')}}</label>
                                            </div>

                                            @if($groupSubRelations->save == 1)
                                                <div class="col-12">
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
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
