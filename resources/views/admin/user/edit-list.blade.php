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
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'createGroup/createitem') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, 'groupCart/cartitems') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($group->name).'/editlist/'.$group->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'groupCart/cartitems') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($group->name).'/editlist/'.$group->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                            @endif
                        </div>
                    </div>
                    <hr/>

                    @if(!empty($result) && count($result))
                        <form class="form" method="POST" action="{{ urlForLanguage($lang, 'savelist/'.$group->id) }}"
                              id="edit-form"
                              enctype="multipart/form-data">

                            @csrf

                            <div class="form-body mt-4">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="border border-3 p-4 rounded">
                                            <div class="mb-3">
                                                <label for="name"
                                                       class="form-label">{{__('variables.title_table')}}</label>
                                                <input type="text" name="name" class="form-control" id="name"
                                                       value="{{$group->name}}">
                                            </div>

                                            <div class="card">
                                                <div class="card-body">

                                                    @foreach($result as $key => $userRoles)
                                                        @if(auth()->user()->root == 1)

                                                            <div class="alert border-0 border-start border-5 border-primary alert-dismissible fade show">
                                                                <div class="form-check">
                                                                    <label for="modules_id-{{$userRoles['modules']->modules_id}}-"
                                                                           class="form-check-label cursor-pointer">{{$userRoles['name']}}</label>
                                                                    <input type="checkbox"
                                                                           class="form-check-input modules-id cursor-pointer"
                                                                           id="modules_id-{{$userRoles['modules']->modules_id}}-"
                                                                           name="modules_id[{{$userRoles['modules']->modules_id}}]"
                                                                           value="{{$userRoles['modules']->modules_id}}"
                                                                           data-module-id="{{$userRoles['modules']->modules_id}}" {{in_array($userRoles['modules']->modules_id, $arr) ? 'checked' : ''}}>
                                                                </div>
                                                            </div>

                                                            <div class="{{in_array($userRoles['modules']->modules_id, $arr) ? '' : 'hidden' }} children-rights"
                                                                 id="taction-{{$userRoles['modules']->modules_id}}-">

                                                                <div class="form-check">
                                                                    <label for="new-{{$userRoles['modules']->modules_id}}-"
                                                                           class="form-check-label cursor-pointer">{{trans('variables.create_new_rights')}}</label>
                                                                    <input type="checkbox"
                                                                           id="new-{{$userRoles['modules']->modules_id}}-"
                                                                           name="new[{{$userRoles['modules']->modules_id}}]"
                                                                           class="form-check-input cursor-pointer" {{in_array('1'.$userRoles['modules']->modules_id, $new) ? 'checked' : '' }}>
                                                                </div>

                                                                <div class="form-check">
                                                                    <label for="save-{{$userRoles['modules']->modules_id}}-"
                                                                           class="form-check-label cursor-pointer">{{trans('variables.save_rights')}}</label>
                                                                    <input type="checkbox"
                                                                           id="save-{{$userRoles['modules']->modules_id}}-"
                                                                           name="save[{{$userRoles['modules']->modules_id}}]" {{in_array('1'.$userRoles['modules']->modules_id, $save) ? 'checked' : '' }}
                                                                           class="form-check-input cursor-pointer">
                                                                </div>

                                                                <div class="form-check">
                                                                    <label for="active-{{$userRoles['modules']->modules_id}}-"
                                                                           class="form-check-label cursor-pointer">{{trans('variables.active_rights')}}</label>
                                                                    <input type="checkbox"
                                                                           id="active-{{$userRoles['modules']->modules_id}}-"
                                                                           name="active[{{$userRoles['modules']->modules_id}}]"
                                                                           class="form-check-input cursor-pointer" {{in_array('1'.$userRoles['modules']->modules_id, $active) ? 'checked' : '' }}>
                                                                </div>

                                                                <div class="form-check">
                                                                    <label for="del_to_rec-{{$userRoles['modules']->modules_id}}-"
                                                                           class="form-check-label cursor-pointer">{{trans('variables.del_to_rec_rights')}}</label>
                                                                    <input type="checkbox"
                                                                           id="del_to_rec-{{$userRoles['modules']->modules_id}}-"
                                                                           name="del_to_rec[{{$userRoles['modules']->modules_id}}]"
                                                                           class="form-check-input cursor-pointer" {{in_array('1'.$userRoles['modules']->modules_id, $del_to_rec) ? 'checked' : '' }}>
                                                                </div>

                                                                <div class="form-check">
                                                                    <label for="del_from_rec-{{$userRoles['modules']->modules_id}}-"
                                                                           class="form-check-label cursor-pointer">{{trans('variables.del_from_rec_rights')}}</label>
                                                                    <input type="checkbox"
                                                                           id="del_from_rec-{{$userRoles['modules']->modules_id}}-"
                                                                           name="del_from_rec[{{$userRoles['modules']->modules_id}}]"
                                                                           class="form-check-input cursor-pointer" {{in_array('1'.$userRoles['modules']->modules_id, $del_from_rec) ? 'checked' : '' }}>
                                                                </div>
                                                            </div>

                                                        @else
                                                            @if($userRoles['modules']->modulesId->alias != 'modules-constructor')

                                                                <div class="alert border-0 border-start border-5 border-primary alert-dismissible fade show">
                                                                    <div class="form-check">
                                                                        <label for="modules_id-{{$userRoles['modules']->modules_id}}-"
                                                                               class="form-check-label cursor-pointer">{{$userRoles['name']}}</label>
                                                                        <input type="checkbox"
                                                                               class="form-check-input modules-id cursor-pointer"
                                                                               id="modules_id-{{$userRoles['modules']->modules_id}}-"
                                                                               name="modules_id[{{$userRoles['modules']->modules_id}}]"
                                                                               value="{{$userRoles['modules']->modules_id}}"
                                                                               data-module-id="{{$userRoles['modules']->modules_id}}" {{in_array($userRoles['modules']->modules_id, $arr) ? 'checked' : ''}}>
                                                                    </div>
                                                                </div>

                                                                <div class="{{in_array($userRoles['modules']->modules_id, $arr) ? '' : 'hidden' }} children-rights"
                                                                     id="taction-{{$userRoles['modules']->modules_id}}-">

                                                                    <div class="form-check">
                                                                        <label for="new-{{$userRoles['modules']->modules_id}}-"
                                                                               class="form-check-label cursor-pointer">{{trans('variables.create_new_rights')}}</label>
                                                                        <input type="checkbox"
                                                                               id="new-{{$userRoles['modules']->modules_id}}-"
                                                                               name="new[{{$userRoles['modules']->modules_id}}]"
                                                                               class="form-check-input cursor-pointer" {{in_array('1'.$userRoles['modules']->modules_id, $new) ? 'checked' : '' }}>
                                                                    </div>

                                                                    <div class="form-check">
                                                                        <label for="save-{{$userRoles['modules']->modules_id}}-"
                                                                               class="form-check-label cursor-pointer">{{trans('variables.save_rights')}}</label>
                                                                        <input type="checkbox"
                                                                               id="save-{{$userRoles['modules']->modules_id}}-"
                                                                               name="save[{{$userRoles['modules']->modules_id}}]" {{in_array('1'.$userRoles['modules']->modules_id, $save) ? 'checked' : '' }}
                                                                               class="form-check-input cursor-pointer">
                                                                    </div>

                                                                    <div class="form-check">
                                                                        <label for="active-{{$userRoles['modules']->modules_id}}-"
                                                                               class="form-check-label cursor-pointer">{{trans('variables.active_rights')}}</label>
                                                                        <input type="checkbox"
                                                                               id="active-{{$userRoles['modules']->modules_id}}-"
                                                                               name="active[{{$userRoles['modules']->modules_id}}]"
                                                                               class="form-check-input cursor-pointer" {{in_array('1'.$userRoles['modules']->modules_id, $active) ? 'checked' : '' }}>
                                                                    </div>

                                                                    <div class="form-check">
                                                                        <label for="del_to_rec-{{$userRoles['modules']->modules_id}}-"
                                                                               class="form-check-label cursor-pointer">{{trans('variables.del_to_rec_rights')}}</label>
                                                                        <input type="checkbox"
                                                                               id="del_to_rec-{{$userRoles['modules']->modules_id}}-"
                                                                               name="del_to_rec[{{$userRoles['modules']->modules_id}}]"
                                                                               class="form-check-input cursor-pointer" {{in_array('1'.$userRoles['modules']->modules_id, $del_to_rec) ? 'checked' : '' }}>
                                                                    </div>

                                                                    <div class="form-check">
                                                                        <label for="del_from_rec-{{$userRoles['modules']->modules_id}}-"
                                                                               class="form-check-label cursor-pointer">{{trans('variables.del_from_rec_rights')}}</label>
                                                                        <input type="checkbox"
                                                                               id="del_from_rec-{{$userRoles['modules']->modules_id}}-"
                                                                               name="del_from_rec[{{$userRoles['modules']->modules_id}}]"
                                                                               class="form-check-input cursor-pointer" {{in_array('1'.$userRoles['modules']->modules_id, $del_from_rec) ? 'checked' : '' }}>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                </div>
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
                        </form>
                    @else
                        @include('admin.templates.empty-list')
                    @endif

                </div>
            </div>
        </div>
    </div>
@stop