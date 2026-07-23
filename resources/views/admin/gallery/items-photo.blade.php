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
                            <h5 class="card-title">{{__('variables.add_photo')}} - <span class="text-primary">"{{$gallery_subject->name ?? '' }}"</span></h5>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForLanguage($lang, 'itemsvideo') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>{{ __('variables.add_video') }}
                                </a>
                                <a href="{{ urlForLanguage($lang, 'itemsphoto') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>{{ __('variables.add_photo') }}
                                </a>
                                <a href="{{ urlForLanguage($lang, 'galleryitemcart') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                            @else
                                <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForLanguage($lang, 'galleryitemcart') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                            @endif
                            @if($groupSubRelations->del_to_rec == 1)
                                <button class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                        data-url="{{urlForFunctionLanguage($lang, 'destroyGalleryItemToCart/destroyGalleryItemToCart')}}"
                                        data-current-url="{{ url()->current() }}" disabled>
                                    <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }}
                                    (<span>0</span>)
                                </button>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <div class="form-body mt-4">
                        <div class="row">
                            <div class="col-lg-12">
                                <form action="{{url($lang, ['back','uploadGalleryPhoto'])}}"
                                      class="dropzone border border-3 p-4 rounded mb-4" style="border: none;"
                                      id="dropzone-images-upload" data-gallery-id="{{ $gallery_subject_id->id ?? ''}}"
                                      enctype="multipart/form-data">
                                    <div class="dz-default dz-message text-center">
                                        <p>{{__('variables.drag_and_drop_images')}}</p>
                                        <span>
                                            <i class="lni lni-cloud-upload dropzone-font-size text-primary"></i>
                                        </span>
                                    </div>
                                </form>
                                @if(!empty($gallery_item) && count($gallery_item))
                                    <div class="card border border-3 mt-4 rounded shadow-none">
                                        <div class="card-body">
                                            <div class="d-lg-flex align-items-center mb-4 gap-3">
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table mb-0 table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th scope="col" class="text-center">№</th>
                                                        <th scope="col"
                                                            class="text-center">{{__('variables.photo')}}</th>
                                                        <th scope="col"
                                                            class="text-center">{{__('variables.title_table')}}</th>
                                                        <th scope="col"
                                                            class="text-center">{{__('variables.short_description')}}</th>
                                                        <th scope="col"
                                                            class="text-center">{{__('variables.edit')}}</th>
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
                                                           data-action="item">
                                                    @foreach($gallery_item as $one_gallery_photo)
                                                        <tr class="row-id"
                                                            data-id="{{$one_gallery_photo->gallery_item_id}}">
                                                            <th class="text-center"
                                                                scope="row">{{ $loop->iteration }}</th>
                                                            <td class="text-center">
                                                                @if(!empty($one_gallery_photo->galleryItemId->img) && file_exists('upfiles/gallery-items/' . $one_gallery_photo->galleryItemId->img))
                                                                    <a href="{{ asset('upfiles/gallery-items') }}/{{$one_gallery_photo->galleryItemId->img ?? ''}}"
                                                                       data-fancybox="gallery">
                                                                        <img src="{{ asset('upfiles/gallery-items/m') }}/{{showImg($one_gallery_photo->galleryItemId->img) ?? ''}}"
                                                                             width="120" height="120">
                                                                    </a>
                                                                @else
                                                                    <img src="{{asset('admin-assets/images/no-image.png')}}"
                                                                         alt="no-image" width="120" height="120"
                                                                         title="No image">
                                                                @endif
                                                            </td>
                                                            <td class="text-center photo-name">
                                                                <span>{{!empty(IfHasName($one_gallery_photo->gallery_item_id, $lang_id, 'gallery_item')) ? IfHasName($one_gallery_photo->gallery_item_id, $lang_id, 'gallery_item') : __('variables.another_name')}}</span>
                                                            </td>
                                                            <td class="text-center photo-descr">
                                                                <span>{{!empty(IfHasBody($one_gallery_photo->gallery_item_id, $lang_id, 'gallery_item')) ? IfHasBody($one_gallery_photo->gallery_item_id, $lang_id, 'gallery_item') : __('variables.another_name')}}</span>
                                                            </td>
                                                            <td class="text-center edit-gallery-photo">
                                                                @foreach($lang_list as $lang_key => $one_lang)
                                                                    <a href="javascript:;"
                                                                       data-url="{{$url_for_active_elem}}"
                                                                       data-lang-id="{{$lang_key}}"
                                                                       class="btn btn-sm btn-{{ !empty(IfHasName($one_gallery_photo->gallery_item_id, $lang_key, 'gallery_item')) ? 'success' : 'danger' }}"
                                                                       data-item-id="{{$one_gallery_photo->gallery_item_id}}">{{Str::ucfirst($one_lang)}}</a>
                                                                @endforeach
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="form-switch">
                                                                    <input class="form-check-input change-active"
                                                                           type="checkbox"
                                                                           data-active="{{$one_gallery_photo->galleryItemId->active}}"
                                                                           data-element-id="{{$one_gallery_photo->gallery_item_id}}"
                                                                           data-action="item"
                                                                           id="switch-active-{{$one_gallery_photo->gallery_item_id}}"
                                                                           data-url="{{$url_for_active_elem}}" {{$one_gallery_photo->galleryItemId->active == 1 ? 'checked' : ''}}>
                                                                    <label class="form-check-label"
                                                                           for="switch-active-{{$one_gallery_photo->gallery_item_id}}"></label>
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
                                                                           value="{{$one_gallery_photo->gallery_item_id}}"
                                                                           id="destroy-element-{{$one_gallery_photo->gallery_item_id}}">
                                                                    <label class="form-check-label"
                                                                           for="destroy-element-{{$one_gallery_photo->gallery_item_id}}"></label>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    @include('admin.templates.empty-list')
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

