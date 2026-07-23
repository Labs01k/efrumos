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
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
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
                            @else
                                <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.memberslist') }}</a>
                                <a href="{{ urlForLanguage($lang, 'modulescart') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.modulescart') }}
                                </a>
                            @endif
                            @if($groupSubRelations->del_to_rec == 1)
                                <button class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                        data-url="{{urlForFunctionLanguage($lang, 'destroyModulesToCart/destroyModulesToCart')}}"
                                        data-current-url="{{ url()->current() }}" disabled>
                                    <i class="fas fa-trash"></i> Delete selected (<span>0</span>)
                                </button>
                            @endif
                        </div>
                    </div>
                    <hr/>
                    @if(!empty($child_modules_list))
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">№</th>
                                    <th scope="col" class="text-center">{{__('variables.title_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.edit_table')}}</th>
                                    @if($groupSubRelations->active == 1)
                                        <th scope="col" class="text-center">{{__('variables.active_table')}}</th>
                                        @if($groupSubRelations->level == 1)
                                            <th scope="col" class="text-center">{{__('variables.top_menu')}}</th>
                                            <th scope="col" class="text-center">{{__('variables.footer_menu')}}</th>
                                        @endif
                                    @endif
                                    <th scope="col" class="text-center">{{__('variables.position_table')}}</th>
                                    @if($groupSubRelations->del_to_rec == 1)
                                        <th scope="col"
                                            class="text-center select-all-elements cursor-pointer">{{__('variables.delete_table')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody class="sort-table" data-url="{{ $url_for_active_elem }}">
                                @foreach($child_modules_list as $key => $one_child_modules_element)
                                    <tr class="row-id" data-id="{{$one_child_modules_element->modules_id}}">
                                        <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                        @if(!IfHasChildModulesList($one_child_modules_element->modulesId->id, 'modules')->isEmpty())
                                            <td class="text-center">
                                                <a href="{{urlForFunctionLanguage($lang, $one_child_modules_element->modulesId->alias.'/memberslist')}}"
                                                   class="text-decoration-underline">{{!empty(IfHasName($one_child_modules_element->modules_id, $lang_id, 'modules')) ? IfHasName($one_child_modules_element->modules_id, $lang_id, 'modules') : __('variables.another_name')}}</a>
                                            </td>
                                        @else
                                            <td class="text-center">
                                                <span>{{ !empty(IfHasName($one_child_modules_element->modules_id, $lang_id, 'modules')) ? IfHasName($one_child_modules_element->modules_id, $lang_id, 'modules') : __('variables.another_name')}}</span>
                                            </td>
                                        @endif
                                        <td class="text-center">
                                            @foreach($lang_list as $lang_key => $one_lang)
                                                <a href="{{urlForFunctionLanguage($lang, GetParentAlias($one_child_modules_element->modules_id, 'modules_id').'/editmodules/'.$one_child_modules_element->modules_id.'/'.$lang_key)}}"
                                                   class="btn btn-sm btn-{{ !empty(IfHasName($one_child_modules_element->modules_id, $lang_key, 'modules')) ? 'success' : 'danger' }}">{{Str::ucfirst($one_lang)}}</a>
                                            @endforeach
                                        </td>
                                        <td class="text-center">
                                            <div class="form-switch">
                                                <input class="form-check-input change-active" type="checkbox"
                                                       data-active="{{$one_child_modules_element->modulesId->active}}"
                                                       data-element-id="{{$one_child_modules_element->modules_id}}"
                                                       data-action="main-active"
                                                       id="switch-active-{{$one_child_modules_element->modules_id}}"
                                                       data-url="{{$url_for_active_elem}}" {{$one_child_modules_element->modulesId->active == 1 ? 'checked' : ''}}>
                                                <label class="form-check-label"
                                                       for="switch-active-{{$one_child_modules_element->modules_id}}"></label>
                                            </div>
                                        </td>
                                        <td class="position cursor-pointer text-center"><i class="lni lni-move"></i>
                                        </td>
                                        @if($groupSubRelations->del_to_rec == 1)
                                            @if(IfHasChildModulesList($one_child_modules_element->modules_id, 'modules')->isEmpty() && $one_child_modules_element->modulesId->root == 0)
                                                <td class="text-center">
                                                    <input class="form-check-input destroy-element" type="checkbox"
                                                           name="destroy_element"
                                                           value="{{$one_child_modules_element->modules_id}}"
                                                           id="destroy-element-{{$one_child_modules_element->modules_id}}">
                                                    <label class="form-check-label"
                                                           for="destroy-element-{{$one_child_modules_element->modules_id}}"></label>
                                                </td>

                                            @elseif($one_child_modules_element->modulesId->root == 1)
                                                <td>
                                                    <span>{{__('__.cant_delete_module')}}</span>
                                                </td>
                                            @else
                                                <td>
                                                    <span>{{__('__.delete_inner_modules')}}</span>
                                                </td>
                                            @endif
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
