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
                                    class="text-primary">"{{$goods_elems->name ?? '' }}"</span></h5>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                @if(request()->segment(5) == '' || request()->segment(4) == 'createGoodsSubject')
                                    <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                    <a href="{{ urlForFunctionLanguage($lang, 'createGoodsSubject/creategoodssubject') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>{{ __('variables.add_subject') }}
                                    </a>
                                    <a href="{{ urlForFunctionLanguage($lang, 'goodsSubjectCart/goodssubjectcart') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                    </a>
                                    <a href="{{ urlForFunctionLanguage($lang, Str::slug($goods_without_lang->name).'/editgoodssubject/'.$goods_without_lang->goods_subject_id.'/'.$lang_id) }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                    </a>
                                    <a href="{{ route('category', $goods_subject_id->alias) }}" target="_blank"
                                       class="btn btn-success mt-2 mt-lg-0">
                                        <div class="font-17"><i class="lni lni-eye"></i>
                                        </div>
                                    </a>
                                @else
                                    <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                    <a href="{{ urlForLanguage($lang, 'creategoodssubject') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>{{ __('variables.add_subject') }}
                                    </a>
                                    <a href="{{ urlForLanguage($lang, 'goodssubjectcart') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                    </a>
                                    <a href="{{ urlForLanguage($lang, 'editgoodssubject/'.$goods_without_lang->goods_subject_id.'/'.$lang_id) }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                    </a>
                                    <a href="{{ route('category', $goods_subject_id->alias) }}" target="_blank"
                                       class="btn btn-success mt-2 mt-lg-0">
                                        <div class="font-17"><i class="lni lni-eye"></i>
                                        </div>
                                    </a>
                                @endif
                            @else
                                @if(request()->segment(5) == '' || request()->segment(4) == 'createGoodsSubject')
                                    <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                    <a href="{{ urlForFunctionLanguage($lang, 'goodsSubjectCart/goodssubjectcart') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                    </a>
                                    <a href="{{ urlForFunctionLanguage($lang, Str::slug($goods_without_lang->name).'/editgoodssubject/'.$goods_without_lang->goods_subject_id.'/'.$lang_id) }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                    </a>
                                    <a href="{{ route('category', $goods_subject_id->alias) }}" target="_blank"
                                       class="btn btn-success mt-2 mt-lg-0">
                                        <div class="font-17"><i class="lni lni-eye"></i>
                                        </div>
                                    </a>
                                @else
                                    <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                    <a href="{{ urlForLanguage($lang, 'goodssubjectcart') }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                    </a>
                                    <a href="{{ urlForLanguage($lang, 'editgoodssubject/'.$goods_without_lang->goods_subject_id.'/'.$lang_id) }}"
                                       class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                    </a>
                                    <a href="{{ route('category', $goods_subject_id->alias) }}" target="_blank"
                                       class="btn btn-success mt-2 mt-lg-0">
                                        <div class="font-17"><i class="lni lni-eye"></i>
                                        </div>
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'savesubject/'.$goods_without_lang->goods_subject_id.'/'.$lang_to_edit) }}"
                          id="edit-form" data-parent-url="{{$url_for_active_elem}}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-8 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control" id="name"
                                                   value="{{ $goods_elems->name ?? ''}}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="alias"
                                                   class="form-label">{{__('variables.alias_table')}}</label>
                                            <input type="text" name="alias" class="form-control" id="alias"
                                                   value="{{$goods_subject_id->alias ?? '' }}">
                                        </div>

                                        <div class="mb-3">
                                            <label for="short_descr"
                                                   class="form-label">{{__('variables.short_description')}}</label>
                                            <textarea class="form-control" name="short_descr" id="short_descr"
                                                      rows="3">{{$goods_elems->short_descr ?? ''}}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="body" class="form-label">{{__('variables.description')}}</label>
                                            <textarea class="form-control editor" name="body"
                                                      id="body">{{$goods_elems->body ?? ''}}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="upload_files"
                                                   class="form-label">{{__('variables.select_file')}}</label>
                                            <input class="form-control" type="file" name="upload_files[]"
                                                   id="upload_files">
                                        </div>

                                        @include('admin.templates.uploaded-image', ['upload_path' => 'goods-subject', 'item' => $goods_subject_id])

                                        <div class="mb-3">
                                            <label for="link-banner" class="form-label">Link pentru banner</label>
                                            <input type="text" name="link_banner" class="form-control" id="link-banner" value="{{$goods_elems->link_banner ?? ''}}">
                                        </div>

                                        <div class="position-relative d-flex justify-content-between mt-5">
                                            <div>
                                                <h5 class="card-title">{{__('variables.seo_settings')}}</h5>
                                            </div>
                                            <div class="show-seo-settings" role="button">
                                                <h5 class="card-title text-primary font-30"><i
                                                        class="fadeIn animated bx bx-down-arrow-circle"></i></h5>
                                            </div>
                                        </div>
                                        <hr>

                                        <div class="hide-seo-settings">
                                            <div class="mb-3">
                                                <label for="page_title"
                                                       class="form-label">{{__('variables.general_title_page')}}</label>
                                                <input type="text" name="page_title" class="form-control"
                                                       id="page_title" value="{{$goods_elems->page_title ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="h1_title"
                                                       class="form-label">{{__('variables.h1_title_page')}}</label>
                                                <input type="text" name="h1_title" class="form-control" id="h1_title"
                                                       value="{{$goods_elems->h1_title ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_title"
                                                       class="form-label">{{__('variables.meta_title_page')}}</label>
                                                <input type="text" name="meta_title" class="form-control"
                                                       id="meta_title" value="{{$goods_elems->meta_title ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_keywords"
                                                       class="form-label">{{__('variables.meta_keywords_page')}}</label>
                                                <input type="text" name="meta_keywords" class="form-control"
                                                       id="meta_keywords"
                                                       value="{{$goods_elems->meta_keywords ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_description"
                                                       class="form-label">{{__('variables.meta_description_page')}}</label>
                                                <input type="text" name="meta_description" class="form-control"
                                                       id="meta_description"
                                                       value="{{$goods_elems->meta_description ?? '' }}">
                                            </div>
                                        </div>
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
                                            <div class="col-12">
                                                <label for="p_id"
                                                       class="form-label">{{__('variables.p_id_name')}}</label>
                                                <select class="form-select single-select" name="p_id" id="p_id">
                                                    <option
                                                        value="0" {{ !is_null($goods_subject_id) ? (($goods_subject_id->p_id == 0) ? 'selected' : '') : ''}}>{{__('variables.home')}}</option>
                                                    {!! SelectGoodsSubjectTree($lang_id, 0 ,$goods_subject_id->p_id) !!}
                                                </select>
                                            </div>

                                                <div class="col-12">
                                                    <label for="section"
                                                           class="form-label">{{__('variables.other_goods_subject_id')}}</label>
                                                    <input type="text" name="section"
                                                           class="form-control"
                                                           id="section"
                                                           value="{{$goods_subject_id->section ?? ''}}">
                                                </div>

                                                <div class="col-12">
                                                <label for="icon_name"
                                                       class="form-label">{{__('variables.icon_name')}}</label>
                                                <input type="text" name="icon_name" class="form-control" id="icon_name"
                                                       value="{{$goods_subject_id->icon_name ?? '' }}">
                                            </div>

                                            <div class="col-12">
                                                <label for="position_promo"
                                                       class="form-label">{{__('variables.position_promo')}}</label>
                                                <input type="text" name="position_promo" class="form-control"
                                                       id="position_promo"
                                                       value="{{ $goods_subject_id->position_promo ?? '' }}">
                                            </div>

                                            <span class="text-primary mt-3">{{__('variables.banner_size')}} - 1200x630</span>
                                            <div class="mt-0">
                                                <label for="img-two"
                                                       class="form-label">{{__('variables.meta_image')}}</label>
                                                <input class="form-control" type="file" name="img_two"
                                                       id="img-two">

                                            </div>
                                            @include('admin.templates.uploaded-different-image', ['upload_path' => 'goods-subject-meta', 'item' => $goods_subject_id, 'item_image_name' => $goods_subject_id->img_two])

                                            @if($groupSubRelations->save == 1)
                                                <button class="btn btn-success"
                                                        onclick="saveForm(this)"
                                                        data-form-id="edit-form">{{__('variables.save_it')}}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="form-body mt-4">

                        <div class="position-relative d-flex justify-content-between mt-5">
                            <div>
                                <h5 class="card-title">{{__('variables.for_slider')}} - <span
                                        class="text-primary font-14">{{__('variables.banner_size')}} - 1280x520</span>
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <form action="{{url($lang, ['back','uploadGoodsSubjectGallery'])}}"
                                      class="dropzone border border-3 p-4 rounded" style="border: none;"
                                      id="dropzone-images-upload" data-gallery-id="{{ $goods_subject_id->id ?? '' }}"
                                      data-current-lang-id="{{ $lang_to_edit }}"
                                      enctype="multipart/form-data">

                                    <div class="dz-default dz-message text-center">
                                        <p>{{__('variables.drag_and_drop_images')}}</p>
                                        <span>
                                            <i class="lni lni-cloud-upload dropzone-font-size text-primary"></i>
                                        </span>
                                    </div>
                                </form>
                                @if(!empty($goods_subject_photo) && count($goods_subject_photo))
                                    <div class="card border border-3 mt-4 rounded shadow-none">
                                        <div class="card-body">
                                            <div class="d-lg-flex align-items-center mb-4 gap-3">
                                                <div class="ms-auto">
                                                    @if($groupSubRelations->del_to_rec == 1)
                                                        <button
                                                            class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                                            data-url="{{urlForLanguage($lang, 'destroygoodsphoto/destroygoodsphoto')}}"
                                                            data-current-url="{{ url()->current() }}"
                                                            data-upload-path-optional="goods-subject-gallery" disabled>
                                                            <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }}
                                                            (<span>0</span>)
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table mb-0 table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th scope="col" class="text-center">№</th>
                                                        <th scope="col"
                                                            class="text-center">{{__('variables.photo')}}</th>
                                                        <th scope="col"
                                                            class="text-center">{{__('variables.link')}}</th>
                                                        @if($groupSubRelations->active == 1)
                                                            <th scope="col"
                                                                class="text-center">{{__('variables.active_table')}}</th>
                                                        @endif
                                                        <th scope="col"
                                                            class="text-center">{{__('variables.position_table')}}</th>
                                                        @if($groupSubRelations->del_to_rec == 1)
                                                            <th scope="col"
                                                                class="text-center select-all-elements cursor-pointer">{{__('variables.delete_table')}}</th>
                                                        @endif
                                                    </tr>
                                                    </thead>
                                                    <tbody class="sort-table" data-url="{{ $url_for_active_elem }}"
                                                           data-action="goods-subject-gallery">
                                                    @foreach($goods_subject_photo as $one_goods_subject_photo)
                                                        <tr class="row-id" data-id="{{$one_goods_subject_photo->id}}">
                                                            <th class="text-center"
                                                                scope="row">{{ $loop->iteration }}</th>
                                                            <td class="text-center">
                                                                @if(!empty($one_goods_subject_photo->img) && file_exists('upfiles/goods-subject-gallery/' . $one_goods_subject_photo->img))
                                                                    <a href="{{ asset('upfiles/goods-subject-gallery') }}/{{$one_goods_subject_photo->img ?? ''}}"
                                                                       data-fancybox="gallery">
                                                                        <img
                                                                            src="{{ asset('upfiles/goods-subject-gallery/m') }}/{{showImg($one_goods_subject_photo->img) ?? ''}}"
                                                                            class="product-md-img">
                                                                    </a>
                                                                @else
                                                                    <img
                                                                        src="{{asset('admin-assets/images/no-image.png')}}"
                                                                        alt="no-image" class="product-md-img"
                                                                        title="No image">
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                <input type="text"
                                                                       class="form-control form-control-sm save-slider-link"
                                                                       data-goods-subject-id="{{ $one_goods_subject_photo->goods_subject_id ?? '' }}"
                                                                       data-goods-subject-image-id="{{ $one_goods_subject_photo->id ?? '' }}"
                                                                       data-current-lang-id="{{ $one_goods_subject_photo->lang_id ?? '' }}"
                                                                       value="{{ $one_goods_subject_photo->link ?? '' }}">
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="form-switch">
                                                                    <input class="form-check-input change-active"
                                                                           type="checkbox"
                                                                           data-active="{{$one_goods_subject_photo->active}}"
                                                                           data-element-id="{{$one_goods_subject_photo->id}}"
                                                                           data-action="goods-subject-gallery"
                                                                           id="switch-active-{{$one_goods_subject_photo->id}}"
                                                                           data-url="{{$url_for_active_elem}}" {{$one_goods_subject_photo->active == 1 ? 'checked' : ''}}>
                                                                    <label class="form-check-label"
                                                                           for="switch-active-{{$one_goods_subject_photo->id}}"></label>
                                                                </div>
                                                            </td>
                                                            <td class="position cursor-pointer text-center"><i
                                                                    class="lni lni-move"></i>
                                                            </td>
                                                            @if($groupSubRelations->del_to_rec == 1)
                                                                <td class="text-center">
                                                                    <input class="form-check-input destroy-element"
                                                                           type="checkbox"
                                                                           name="destroy_element"
                                                                           value="{{$one_goods_subject_photo->id}}"
                                                                           id="destroy-element-{{$one_goods_subject_photo->id}}">
                                                                    <label class="form-check-label"
                                                                           for="destroy-element-{{$one_goods_subject_photo->id}}"></label>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
