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
                                    class="text-primary">"{{$user->name . ' ' . $user->last_name }}"</span></h5>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForFunctionLanguage($lang, 'memberslist') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'create_user/createitem') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>
                            @else
                                <a href="{{ urlForLanguage($lang, '/') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST" action="{{ urlForLanguage($lang, 'save/'.$user->id) }}"
                          id="edit-form"
                          enctype="multipart/form-data">

                        @csrf

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-8 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.name_text')}}</label>
                                            <input type="text" name="name" class="form-control" id="name"
                                                   value="{{$user->name ?? ''}}">
                                        </div>

                                        <div class="mb-3">
                                            <label for="last_name"
                                                   class="form-label">{{__('variables.last_name')}}</label>
                                            <input type="text" name="last_name" class="form-control" id="last_name"
                                                   value="{{$user->last_name ?? ''}}">
                                        </div>

                                        <div class="row">

                                            <div class="mb-3 col-md-6">
                                                <label for="email"
                                                       class="form-label">{{__('variables.email_text')}}</label>
                                                <input type="email" name="email" class="form-control" id="email"
                                                       value="{{$user->email ?? ''}}">
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="phone" class="form-label">{{__('variables.phone')}}</label>
                                                <input type="text" name="phone" class="form-control" id="phone"
                                                       value="{{$user->phone ?? ''}}">
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="password"
                                                       class="form-label">{{__('variables.password_text')}}</label>
                                                <input type="password" name="password" class="form-control"
                                                       id="password">
                                            </div>

                                            <div class="col-md-6">
                                                <label for="password_confirmation"
                                                       class="form-label">{{__('variables.repeat_password')}}</label>
                                                <input type="password" name="password_confirmation" class="form-control"
                                                       id="password_confirmation">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="row g-3">

                                            <div class="col-12">
                                                <label for="upload_files"
                                                       class="form-label">{{__('variables.select_file')}}</label>
                                                <input class="form-control" type="file" name="upload_files[]"
                                                       id="upload_files" multiple="">
                                            </div>

                                            @include('admin.templates.uploaded-image', ['upload_path' => 'front-user', 'item' => $user])

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
