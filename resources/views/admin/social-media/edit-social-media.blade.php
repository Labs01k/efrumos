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
                                    class="text-primary">"{{$social_media->name ?? '' }}"</span></h5>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'createItem/createitem') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($social_media->name).'/edititem/'.$social_media->id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($social_media->name).'/edititem/'.$social_media->id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'save/'.$social_media->id.'/'.$lang_id) }}"
                          id="edit-form" enctype="multipart/form-data">
                        @csrf

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-12 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control" id="name"
                                                   value="{{$social_media->name ?? '' }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="link"
                                                   class="form-label">{{__('variables.link')}}</label>
                                            <input type="text" name="link" class="form-control" id="link"
                                                   value="{{$social_media->link ?? '' }}">
                                        </div>

                                        <div class="mb-3">
                                            <label for="icon_name"
                                                   class="form-label">{{__('variables.icon_name')}}</label>
                                            <input type="text" name="icon_name" class="form-control" id="icon_name" value="{{$social_media->icon_name ?? '' }}">
                                        </div>

                                        <div class="mb-3">
                                            <label for="upload_files"
                                                   class="form-label">{{__('variables.select_file')}}</label>
                                            <input class="form-control" type="file" name="upload_files[]"
                                                   id="upload_files" multiple="">
                                        </div>

                                        @include('admin.templates.uploaded-image', ['upload_path' => 'social-media', 'item' => $social_media])

                                        @if($groupSubRelations->save == 1)
                                            <div class="mb-3">
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
                </div>
            </div>
        </div>
    </div>
@stop
