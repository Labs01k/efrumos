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
                                    class="text-primary">"{{$user->name ?? '' }}"</span></h5>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                @if(auth()->user()->root == 1)
                                    <a href="{{ urlForLanguage($lang, 'createuser') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                    </a>
                                @endif
                            @else
                                <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'save/'.$user->id) }}"
                          id="edit-form" data-parent-url="{{$url_for_active_elem}}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-8 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="login" class="form-label">{{__('variables.login_text')}}</label>
                                            <input type="text" name="login" class="form-control" id="login"
                                                   value="{{$user->login ?? '' }}">
                                        </div>

                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.name_text')}}</label>
                                            <input type="text" name="name" class="form-control" id="name"
                                                   value="{{$user->name ?? '' }}">
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label">{{__('variables.email_text')}}</label>
                                            <input type="email" name="email" class="form-control" id="email"
                                                   value="{{$user->email ?? '' }}">
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label for="password"
                                                       class="form-label">{{__('variables.password_text')}}</label>
                                                <input type="password" name="password" class="form-control"
                                                       id="password" autocomplete="off"
                                                       placeholder="{{trans('variables.empty_pass')}}">
                                            </div>

                                            <div class="col-md-6">
                                                <label for="password_confirmation"
                                                       class="form-label">{{__('variables.repeat_password')}}</label>
                                                <input type="password" name="password_confirmation"
                                                       class="form-control" autocomplete="off"
                                                       id="password_confirmation">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="row g-3">

                                            @if(!empty($all_group) && auth()->user()->id != $user->id && auth()->user()->root == 1)
                                                <div class="col-12">
                                                    <label for="group-list"
                                                           class="form-label">{{__('variables.group-list')}}</label>
                                                    <select class="form-select" name="group-list" id="group-list">
                                                        @foreach($all_group as $one_group)
                                                            <option
                                                                value="{{$one_group->id}}" {{$group->id == $one_group->id ? 'selected' : ''}}>{{$one_group->name ?? '' }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif

                                            <div class="mb-3">
                                                <label for="upload_files"
                                                       class="form-label">{{__('variables.select_file')}}</label>
                                                <input class="form-control" type="file" name="upload_files[]"
                                                       id="upload_files">
                                            </div>

                                            @include('admin.templates.uploaded-image', ['upload_path' => 'admin-user', 'item' => $user])

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
