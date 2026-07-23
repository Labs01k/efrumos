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
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'createsocial/createsocial') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST" action="{{ urlForLanguage($lang, 'save') }}" id="add-form"
                          enctype="multipart/form-data">

                        @csrf

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-12 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control" id="name" autofocus>
                                        </div>
                                        <div class="mb-3">
                                            <label for="link"
                                                   class="form-label">{{__('variables.link')}}</label>
                                            <input type="text" name="link" class="form-control" id="link">
                                        </div>

                                        <div class="mb-3">
                                            <label for="icon_name"
                                                   class="form-label">{{__('variables.icon_name')}}</label>
                                            <input type="text" name="icon_name" class="form-control" id="icon_name">
                                        </div>

                                        <div class="mb-3">
                                            <label for="upload_files"
                                                   class="form-label">{{__('variables.select_file')}}</label>
                                            <input class="form-control" type="file" name="upload_files[]"
                                                   id="upload_files">
                                        </div>

                                        @include('admin.templates.upload-new-images')

                                        @if($groupSubRelations->save == 1)
                                            <div class="mb-3">
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
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
