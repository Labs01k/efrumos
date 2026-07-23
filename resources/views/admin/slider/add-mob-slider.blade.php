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
                                        class="text-primary">"{{$banner_top->name ?? '' }}"</span></h5>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'createBanner/createitem') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, 'bannersCart/cartitems') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-cart"></i>{{ __('variables.elements_basket') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($banner_top_without_lang->name).'/edititem/'.$banner_top_without_lang->banner_top_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'bannersCart/cartitems') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-cart"></i>{{ __('variables.elements_basket') }}
                                </a>
                                <a href="{{  urlForFunctionLanguage($lang, Str::slug($banner_top_without_lang->name).'/edititem/'.$banner_top_without_lang->banner_top_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'saveImgMobile/'.$banner_top_without_lang->banner_top_id.'/'.$lang_to_edit) }}"
                          id="edit-form" data-parent-url="{{$url_for_active_elem}}" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="mob_slide" value="1">

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-12 mb-2">
                                    <div class="border border-3 p-4 rounded">

                                        <span class="text-primary">{{__('variables.banner_size')}} - 830x1000</span>

                                        <div class="mb-3">
                                            <label for="upload_files" class="form-label">{{__('variables.select_file')}}</label>
                                            <input class="form-control" type="file" name="upload_files[]"
                                                   id="upload_files" multiple="">
                                        </div>

                                        @include('admin.templates.uploaded-mob-image', ['upload_path' => 'slider-mobile', 'item' => $banner_top])

                                    </div>
                                </div>

                                @if($groupSubRelations->save == 1)
                                    <div class="mt-2">
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
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
