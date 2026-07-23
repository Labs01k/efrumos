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
                            <h5 class="card-title">{{__('variables.add_video')}} - <span
                                        class="text-primary">"{{$gallery_subject->name ?? '' }}"</span></h5>
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

                    <form class="form" method="POST" action="{{ urlForLanguage($lang, 'createitemsvideo') }}"
                          id="add-video-form" style="margin-bottom: 1rem;"
                          enctype="multipart/form-data">

                        @csrf

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-8 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control" id="name">
                                        </div>

                                        <div class="mb-3">
                                            <label for="body" class="form-label">{{__('variables.description')}}</label>
                                            <textarea class="form-control" name="body" id="body" rows="3"></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="youtube_link"
                                                   class="form-label">{{__('variables.youtube_id')}}</label>
                                            <input type="text" name="youtube_link" class="form-control"
                                                   id="youtube_link" data-url="{{ $url_for_active_elem }}" value="">
                                        </div>

                                        <div class="mb-3 col-md-6">
                                            <div class="youtube_id"></div>
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
                                                            <option value="{{$lang_key}}" {{$lang_key == $lang_id ? 'selected' : ''}}>{{Str::ucfirst($one_lang)}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif

                                            @if($groupSubRelations->save == 1)
                                                <div class="col-12">
                                                    <div class="d-grid">
                                                        <button class="btn btn-success"
                                                                onclick="saveForm(this)"
                                                                data-form-id="add-video-form">{{__('variables.save_rights')}}
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
                                            <th scope="col" class="text-center">{{__('variables.photo')}}</th>
                                            <th scope="col" class="text-center">{{__('variables.title_table')}}</th>
                                            <th scope="col" class="text-center">{{__('variables.edit')}}</th>
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
                                                <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                                <td class="text-center">
                                                    <a href="https://www.youtube.com/embed/{{$one_gallery_photo->galleryItemId->youtube_id ?? '' }}?autoplay=0"
                                                       data-fancybox="gallery">
                                                        <img src="https://img.youtube.com/vi/{{$one_gallery_photo->galleryItemId->youtube_id ?? '' }}/0.jpg"
                                                             width="120" height="120">
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <span>{{!empty(IfHasName($one_gallery_photo->gallery_item_id, $lang_id, 'gallery_item')) ? IfHasName($one_gallery_photo->gallery_item_id, $lang_id, 'gallery_item') : __('variables.another_name')}}</span>
                                                </td>
                                                <td class="edit-gallery-item">
                                                    @foreach($lang_list as $lang_key => $one_lang)
                                                        <a href="javascript:;" data-url="{{$url_for_active_elem}}"
                                                           data-lang-id="{{$lang_key}}"
                                                           class="btn btn-sm btn-{{ empty(IfHasName($one_gallery_photo->gallery_item_id, $lang_key, 'gallery_item')) && $one_gallery_photo->lang_id == $lang_key ? 'success' : (!empty(IfHasName($one_gallery_photo->gallery_item_id, $lang_key, 'gallery_item')) ? 'success' : 'danger') }}"
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
@stop

