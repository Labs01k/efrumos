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
                                    class="text-primary">"{{$info_item->name ?? '' }}"</span></h5>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForLanguage($lang, 'createinfoitem') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>
                                <a href="{{ urlForLanguage($lang, 'infoitemscart') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-cart"></i>{{ __('variables.elements_basket') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, $info_line_id->alias.'/editinfoitem/'.$info_item_without_lang->info_item_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @else
                                <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForLanguage($lang, 'infoitemscart') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-cart"></i>{{ __('variables.elements_basket') }}
                                </a>
                                <a href="{{ urlForFunctionLanguage($lang, $info_line_id->alias.'/editinfoitem/'.$info_item_without_lang->info_item_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'saveinfoitem/'.$info_item_without_lang->info_item_id.'/'.$lang_to_edit) }}"
                          id="edit-form" data-parent-url="{{$url_for_active_elem}}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-8 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control" id="name"
                                                   value="{{ $info_item->name ?? '' }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="alias"
                                                   class="form-label">{{__('variables.alias_table')}}</label>
                                            <input type="text" name="alias" class="form-control" id="alias"
                                                   value="{{$info_item_id->alias ?? '' }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="descr"
                                                   class="form-label">{{__('variables.short_description')}}</label>
                                            <textarea class="form-control" name="descr" id="descr"
                                                      rows="3">{{ $info_item->descr ?? '' }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="body" class="form-label">{{__('variables.description')}}</label>
                                            <textarea class="form-control editor" name="body" id="body"
                                                      rows="10">{{ $info_item->body ?? '' }}</textarea>
                                        </div>

                                        @if(!empty($promo_list) && count($promo_list))
                                            <div class="mb-3">
                                                <label for="goods_promo_id"
                                                       class="form-label">{{__('variables.promotions')}}</label>
                                                <select name="goods_promo_id[]" id="goods_promo_id"
                                                        class="form-select multiple-select" multiple>
                                                    @foreach($promo_list as $one_promo)
                                                        <option
                                                            value="{{ $one_promo->id ?? '' }}" {{ in_array($one_promo->id, explode(',',$info_item_id->goods_promo_id))? 'selected' : '' }}>{{ $one_promo->name ?? '' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        @if(!empty($goods_list) && count($goods_list))
                                            <div class="mb-3">
                                                <label for="goods_list"
                                                       class="form-label">{{__('variables.product')}}</label>
                                                <select name="goods_list[]" id="goods_list"
                                                        class="form-select multiple-select" multiple>
                                                    @foreach($goods_list as $one_goods)
                                                        <option
                                                            value="{{ $one_goods->id ?? '' }}" {{ in_array($one_goods->id,explode(',',$info_item_id->goods_list))? 'selected' : '' }}>{{ $one_goods->name ?? '' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        @if(request()->segment(4) == 'blog' || request()->segment(4) == 'news')
                                            <span class="text-primary">{{__('variables.banner_size')}} - 1600x600</span>
                                        @else
                                            <span class="text-primary">{{__('variables.banner_size')}} - 1270x730</span>
                                        @endif

                                        <div class="mb-3">
                                            <label for="upload_files"
                                                   class="form-label">{{__('variables.select_file')}}</label>
                                            <input class="form-control" type="file" name="upload_files[]"
                                                   id="upload_files" multiple="">
                                        </div>

                                        @include('admin.templates.uploaded-images', ['upload_path' => 'info-items'])

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
                                                       id="page_title" value="{{$info_item->page_title ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="h1_title"
                                                       class="form-label">{{__('variables.h1_title_page')}}</label>
                                                <input type="text" name="h1_title" class="form-control" id="h1_title"
                                                       value="{{$info_item->h1_title ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_title"
                                                       class="form-label">{{__('variables.meta_title_page')}}</label>
                                                <input type="text" name="meta_title" class="form-control"
                                                       id="meta_title" value="{{$info_item->meta_title ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_keywords"
                                                       class="form-label">{{__('variables.meta_keywords_page')}}</label>
                                                <input type="text" name="meta_keywords" class="form-control"
                                                       id="meta_keywords" value="{{$info_item->meta_keywords ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_description"
                                                       class="form-label">{{__('variables.meta_description_page')}}</label>
                                                <input type="text" name="meta_description" class="form-control"
                                                       id="meta_description"
                                                       value="{{$info_item->meta_description ?? '' }}">
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
                                                <label for="add_date"
                                                       class="form-label">{{__('variables.date_table')}}</label>
                                                <input type="text" name="add_date" id="add_date" class="form-control"
                                                       value="{{ \Carbon\Carbon::parse($info_item_id->add_date)->format('Y-m-d H:i') }}">
                                            </div>

                                            {{--<div class="col-12">
                                                <input type="checkbox" class="form-check-input"
                                                       name="show_text_in_products"
                                                       id="show_text_in_products" {{$info_item_id->show_text_in_products == 1 ? 'checked' : ''}}>
                                                <label class="form-check-label"
                                                       for="show_text_in_products">{{__('variables.show_text_in_products')}}</label>
                                            </div>

                                            <div class="col-12">
                                                <label for="promo_color"
                                                       class="form-label">{{__('variables.promo_color')}}</label>
                                                <input type="text" name="promo_color" class="form-control"
                                                       id="promo_color"
                                                       value="{{$info_item_id->promo_color ?? '' }}">
                                            </div>

                                                <div class="col-12">
                                                    <label for="text_tag_promo"
                                                           class="form-label">{{__('variables.text_promo_tag')}}</label>
                                                    <input type="text" name="text_tag_promo" class="form-control"
                                                           id="text_tag_promo" value="{{$info_item->text_tag_promo ?? '' }}">
                                                </div>--}}


                                                <div class="col-12">
                                                    <label for="author"
                                                           class="form-label">{{__('variables.author')}}</label>
                                                    <input type="text" name="author" class="form-control"
                                                           id="author" value="{{ $info_item->author ?? '' }}">
                                                </div>

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
                </div>
            </div>
        </div>
    </div>

@stop

@push('other-scripts')
    <script>
        $('#add_date').flatpickr({
            altFormat: "j F Y",
            enableTime: true,
            time_24hr: true,
            dateFormat: "Y-m-d H:i",
            disableMobile: "true",
            "locale": "{{ $lang }}"
        });
    </script>
@endpush
