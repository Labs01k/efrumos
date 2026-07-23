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
                                           placeholder="{{ __('variables.search_object_it') }}">
                                    <button type="submit" class="input-group-text btn-success btn-search"><i
                                            class="bx bx-search"></i></button>
                                </div>
                            </form>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForFunctionLanguage($lang, $labels_id->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForLanguage($lang, 'createItem') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, $labels_id->id) }}"
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
                    @if(!empty($child_labels_list_id) && count($child_labels_list_id))
                        <div class="table-responsive tables-white-space-normal">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">№</th>
                                    <th scope="col" class="text-center">{{__('variables.title_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.edit_table')}}</th>
                                    @if($groupSubRelations->del_to_rec == 1)
                                        <th scope="col"
                                            class="text-center select-all-elements cursor-pointer">{{__('variables.delete_table')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($child_labels_list_id as $one_label_element)
                                    <tr class="row-id" data-id="{{$one_label_element->id}}">
                                        <th class="text-center" scope="row">{{ $one_label_element->id }}</th>
                                        <td class="text-center">
                                            <span>{{ $one_label_element->itemByLang->name ?? __('variables.another_name')}}</span><br>
                                            @if(Auth::user()->root == 1)
                                                <span
                                                    class="text-secondary">ShowLabelById({{ $one_label_element->id ?? '' }})</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex ml-labels-buttons justify-content-center">
                                                @foreach($lang_list as $lang_key => $one_lang)
                                                    <a href="{{urlForFunctionLanguage($lang, $one_label_element->p_id.'/editItem/'.$one_label_element->id.'/'.$lang_key)}}"
                                                       class="btn btn-sm btn-{{ !empty(IfHasName($one_label_element->id, $lang_key, 'labels')) ? 'success' : 'danger' }}">{{Str::ucfirst($one_lang)}}</a>
                                                @endforeach
                                            </div>
                                        </td>
                                        @if($groupSubRelations->del_to_rec == 1)
                                            <td class="text-center">
                                                <input class="form-check-input destroy-element" type="checkbox"
                                                       name="destroy_element"
                                                       value="{{$one_label_element->id}}"
                                                       id="destroy-element-{{$one_label_element->id}}">
                                                <label class="form-check-label"
                                                       for="destroy-element-{{$one_label_element->id}}"></label>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.templates.pagination', ['paginator' => $child_labels_list_id])
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
