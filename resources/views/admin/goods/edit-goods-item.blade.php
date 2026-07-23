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
                                <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForLanguage($lang, 'creategoodsitem') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="bx bxs-plus-square"></i>{{ __('variables.add_item') }}
                                </a>
                                <a href="{{ urlForLanguage($lang, 'goodsitemcart') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                                <a href="{{ urlForLanguage($lang, 'editgoodsitem/'.$goods_without_lang->goods_item_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                                <a href="{{ route('catalog-product', ['product', $goods_item_id->alias]) }}" target="_blank"
                                   class="btn btn-success mt-2 mt-lg-0"><div class="font-17"><i class="lni lni-eye"></i>
                                    </div>
                                </a>
                            @else
                                <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForLanguage($lang, 'goodsitemcart') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                                </a>
                                <a href="{{ urlForLanguage($lang, 'editgoodsitem/'.$goods_without_lang->goods_item_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                                <a href="{{ route('catalog-product', ['product', $goods_item_id->alias]) }}" target="_blank"
                                   class="btn btn-success mt-2 mt-lg-0"><div class="font-17"><i class="lni lni-eye"></i>
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'saveitem/'.$goods_without_lang->goods_item_id.'/'.$lang_to_edit) }}"
                          id="edit-form"
                          enctype="multipart/form-data">

                        @csrf
                        <input type="hidden" name="position" value="{{ $goods_item_id->position ?? ''}}">

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-8 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control" id="name"
                                                   value="{{$goods_elems->name ?? ''}}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="alias"
                                                   class="form-label">{{__('variables.alias_table')}}</label>
                                            <input type="text" name="alias" class="form-control" id="alias"
                                                   value="{{$goods_item_id->alias ?? ''}}">
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
                                            <label for="body-two" class="form-label">{{__('variables.user_method')}}</label>
                                            <textarea class="form-control editor" name="body_two"
                                                      id="body-two">{{$goods_elems->body_two ?? ''}}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="youtube_link"
                                                   class="form-label">{{__('variables.youtube_id')}}</label>
                                            <input type="text" id="youtube_link" name="youtube_link"
                                                   class="form-control" value="{{$goods_item_id->youtube_link ?? ''}}">
                                        </div>


                                        @if(!empty($goods_list) && count($goods_list))
                                            <div class="position-relative d-flex justify-content-between mt-5">
                                                <div>
                                                    <h5 class="card-title">{{__('variables.products_replaced_and_complementary')}}</h5>
                                                </div>
                                            </div>
                                            <hr>

                                            <div class="mb-3">
                                                <label for="produse_similare"
                                                       class="form-label">{{__('variables.products_replaced')}}</label>
                                                <select class="form-select multiple-select" name="produse_similare[]" id="produse_similare" multiple>
                                                    @foreach($goods_list as $one_goods)
                                                        <option value="{{ $one_goods->id ?? '' }}" {{ in_array($one_goods->id,explode(',',$goods_item_id->produse_similare))? 'selected' : '' }}>{{$one_goods->itemByLang->name ?? '' }} | {{ $one_goods->one_c_code ?? '' }} | {{ $one_goods->articol or '' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="produse_compatibile"
                                                       class="form-label">{{__('variables.products_complementary')}}</label>
                                                <select class="form-select multiple-select" name="produse_compatibile[]" id="produse_compatibile" multiple>
                                                    @foreach($goods_list as $one_goods)
                                                        <option value="{{ $one_goods->id ?? '' }}" {{ in_array($one_goods->id,explode(',',$goods_item_id->produse_compatibile))? 'selected' : '' }}>{{$one_goods->itemByLang->name ?? '' }} | {{ $one_goods->one_c_code ?? '' }} | {{ $one_goods->articol or '' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        @if(!empty($goods_parameters) && count($goods_parameters))
                                            @php
                                                $curr_goods_subject_id = null;
                                                if(!is_null($current_subject_id))
                                                    $curr_goods_subject_id = $current_subject_id->id;
                                                else
                                                    $curr_goods_subject_id = $goods_item_id->goods_subject_id;
                                            @endphp
                                            <div class="position-relative d-flex justify-content-between mt-5">
                                                <div>
                                                    <h5 class="card-title">{{__('variables.parameters_list')}}</h5>
                                                </div>
                                                <div class="show-goods-parameter cursor-pointer">
                                                    <h5 class="card-title text-primary font-30"><i
                                                            class="lni lni-arrow-down-circle"></i></h5>
                                                </div>
                                            </div>
                                            <hr>

                                            <div class="row row-cols-auto row-cols-1 hide-goods-parameters"
                                                 style="display: block;">
                                                @foreach($goods_parameters as $one_parameter)
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label fs-6 text-decoration-underline">{{$one_parameter->name ?? ''}}</label>
                                                        <div{{ $one_parameter->measure_type == 'with_measure' ? ' class=input-group' : '' }}>
                                                            <input type="hidden" name="goods_parametr_id[]"
                                                                   value="{{$one_parameter->goods_parametr_id ?? ''}}">
                                                            {{addEditParameterInItem($one_parameter->goods_parametr_id, $lang_to_edit, getMainCatalogId(), $goods_without_lang->goods_item_id)}}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="position-relative d-flex justify-content-between mt-5">
                                            <div>
                                                <h5 class="card-title">{{__('variables.seo_settings')}}</h5>
                                            </div>
                                            <div class="show-seo-settings cursor-pointer">
                                                <h5 class="card-title text-primary font-30"><i
                                                        class="lni lni-arrow-down-circle"></i></h5>
                                            </div>
                                        </div>
                                        <hr>

                                        <div class="hide-seo-settings">
                                            <div class="mb-3">
                                                <label for="page_title"
                                                       class="form-label">{{__('variables.general_title_page')}}</label>
                                                <input type="text" name="page_title" class="form-control"
                                                       id="page_title" value="{{ $goods_elems->page_title ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="h1_title"
                                                       class="form-label">{{__('variables.h1_title_page')}}</label>
                                                <input type="text" name="h1_title" class="form-control" id="h1_title"
                                                       value="{{ $goods_elems->h1_title ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_title"
                                                       class="form-label">{{__('variables.meta_title_page')}}</label>
                                                <input type="text" name="meta_title" class="form-control"
                                                       id="meta_title" value="{{ $goods_elems->meta_title ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_keywords"
                                                       class="form-label">{{__('variables.meta_keywords_page')}}</label>
                                                <input type="text" name="meta_keywords" class="form-control"
                                                       id="meta_keywords"
                                                       value="{{ $goods_elems->meta_keywords ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_description"
                                                       class="form-label">{{__('variables.meta_description_page')}}</label>
                                                <input type="text" name="meta_description" class="form-control"
                                                       id="meta_description"
                                                       value="{{ $goods_elems->meta_description ?? '' }}">
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
                                                    {!! SelectGoodsItemTree($lang_id, 0 ,$goods_item_id->goods_subject_id) !!}
                                                </select>
                                            </div>

                                            <div class="col-12">
                                                <label for="brand_id"
                                                       class="form-label">{{__('variables.brand')}}</label>
                                                <select class="form-select single-select" name="brand_id" id="brand_id">
                                                    <option value="0"
                                                            selected>{{__('variables.select_item')}}</option>
                                                    {!! SelectBrandsTree($lang_id, 0, $goods_item_id->brand_id) !!}
                                                </select>
                                            </div>

                                            <div class="col-12">
                                                <label for="b2b_type"
                                                       class="form-label">{{ __('variables.media_type') }}</label>
                                                <select class="form-select" name="b2b_type" id="b2b_type">
                                                    <option
                                                        value="b2b" {{ $goods_item_id->b2b_type == 'b2b'? 'selected' : ''  }}>
                                                        b2b
                                                    </option>
                                                    <option
                                                        value="all" {{ $goods_item_id->b2b_type == 'all'? 'selected' : ''  }}>
                                                        all
                                                    </option>
                                                </select>
                                            </div>

                                            @if($goods_types && count($goods_types))
                                                <div class="col-12">
                                                    <label for="goods_type_id"
                                                           class="form-label">{{__('variables.product_type')}}</label>
                                                    <select class="form-select single-select" name="goods_type_id" id="goods_type_id">
                                                        <option value="" selected>{{__('variables.select_item')}}</option>
                                                        @foreach($goods_types as $one_type)
                                                            <option
                                                                value="{{ $one_type->id }}" {{ $one_type->id == $goods_item_id->goods_type_id ? 'selected' : ''  }}>{{ $one_type->itemByLang->name ?? '' }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif

                                            <div class="col-md-6">
                                                <label for="price" class="form-label">{{__('variables.price')}}</label>
                                                <input type="text" name="price" class="form-control" id="price"
                                                       value="{{$goods_item_id->price ?? ''}}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="price_promo"
                                                       class="form-label">{{__('variables.price_promo')}}</label>
                                                <input type="text" name="price_promo" class="form-control" id="price_promo"
                                                       value="{{$goods_item_id->price_promo ?? ''}}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="one_c_code"
                                                       class="form-label">{{__('variables.1c_code')}}</label>
                                                <input type="text" class="form-control" name="one_c_code"
                                                       id="one_c_code" value="{{$goods_item_id->one_c_code ?? ''}}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="barcode"
                                                       class="form-label">{{__('variables.barcode')}}</label>
                                                <input type="text" class="form-control" name="barcode"
                                                       id="barcode" value="{{$goods_item_id->barcode ?? ''}}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="articol"
                                                       class="form-label">{{__('variables.articol')}}</label>
                                                <input type="text" class="form-control" name="articol"
                                                       id="articol" value="{{$goods_item_id->articol ?? ''}}">
                                            </div>
                                                <div class="col-md-6">
                                                    <label for="products_count"
                                                           class="form-label">{{__('variables.goods_count')}}</label>
                                                    <input type="text" name="products_count" class="form-control"
                                                           id="products_count"
                                                           value="{{$goods_item_id->products_count ?? ''}}">
                                                </div>

                                                <div class="col-md-6">
                                                    <input type="checkbox" class="form-check-input" name="new_element"
                                                           id="new_element" {{$goods_item_id->new_element == 1 ? 'checked' : ''}}>
                                                    <label class="form-check-label"
                                                           for="new_element">{{__('variables.new_element')}}</label>
                                                </div>

                                            <div class="col-md-6">
                                                <input type="checkbox" class="form-check-input" name="popular_element"
                                                       id="popular_element" {{$goods_item_id->popular_element == 1 ? 'checked' : ''}}>
                                                <label class="form-check-label"
                                                       for="popular_element">{{__('variables.popular_element')}}</label>
                                            </div>

                                            <div class="col-md-6">
                                                <input type="checkbox" class="form-check-input" name="in_stoc"
                                                       id="in_stoc" {{$goods_item_id->in_stoc == 1 ? 'checked' : ''}}>
                                                <label class="form-check-label"
                                                       for="in_stoc">{{__('variables.in_stock')}}</label>
                                            </div>

                                                <div class="col-md-6">
                                                    <input type="checkbox" class="form-check-input" name="show_in_search"
                                                           id="show_in_search" {{$goods_item_id->show_in_search == 1 ? 'checked' : ''}}>
                                                    <label class="form-check-label"
                                                           for="show_in_search">{{__('variables.show_in_search')}}</label>
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
                        </div>
                    </form>

                    <div class="form-body mt-4">
                        <div class="row">
                            <div class="col-lg-12">
                                <form action="{{url($lang, ['back','upload'])}}"
                                      class="dropzone border border-3 p-4 rounded" style="border: none;"
                                      id="dropzone-images-upload" data-gallery-id="{{ $goods_item_id->id ?? '' }}"
                                      enctype="multipart/form-data">
                                    <div class="dz-default dz-message text-center">
                                        <p>{{__('variables.drag_and_drop_images')}}</p>
                                        <span>
                                            <i class="lni lni-cloud-upload dropzone-font-size text-primary"></i>
                                        </span>
                                    </div>
                                </form>
                                @if($goods_photo && $goods_photo->isNotEmpty())
                                    <div class="card border border-3 mt-4 rounded shadow-none">
                                        <div class="card-body">
                                            <div class="d-lg-flex align-items-center mb-4 gap-3">
                                                <div class="ms-auto">
                                                    @if($groupSubRelations->del_to_rec == 1)
                                                        <button
                                                            class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                                            data-url="{{urlForLanguage($lang, 'destroygoodsphoto/destroygoodsphoto')}}"
                                                            data-current-url="{{ url()->current() }}" data-upload-path-optional="goods-items" disabled>
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
                                                    <tbody class="sort-table" data-url="{{ $current_url }}"
                                                           data-action="gallery">
                                                    @foreach($goods_photo as $one_goods_photo)
                                                        <tr class="row-id" data-id="{{$one_goods_photo->id}}">
                                                            <th class="text-center"
                                                                scope="row">{{ $loop->iteration }}</th>
                                                            <td class="text-center">
                                                                @if(!empty($one_goods_photo->img) && file_exists('upfiles/goods-items/' . $one_goods_photo->img))
                                                                    <a href="{{ asset('upfiles/goods-items') }}/{{$one_goods_photo->img ?? ''}}"
                                                                       data-fancybox="gallery">
                                                                        <img
                                                                            src="{{ asset('upfiles/goods-items/m') }}/{{showImg($one_goods_photo->img) ?? ''}}"
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
                                                                <div class="form-switch">
                                                                    <input class="form-check-input change-active"
                                                                           type="checkbox"
                                                                           data-active="{{$one_goods_photo->active}}"
                                                                           data-element-id="{{$one_goods_photo->id}}"
                                                                           data-action="gallery"
                                                                           id="switch-active-{{$one_goods_photo->id}}"
                                                                           data-url="{{$current_url}}" {{$one_goods_photo->active == 1 ? 'checked' : ''}}>
                                                                    <label class="form-check-label"
                                                                           for="switch-active-{{$one_goods_photo->id}}"></label>
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
                                                                           value="{{$one_goods_photo->id}}"
                                                                           id="destroy-element-{{$one_goods_photo->id}}">
                                                                    <label class="form-check-label"
                                                                           for="destroy-element-{{$one_goods_photo->id}}"></label>
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


