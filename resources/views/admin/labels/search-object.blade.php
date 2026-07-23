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
                        <div class="position-relative">
                            <form method="GET" action="{{urlForFunctionLanguage($lang, 'search/searchObjects')}}">
                                <div class="input-group">
                                    <input type="text" name="search-key" class="form-control"
                                           placeholder="{{ __('variables.search_object_it') }}" value="{{ $concrete_search_key ?? '' }}">
                                    <button type="submit" class="input-group-text btn-success btn-search"><i
                                                class="bx bx-search"></i></button>
                                </div>
                            </form>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'createLabel/createitem') }}"
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
                                        data-url="{{urlForFunctionLanguage($lang, 'destroyLabelFromCart/destroyLabelFromCart')}}"
                                        data-current-url="{{ url()->current() }}" disabled>
                                    <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }} (<span>0</span>)
                                </button>
                            @endif
                        </div>
                    </div>
                    <hr/>
                    @if(!empty($labels_list) && count($labels_list))
                        <div class="table-responsive tables-white-space-normal">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">{{__('variables.id_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.title_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.edit_table')}}</th>
                                    @if($groupSubRelations->del_to_rec == 1)
                                        <th scope="col"
                                            class="text-center select-all-elements cursor-pointer">{{__('variables.delete_table')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody data-url="{{ $url_for_active_elem }}">
                                @foreach($labels_list as $label)
                                    <tr class="row-id" data-id="{{$label->labels_id}}">
                                        <th class="text-center" scope="row">{{$label->labels_id}}</th>
                                        <td class="text-center">
                                            <span>
                                                {{ !empty(IfHasName($label->labels_id, $lang_id, 'labels')) ? IfHasName($label->labels_id, $lang_id, 'labels') : trans('variables.another_name')}}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex ml-labels-buttons justify-content-center">
                                                @foreach($lang_list as $lang_key => $one_lang)
                                                    <a href="{{urlForFunctionLanguage($lang, Str::slug($label->name).'/edititem/'.$label->labels_id.'/'.$lang_key)}}"
                                                       class="btn btn-sm btn-{{ !empty(IfHasName($label->labels_id, $lang_key, 'labels')) ? 'success' : 'danger' }}">{{Str::ucfirst($one_lang)}}</a>
                                                @endforeach
                                            </div>
                                        </td>
                                        @if($label->labelsId->children->isEmpty())
                                            @if($groupSubRelations->del_to_rec == 1 || $groupSubRelations->del_from_rec == 1)
                                                <td class="text-center">
                                                    <input class="form-check-input destroy-element" type="checkbox"
                                                           name="destroy_element"
                                                           value="{{$label->labels_id}}"
                                                           id="destroy-element-{{$label->labels_id}}">
                                                    <label class="form-check-label"
                                                           for="destroy-element-{{$label->labels_id}}"></label>
                                                </td>
                                            @endif
                                        @else
                                            <td class="text-center">
                                                <span>{{__('variables.delete_inner_modules')}}</span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.templates.pagination', ['paginator' => $labels_list])
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
